<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Services\CandidatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AgentDepotController extends Controller
{
    protected $candidatureService;

    public function __construct(CandidatureService $candidatureService)
    {
        $this->candidatureService = $candidatureService;
    }

    /**
     * Liste des candidatures
     */
    public function index(Request $request)
    {
        $agent = auth()->user();
        
        \Log::info('Agent Depot accessing candidatures', [
            'agent_id' => $agent->id,
            'agent_email' => $agent->email,
            'centre_depot_id' => $agent->centre_depot_id
        ]);
        
        $query = Candidature::with([
            'user:id,nom,prenom,email,telephone',
            'concours:id,nom,code,date_examen',
            'centreDepot:id,nom,code',
            'centreExamen:id,nom',
            'salleExamen:id,nom'
        ])->latest();

        // Si l'agent est assigné à un centre de dépôt, filtrer par ce centre
        // Sinon, il voit toutes les candidatures
        if ($agent->centre_depot_id) {
            \Log::info('Filtering by centre_depot_id', ['centre_id' => $agent->centre_depot_id]);
            $query->where('centre_depot_id', $agent->centre_depot_id);
        } else {
            \Log::info('Agent has no centre assigned - showing ALL candidatures');
        }

        // Filtres additionnels
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('concours_id')) {
            $query->where('concours_id', $request->concours_id);
        }

        if ($request->has('centre_depot_id')) {
            $query->where('centre_depot_id', $request->centre_depot_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code_candidat', 'like', "%{$search}%")
                  ->orWhere('cni', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('nom', 'like', "%{$search}%")
                         ->orWhere('prenom', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $candidatures = $query->paginate($request->per_page ?? 20);
        
        \Log::info('Candidatures loaded', [
            'total' => $candidatures->total(),
            'current_page' => $candidatures->currentPage(),
            'per_page' => $candidatures->perPage()
        ]);

        return response()->json($candidatures);
    }

    /**
     * Détails d'une candidature
     */
    public function show($id)
    {
        $candidature = Candidature::with([
            'user',
            'concours',
            'centreDepot',
            'centreExamen',
            'salleExamen',
            'validateurDepot',
            'validateurExamen'
        ])->findOrFail($id);

        return response()->json([
            'candidature' => $candidature
        ]);
    }

    /**
     * Valider une candidature
     */
    public function valider($id, Request $request)
    {
        $validated = $request->validate([
            'salle_examen_id' => 'required|exists:salles_examen,id',
        ]);

        try {
            $candidature = Candidature::findOrFail($id);
            $candidature = $this->candidatureService->valider(
                $candidature,
                $validated,
                auth()->user()
            );
            
            return response()->json([
                'message' => 'Candidature validée avec succès. Le candidat a reçu sa convocation.',
                'candidature' => $candidature->load(['salleExamen', 'centreExamen'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Rejeter une candidature
     */
    public function rejeter($id, Request $request)
    {
        $validated = $request->validate([
            'motif' => 'required|string|min:10',
        ]);

        try {
            $candidature = Candidature::findOrFail($id);
            $candidature = $this->candidatureService->rejeter(
                $candidature,
                $validated['motif'],
                auth()->user()
            );
            
            return response()->json([
                'message' => 'Candidature rejetée. Le candidat a été notifié.',
                'candidature' => $candidature
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Annuler une validation
     */
    public function annulerValidation($id, Request $request)
    {
        $validated = $request->validate([
            'motif' => 'required|string|min:10',
        ]);

        try {
            $candidature = Candidature::findOrFail($id);
            
            // Vérifier que la candidature est bien validée
            if ($candidature->statut !== 'valide_depot') {
                return response()->json([
                    'message' => 'Seules les candidatures validées peuvent être annulées'
                ], 400);
            }

            // Remettre en documents_a_corriger et supprimer l'assignation
            $candidature->update([
                'statut' => 'documents_a_corriger',
                'salle_examen_id' => null,
                'numero_table' => null,
                'qr_code_data' => null,
                'valide_par_depot' => null,
                'valide_depot_at' => null,
                'motif_annulation' => $validated['motif'],
            ]);

            // Déverrouiller la candidature
            $candidature->deverrouiller();

            // Envoyer notification au candidat
            $candidature->user->notify(new \App\Notifications\ValidationAnnuleeNotification($candidature, $validated['motif']));

            // Log
            \App\Models\AuditLog::log('cancel_validation', $candidature, auth()->user());

            return response()->json([
                'message' => 'Validation annulée avec succès. Le candidat a été notifié.',
                'candidature' => $candidature->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Voir un document uploadé
     */
    public function voirDocument($id, $type)
    {
        $candidature = Candidature::findOrFail($id);
        
        // Gérer le cas spécial de photo_candidat
        if ($type === 'photo_candidat') {
            $field = 'photo_candidat';
        } else {
            $field = "document_{$type}";
        }
        
        // Liste des champs valides
        $validFields = ['document_cni', 'document_diplome', 'document_acte_naissance', 'document_recu_paiement', 'photo_candidat'];
        
        if (!in_array($field, $validFields)) {
            return response()->json(['message' => 'Type de document invalide'], 400);
        }

        if (!$candidature->$field) {
            return response()->json(['message' => 'Document non disponible'], 404);
        }

        $path = storage_path("app/public/{$candidature->$field}");
        
        if (!file_exists($path)) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        return response()->file($path);
    }

    /**
     * Statistiques pour l'agent
     */
    public function statistiques(Request $request)
    {
        $agent = auth()->user();
        
        $query = Candidature::query();

        // Si l'agent est assigné à un centre, filtrer par ce centre
        if ($agent->centre_depot_id) {
            $query->where('centre_depot_id', $agent->centre_depot_id);
        }

        // Si un centre spécifique est demandé (pour les agents sans centre assigné)
        if ($request->has('centre_depot_id')) {
            $query->where('centre_depot_id', $request->centre_depot_id);
        }

        $stats = [
            'total' => $query->count(),
            'en_attente' => (clone $query)->enAttente()->count(),
            'valide_depot' => (clone $query)->valideDepot()->count(),
            'documents_a_corriger' => (clone $query)->where('statut', 'documents_a_corriger')->count(),
            'rejete' => (clone $query)->rejete()->count(),
        ];

        // Statistiques par concours
        $parConcours = (clone $query)
            ->select('concours_id', \DB::raw('count(*) as total'))
            ->with('concours:id,nom,code')
            ->groupBy('concours_id')
            ->get();

        return response()->json([
            'statistiques' => $stats,
            'par_concours' => $parConcours,
            'centre_assigne' => $agent->centre_depot_id ? [
                'id' => $agent->centre_depot_id,
                'nom' => $agent->centreDepot?->nom
            ] : null
        ]);
    }

    /**
     * Envoyer les dossiers validés à l'admin
     */
    public function envoyerDossiersAdmin(Request $request)
    {
        $validated = $request->validate([
            'candidature_ids' => 'required|array|min:1',
            'candidature_ids.*' => 'exists:candidatures,id',
        ]);

        try {
            $candidatures = Candidature::whereIn('id', $validated['candidature_ids'])
                ->where('statut', 'valide_depot')
                ->with(['user', 'concours', 'centreDepot'])
                ->get();

            if ($candidatures->isEmpty()) {
                return response()->json([
                    'message' => 'Aucune candidature validée trouvée'
                ], 404);
            }

            // Envoyer notification à l'admin
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new \App\Notifications\DossiersValidesNotification($candidatures, auth()->user()));
            }

            // Enregistrer dans les logs
            foreach ($candidatures as $candidature) {
                AuditLog::log('send_to_admin', $candidature, auth()->user());
            }

            return response()->json([
                'message' => 'Dossiers envoyés à l\'administrateur avec succès',
                'nombre_dossiers' => $candidatures->count(),
                'candidatures' => $candidatures->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'code_candidat' => $c->code_candidat,
                        'candidat' => $c->user->nom_complet,
                        'concours' => $c->concours->nom,
                        'statut' => $c->statut,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Exporter la liste des candidatures
     */
    public function exporterListe(Request $request)
    {
        $query = Candidature::with(['user', 'concours', 'centreDepot']);

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('concours_id')) {
            $query->where('concours_id', $request->concours_id);
        }

        if ($request->has('centre_depot_id')) {
            $query->where('centre_depot_id', $request->centre_depot_id);
        }

        $candidatures = $query->get();

        // Préparer les données pour l'export
        $data = $candidatures->map(function ($c) {
            return [
                'Code Candidat' => $c->code_candidat,
                'Nom' => $c->user->nom,
                'Prénom' => $c->user->prenom,
                'Email' => $c->user->email,
                'Téléphone' => $c->telephone,
                'CNI' => $c->cni,
                'Date Naissance' => $c->date_naissance->format('d/m/Y'),
                'Concours' => $c->concours->nom,
                'Filière' => $c->filiere,
                'Diplôme' => $c->diplome_admission,
                'Centre Dépôt' => $c->centreDepot->nom,
                'Statut' => $c->statut_libelle,
                'Date Soumission' => $c->created_at->format('d/m/Y H:i'),
            ];
        });

        // Créer un CSV simple
        $filename = 'candidatures_' . date('Y-m-d_His') . '.csv';
        $path = storage_path("app/public/exports/{$filename}");

        // Créer le dossier si nécessaire
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');
        
        // Headers
        if ($data->count() > 0) {
            fputcsv($file, array_keys($data->first()));
        }

        // Data
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
