<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\CentreExamen;
use App\Services\CandidatureService;
use Illuminate\Http\Request;

class AgentExamenController extends Controller
{
    protected $candidatureService;

    public function __construct(CandidatureService $candidatureService)
    {
        $this->candidatureService = $candidatureService;
    }

    /**
     * Liste des candidatures pour un centre d'examen
     */
    public function index(Request $request)
    {
        $query = Candidature::with([
            'user:id,nom,prenom,email',
            'concours:id,nom,code,date_examen,heure_examen',
            'centreExamen:id,nom',
            'salleExamen:id,nom'
        ])
        ->whereNotNull('centre_examen_id')
        ->whereIn('statut', ['valide_depot', 'convoque', 'present', 'absent'])
        ->latest();

        // Filtre par centre d'examen
        if ($request->has('centre_examen_id')) {
            $query->where('centre_examen_id', $request->centre_examen_id);
        }

        // Filtre par salle
        if ($request->has('salle_examen_id')) {
            $query->where('salle_examen_id', $request->salle_examen_id);
        }

        // Filtre par statut
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par concours
        if ($request->has('concours_id')) {
            $query->where('concours_id', $request->concours_id);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code_candidat', 'like', "%{$search}%")
                  ->orWhere('numero_table', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('nom', 'like', "%{$search}%")
                         ->orWhere('prenom', 'like', "%{$search}%");
                  });
            });
        }

        $candidatures = $query->paginate($request->per_page ?? 20);

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
     * Scanner un QR Code
     */
    public function scanQRCode(Request $request)
    {
        $validated = $request->validate([
            'qr_code_data' => 'required_without:code_candidat|string',
            'code_candidat' => 'required_without:qr_code_data|string',
        ]);

        // Support both field names for backward compatibility
        $qrCodeData = $validated['qr_code_data'] ?? $validated['code_candidat'];

        try {
            $candidature = $this->candidatureService->verifierQRCode($qrCodeData);

            if (!$candidature) {
                return response()->json([
                    'valid' => false,
                    'message' => 'QR Code invalide ou expiré'
                ], 404);
            }

            // Vérifier que la candidature appartient au centre de l'agent
            $agent = auth()->user();
            if ($agent->centre_examen_id && $candidature->centre_examen_id !== $agent->centre_examen_id) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Ce candidat n\'est pas affecté à votre centre d\'examen',
                    'candidature' => $candidature->load(['user', 'concours', 'centreExamen'])
                ], 403);
            }

            // Vérifier que la candidature est bien validée
            if (!in_array($candidature->statut, ['valide_depot', 'convoque', 'present'])) {
                return response()->json([
                    'valid' => false,
                    'message' => "Cette candidature n'est pas autorisée pour l'examen (Statut: {$candidature->statut_libelle})",
                    'candidature' => $candidature->load(['user', 'concours'])
                ], 403);
            }

            return response()->json([
                'valid' => true,
                'message' => 'QR Code valide',
                'candidature' => $candidature->load([
                    'user:id,nom,prenom,email',
                    'concours:id,nom,code,date_examen,heure_examen',
                    'centreExamen:id,nom',
                    'salleExamen:id,nom',
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Erreur lors de la vérification du QR Code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer le rapport de fin d'examen à l'admin
     */
    public function envoyerRapportAdmin(Request $request)
    {
        $validated = $request->validate([
            'centre_examen_id' => 'required|exists:centres_examen,id',
            'concours_id' => 'required|exists:concours,id',
        ]);

        try {
            $centreExamen = CentreExamen::findOrFail($validated['centre_examen_id']);
            $concours = Concours::findOrFail($validated['concours_id']);

            // Récupérer tous les candidats
            $candidatures = Candidature::where('centre_examen_id', $centreExamen->id)
                ->where('concours_id', $concours->id)
                ->whereIn('statut', ['present', 'absent', 'fraude'])
                ->with(['user', 'salleExamen'])
                ->get();

            if ($candidatures->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun candidat trouvé pour ce centre et concours'
                ], 404);
            }

            // Statistiques
            $stats = [
                'total' => $candidatures->count(),
                'present' => $candidatures->where('statut', 'present')->count(),
                'absent' => $candidatures->where('statut', 'absent')->count(),
                'fraude' => $candidatures->where('statut', 'fraude')->count(),
            ];

            // Créer CSV
            $filename = "rapport_examen_{$concours->code}_{$centreExamen->code}.csv";
            $path = storage_path("app/public/exports/{$filename}");

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $file = fopen($path, 'w');
            fputcsv($file, ['Code Candidat', 'Nom', 'Prénom', 'Salle', 'Table', 'Statut', 'Email']);

            foreach ($candidatures as $c) {
                $statut_label = match($c->statut) {
                    'present' => 'Présent',
                    'absent' => 'Absent',
                    'fraude' => 'Fraude',
                    default => $c->statut,
                };

                fputcsv($file, [
                    $c->code_candidat,
                    $c->user->nom,
                    $c->user->prenom,
                    $c->salleExamen->nom,
                    $c->numero_table,
                    $statut_label,
                    $c->user->email,
                ]);
            }

            fclose($file);

            // Envoyer email à l'admin
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new \App\Notifications\RapportExamenNotification($stats, $concours, $centreExamen, auth()->user()));
            }

            // Enregistrer dans les logs
            AuditLog::log('send_exam_report', $concours, auth()->user());

            return response()->json([
                'message' => 'Rapport envoyé à l\'administrateur',
                'statistiques' => $stats,
                'centre_examen' => $centreExamen->nom,
                'concours' => $concours->nom,
                'fichier' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Marquer un candidat comme présent
     */
    public function marquerPresent($id)
    {
        try {
            $candidature = Candidature::findOrFail($id);

            // Vérifier que la candidature est bien validée
            if (!in_array($candidature->statut, ['valide_depot', 'convoque'])) {
                return response()->json([
                    'message' => "Cette candidature ne peut pas être marquée comme présente (Statut: {$candidature->statut_libelle})"
                ], 400);
            }

            $candidature = $this->candidatureService->marquerPresent($candidature, auth()->user());
            
            return response()->json([
                'message' => "Candidat {$candidature->user->nom_complet} marqué comme présent",
                'candidature' => $candidature->load(['user', 'salleExamen'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Marquer un candidat comme absent ou fraude
     */
    public function marquerAbsent($id, Request $request)
    {
        $validated = $request->validate([
            'motif' => 'nullable|string|max:500',
            'statut' => 'nullable|in:absent,fraude',
        ]);

        try {
            $candidature = Candidature::findOrFail($id);

            // Vérifier que la candidature est bien validée
            if (!in_array($candidature->statut, ['valide_depot', 'convoque'])) {
                return response()->json([
                    'message' => "Cette candidature ne peut pas être marquée comme absente (Statut: {$candidature->statut_libelle})"
                ], 400);
            }

            $statut = $validated['statut'] ?? 'absent';
            $motif = $validated['motif'] ?? ($statut === 'fraude' ? 'Faux documents détectés' : 'Non présenté à l\'examen');

            $candidature = $this->candidatureService->marquerAbsent(
                $candidature,
                $motif,
                $statut
            );
            
            return response()->json([
                'message' => "Candidat {$candidature->user->nom_complet} marqué comme {$statut}",
                'candidature' => $candidature->load(['user'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Statistiques pour le centre d'examen
     */
    public function statistiques(Request $request)
    {
        $query = Candidature::whereNotNull('centre_examen_id');

        // Si un centre spécifique est demandé
        if ($request->has('centre_examen_id')) {
            $query->where('centre_examen_id', $request->centre_examen_id);
        }

        // Si une salle spécifique est demandée
        if ($request->has('salle_examen_id')) {
            $query->where('salle_examen_id', $request->salle_examen_id);
        }

        // Si un concours spécifique est demandé
        if ($request->has('concours_id')) {
            $query->where('concours_id', $request->concours_id);
        }

        $stats = [
            'total_convoque' => (clone $query)->whereIn('statut', ['valide_depot', 'convoque', 'present', 'absent'])->count(),
            'present' => (clone $query)->present()->count(),
            'absent' => (clone $query)->absent()->count(),
            'en_attente' => (clone $query)->where('statut', 'convoque')->count(),
        ];

        // Taux de présence
        $stats['taux_presence'] = $stats['total_convoque'] > 0 
            ? round(($stats['present'] / $stats['total_convoque']) * 100, 2) 
            : 0;

        // Statistiques par salle
        $parSalle = (clone $query)
            ->select('salle_examen_id', 
                \DB::raw('count(*) as total'),
                \DB::raw('sum(case when statut = "present" then 1 else 0 end) as presents'),
                \DB::raw('sum(case when statut = "absent" then 1 else 0 end) as absents')
            )
            ->with('salleExamen:id,nom,centre_examen_id')
            ->groupBy('salle_examen_id')
            ->get();

        return response()->json([
            'statistiques' => $stats,
            'par_salle' => $parSalle
        ]);
    }

    /**
     * Exporter la feuille de présence
     */
    public function exporterFeuillePresence(Request $request)
    {
        $validated = $request->validate([
            'centre_examen_id' => 'nullable|exists:centres_examen,id',
            'salle_examen_id' => 'nullable|exists:salles_examen,id',
            'concours_id' => 'nullable|exists:concours,id',
        ]);

        $query = Candidature::with(['user', 'concours', 'salleExamen'])
            ->whereNotNull('centre_examen_id')
            ->whereIn('statut', ['valide_depot', 'convoque', 'present', 'absent']);

        if (isset($validated['centre_examen_id'])) {
            $query->where('centre_examen_id', $validated['centre_examen_id']);
        }

        if (isset($validated['salle_examen_id'])) {
            $query->where('salle_examen_id', $validated['salle_examen_id']);
        }

        if (isset($validated['concours_id'])) {
            $query->where('concours_id', $validated['concours_id']);
        }

        $candidatures = $query->orderBy('salle_examen_id')
            ->orderBy('numero_table')
            ->get();

        // Préparer les données pour l'export
        $data = $candidatures->map(function ($c) {
            return [
                'Code Candidat' => $c->code_candidat,
                'Nom' => $c->user->nom,
                'Prénom' => $c->user->prenom,
                'Salle' => $c->salleExamen->nom,
                'Table N°' => $c->numero_table,
                'Concours' => $c->concours->nom,
                'Présence' => $c->statut === 'present' ? 'Présent' : ($c->statut === 'absent' ? 'Absent' : 'En attente'),
                'Signature' => '',
            ];
        });

        // Créer un CSV
        $filename = 'feuille_presence_' . date('Y-m-d_His') . '.csv';
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
