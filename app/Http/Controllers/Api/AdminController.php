<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Concours, Candidature, User, CentreDepot, CentreExamen, SalleExamen, Region, Departement, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard avec statistiques globales
     */
    public function dashboard()
    {
        $stats = [
            'concours' => [
                'total' => Concours::count(),
                'ouverts' => Concours::ouvert()->count(),
                'prochains' => Concours::prochain()->count(),
            ],
            'candidatures' => [
                'total' => Candidature::count(),
                'en_attente' => Candidature::enAttente()->count(),
                'valide_depot' => Candidature::valideDepot()->count(),
                'present' => Candidature::present()->count(),
                'absent' => Candidature::absent()->count(),
            ],
            'utilisateurs' => [
                'total' => User::count(),
                'candidats' => User::where('role', 'candidat')->count(),
                'agents_depot' => User::where('role', 'agent_depot')->count(),
                'agents_examen' => User::where('role', 'agent_examen')->count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'centres' => [
                'centres_depot' => CentreDepot::count(),
                'centres_examen' => CentreExamen::count(),
                'salles_examen' => SalleExamen::count(),
            ],
        ];

        // Candidatures récentes
        $candidatures_recentes = Candidature::with(['user', 'concours'])
            ->latest()
            ->limit(10)
            ->get();

        // Concours à venir
        $concours_prochains = Concours::prochain()
            ->limit(5)
            ->get();

        return response()->json([
            'statistiques' => $stats,
            'candidatures_recentes' => $candidatures_recentes,
            'concours_prochains' => $concours_prochains,
        ]);
    }

    /**
     * Statistiques globales
     */
    public function statistiquesGlobales(Request $request)
    {
        $stats = [
            'total_candidatures' => Candidature::count(),
            'candidatures_en_attente' => Candidature::where('statut', 'en_attente')->count(),
            'candidatures_validees' => Candidature::where('statut', 'valide_depot')->count(),
            'candidatures_rejetees' => Candidature::where('statut', 'documents_a_corriger')->count(),
            'total_concours' => Concours::count(),
            'concours_actifs' => Concours::where('inscription_ouverte', true)->count(),
            'total_utilisateurs' => User::count(),
            'total_centres_depot' => CentreDepot::count(),
            'total_centres_examen' => CentreExamen::count(),
        ];

        return response()->json($stats);
    }

    /**
     * Statistiques par concours
     */
    public function statistiquesParConcours($id)
    {
        $concours = Concours::findOrFail($id);

        $stats = [
            'concours' => $concours,
            'candidatures' => [
                'total' => $concours->getNombreCandidatures(),
                'en_attente' => $concours->candidatures()->enAttente()->count(),
                'valide_depot' => $concours->candidatures()->valideDepot()->count(),
                'present' => $concours->candidatures()->present()->count(),
                'absent' => $concours->candidatures()->absent()->count(),
            ],
            'par_centre_examen' => $concours->candidatures()
                ->select('centre_examen_id', \DB::raw('count(*) as total'))
                ->whereNotNull('centre_examen_id')
                ->with('centreExamen:id,nom')
                ->groupBy('centre_examen_id')
                ->get(),
            'par_region' => $concours->candidatures()
                ->select('region_origine', \DB::raw('count(*) as total'))
                ->groupBy('region_origine')
                ->get()
                ->map(function($item) {
                    return [
                        'nom' => $item->region_origine,
                        'total' => $item->total
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Envoyer la liste des candidats autorisés à l'agent examen
     */
    public function envoyerListeAgentExamen(Request $request)
    {
        $validated = $request->validate([
            'centre_examen_id' => 'required|exists:centres_examen,id',
            'concours_id' => 'required|exists:concours,id',
        ]);

        try {
            $centreExamen = CentreExamen::findOrFail($validated['centre_examen_id']);
            $concours = Concours::findOrFail($validated['concours_id']);

            // Récupérer tous les candidats validés
            $candidatures = Candidature::where('centre_examen_id', $centreExamen->id)
                ->where('concours_id', $concours->id)
                ->where('statut', 'valide_depot')
                ->with(['user', 'salleExamen'])
                ->get();

            if ($candidatures->isEmpty()) {
                return response()->json([
                    'message' => 'Aucun candidat autorisé trouvé pour ce centre et concours'
                ], 404);
            }

            // Créer CSV
            $filename = "liste_candidats_{$concours->code}_{$centreExamen->code}.csv";
            $path = storage_path("app/public/exports/{$filename}");

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $file = fopen($path, 'w');
            fputcsv($file, ['Code Candidat', 'Nom', 'Prénom', 'Salle', 'Table', 'Email', 'Téléphone']);

            foreach ($candidatures as $c) {
                fputcsv($file, [
                    $c->code_candidat,
                    $c->user->nom,
                    $c->user->prenom,
                    $c->salleExamen->nom,
                    $c->numero_table,
                    $c->user->email,
                    $c->telephone,
                ]);
            }

            fclose($file);

            // Envoyer notification à tous les agents du centre
            $agents = User::where('role', 'agent_examen')
                ->get();

            foreach ($agents as $agent) {
                $agent->notify(new \App\Notifications\ListeCandidatsNotification($candidatures, $concours, $centreExamen));
            }

            // Enregistrer dans les logs
            AuditLog::log('send_list_to_exam_center', $concours, auth()->user());

            return response()->json([
                'message' => 'Liste envoyée aux agents du centre d\'examen',
                'nombre_candidats' => $candidatures->count(),
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

    // GESTION DES CONCOURS
    
    public function getConcours(Request $request)
    {
        $query = Concours::query();

        if ($request->has('actif')) {
            $query->where('inscription_ouverte', $request->actif);
        }

        $concours = $query->latest()->get();

        return response()->json(['concours' => $concours]);
    }

    public function storeConcours(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:concours,code',
            'description' => 'nullable|string',
            'type' => 'required|in:academique,professionnel,technique',
            'filiere' => 'required|string|max:100',
            'cursus' => 'nullable|string|max:50',
            'date_ouverture' => 'required|date',
            'date_cloture' => 'required|date|after:date_ouverture',
            'date_examen' => 'required|date|after:date_cloture',
            'heure_examen' => 'required|date_format:H:i',
            'diplomes_requis' => 'nullable|array',
            'age_minimum' => 'nullable|integer|min:15',
            'age_maximum' => 'nullable|integer|max:50',
            'frais_inscription' => 'required|numeric|min:0',
            'nombre_places' => 'nullable|integer|min:1',
            'documents_requis' => 'nullable|array',
        ]);

        $concours = Concours::create(array_merge($validated, [
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'message' => 'Concours créé avec succès',
            'concours' => $concours
        ], 201);
    }

    public function updateConcours(Request $request, $id)
    {
        $concours = Concours::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date_ouverture' => 'sometimes|date',
            'date_cloture' => 'sometimes|date',
            'date_examen' => 'sometimes|date',
            'heure_examen' => 'sometimes|date_format:H:i',
            'frais_inscription' => 'sometimes|numeric|min:0',
            'nombre_places' => 'nullable|integer|min:1',
            'inscription_ouverte' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $concours->update($validated);

        return response()->json([
            'message' => 'Concours mis à jour',
            'concours' => $concours
        ]);
    }

    public function ouvrirConcours($id)
    {
        $concours = Concours::findOrFail($id);
        $concours->update(['inscription_ouverte' => true]);

        return response()->json([
            'message' => 'Concours ouvert aux inscriptions',
            'concours' => $concours
        ]);
    }

    public function fermerConcours($id)
    {
        $concours = Concours::findOrFail($id);
        $concours->update(['inscription_ouverte' => false]);

        return response()->json([
            'message' => 'Concours fermé aux inscriptions',
            'concours' => $concours
        ]);
    }

    public function deleteConcours($id)
    {
        $concours = Concours::findOrFail($id);
        
        // Vérifier s'il y a des candidatures
        if ($concours->candidatures()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un concours avec des candidatures'
            ], 400);
        }

        $concours->delete();

        return response()->json([
            'message' => 'Concours supprimé avec succès'
        ]);
    }

    public function exporterCandidatures($id)
    {
        $concours = Concours::findOrFail($id);
        
        $candidatures = $concours->candidatures()
            ->with(['user', 'centreDepot', 'centreExamen', 'salleExamen'])
            ->get();

        // Créer CSV
        $filename = "candidatures_{$concours->code}_" . date('Y-m-d') . ".csv";
        $path = storage_path("app/public/exports/{$filename}");

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');
        fputcsv($file, [
            'Code', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Statut', 
            'Centre Dépôt', 'Centre Examen', 'Salle', 'Table', 'Date Soumission'
        ]);

        foreach ($candidatures as $c) {
            fputcsv($file, [
                $c->code_candidat,
                $c->nom,
                $c->prenom,
                $c->user->email,
                $c->telephone,
                $c->statut,
                $c->centreDepot->nom ?? '',
                $c->centreExamen->nom ?? '',
                $c->salleExamen->nom ?? '',
                $c->numero_table ?? '',
                $c->created_at->format('Y-m-d H:i'),
            ]);
        }

        fclose($file);

        return response()->json([
            'message' => 'Export généré avec succès',
            'fichier' => $filename,
            'url' => url("storage/exports/{$filename}"),
            'nombre_candidatures' => $candidatures->count()
        ]);
    }

    // GESTION DES UTILISATEURS

    public function getUsers(Request $request)
    {
        $query = User::with(['centreDepot', 'centreExamen']);

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->get();

        return response()->json(['users' => $users]);
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,agent_depot,agent_examen,candidat',
            'centre_depot_id' => 'nullable|exists:centres_depot,id',
            'centre_examen_id' => 'nullable|exists:centres_examen,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create($validated);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:admin,agent_depot,agent_examen,candidat',
            'centre_depot_id' => 'nullable|exists:centres_depot,id',
            'centre_examen_id' => 'nullable|exists:centres_examen,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user
        ]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    public function toggleUserActive($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'user' => $user
        ]);
    }

    // GESTION DES CANDIDATURES

    public function getCandidatures(Request $request)
    {
        $query = Candidature::with(['user', 'concours', 'centreDepot', 'centreExamen']);

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('concours_id')) {
            $query->where('concours_id', $request->concours_id);
        }

        $candidatures = $query->latest()->get();

        return response()->json(['candidatures' => $candidatures]);
    }

    public function validerCandidature($id, Request $request)
    {
        // Admin peut forcer la validation
        $candidature = Candidature::findOrFail($id);
        
        $validated = $request->validate([
            'salle_examen_id' => 'required|exists:salles_examen,id',
        ]);

        $candidature->update([
            'statut' => 'valide_depot',
            'salle_examen_id' => $validated['salle_examen_id'],
            'valide_par_depot' => auth()->id(),
            'valide_depot_at' => now(),
        ]);

        return response()->json([
            'message' => 'Candidature validée',
            'candidature' => $candidature
        ]);
    }

    public function rejeterCandidature($id, Request $request)
    {
        $candidature = Candidature::findOrFail($id);
        
        $validated = $request->validate([
            'motif' => 'required|string',
        ]);

        $candidature->update([
            'statut' => 'rejete',
            'motif_rejet' => $validated['motif'],
        ]);

        return response()->json([
            'message' => 'Candidature rejetée',
            'candidature' => $candidature
        ]);
    }

    // RÉFÉRENTIELS

    public function getRegions()
    {
        return response()->json(Region::with('departements')->get());
    }

    public function getDepartements($regionId)
    {
        return response()->json(Departement::where('region_id', $regionId)->get());
    }

    public function getCentresDepot()
    {
        $centres = CentreDepot::with(['region', 'departement'])->get();
        return response()->json(['centres' => $centres]);
    }

    public function storeCentreDepot(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:centres_depot,code',
            'nom' => 'required|string|max:255',
            'ville' => 'required|string|max:100',
            'adresse' => 'required|string|max:500',
            'region_id' => 'required|exists:regions,id',
            'departement_id' => 'nullable|exists:departements,id',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $centre = CentreDepot::create($validated);

        return response()->json([
            'message' => 'Centre de dépôt créé avec succès',
            'centre' => $centre->load(['region', 'departement'])
        ], 201);
    }

    public function getCentresExamen()
    {
        $centres = CentreExamen::with(['region', 'departement', 'salles'])->get();
        return response()->json(['centres' => $centres]);
    }

    public function storeCentreExamen(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:centres_examen,code',
            'nom' => 'required|string|max:255',
            'ville' => 'required|string|max:100',
            'adresse' => 'required|string|max:500',
            'region_id' => 'required|exists:regions,id',
            'departement_id' => 'nullable|exists:departements,id',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'capacite_totale' => 'nullable|integer|min:1',
        ]);

        $centre = CentreExamen::create($validated);

        return response()->json([
            'message' => 'Centre d\'examen créé avec succès',
            'centre' => $centre->load(['region', 'departement'])
        ], 201);
    }

    // AUDIT LOGS

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        return response()->json($query->paginate(50));
    }
}
