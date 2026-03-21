<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Rapport, Candidature, User};
use App\Notifications\RapportExamenNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RapportController extends Controller
{
    /**
     * Liste des rapports de l'agent connecté
     */
    public function index(Request $request)
    {
        $agent = auth()->user();
        
        $query = Rapport::where('agent_id', $agent->id)
            ->with(['concours:id,nom,code', 'centreDepot:id,nom', 'centreExamen:id,nom'])
            ->latest();

        // Filtres
        if ($request->has('envoye')) {
            if ($request->envoye === 'true' || $request->envoye === '1') {
                $query->envoye();
            } else {
                $query->nonEnvoye();
            }
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $rapports = $query->paginate($request->per_page ?? 20);

        return response()->json($rapports);
    }

    /**
     * Créer un nouveau rapport
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'concours_id' => 'required|exists:concours,id',
            'type' => 'required|in:depot,examen',
            'titre' => 'required|string|max:255',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'statistiques' => 'nullable|array',
            'observations' => 'nullable|string',
        ]);

        $agent = auth()->user();

        $rapport = Rapport::create([
            'agent_id' => $agent->id,
            'concours_id' => $validated['concours_id'],
            'type' => $validated['type'],
            'titre' => $validated['titre'],
            'periode_debut' => $validated['periode_debut'],
            'periode_fin' => $validated['periode_fin'],
            'statistiques' => $validated['statistiques'] ?? [],
            'description' => $validated['observations'] ?? '',
            'centre_depot_id' => $agent->centre_depot_id,
            'centre_examen_id' => $agent->centre_examen_id,
            'envoye_admin' => false,
        ]);

        return response()->json([
            'message' => 'Rapport créé avec succès',
            'rapport' => $rapport->load(['concours', 'centreDepot', 'centreExamen']),
        ], 201);
    }

    /**
     * Mettre à jour un rapport
     */
    public function update(Request $request, $id)
    {
        $rapport = Rapport::findOrFail($id);
        $agent = auth()->user();

        // Vérifier que le rapport appartient à l'agent
        if ($rapport->agent_id !== $agent->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Ne pas permettre la modification si déjà envoyé
        if ($rapport->envoye_admin) {
            return response()->json(['message' => 'Impossible de modifier un rapport déjà envoyé'], 403);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'statistiques' => 'sometimes|array',
            'periode_debut' => 'sometimes|date',
            'periode_fin' => 'sometimes|date|after_or_equal:periode_debut',
        ]);

        $rapport->update($validated);

        return response()->json([
            'message' => 'Rapport modifié avec succès',
            'rapport' => $rapport->fresh(['concours', 'centreDepot', 'centreExamen']),
        ]);
    }

    /**
     * Supprimer un rapport
     */
    public function destroy($id)
    {
        $rapport = Rapport::findOrFail($id);
        $agent = auth()->user();

        // Vérifier que le rapport appartient à l'agent
        if ($rapport->agent_id !== $agent->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Ne pas permettre la suppression si déjà envoyé
        if ($rapport->envoye_admin) {
            return response()->json(['message' => 'Impossible de supprimer un rapport déjà envoyé'], 400);
        }

        $rapport->delete();

        return response()->json(['message' => 'Rapport supprimé avec succès']);
    }

    /**
     * Générer un rapport pour Agent Dépôt
     */
    public function genererRapportDepot(Request $request)
    {
        $validated = $request->validate([
            'type_periode' => 'required|in:quotidien,hebdomadaire,mensuel,personnalise',
            'date_debut' => 'required_if:type_periode,personnalise|date',
            'date_fin' => 'required_if:type_periode,personnalise|date|after_or_equal:date_debut',
            'concours_id' => 'nullable|exists:concours,id',
        ]);

        $agent = auth()->user();

        // Déterminer les dates selon le type de période
        [$dateDebut, $dateFin, $periode] = $this->calculerPeriode($validated['type_periode'], $validated);

        // Récupérer les candidatures de la période
        $query = Candidature::whereBetween('updated_at', [$dateDebut, $dateFin]);

        // Filtrer par centre si l'agent est assigné
        if ($agent->centre_depot_id) {
            $query->where('centre_depot_id', $agent->centre_depot_id);
        }

        // Filtrer par concours si spécifié
        if (isset($validated['concours_id'])) {
            $query->where('concours_id', $validated['concours_id']);
        }

        $candidatures = $query->with(['user', 'concours'])->get();

        // Calculer les statistiques
        $stats = [
            'total_traitees' => $candidatures->count(),
            'validees' => $candidatures->where('statut', 'valide_depot')->count(),
            'rejetees' => $candidatures->where('statut', 'documents_a_corriger')->count(),
            'en_attente' => $candidatures->where('statut', 'en_attente')->count(),
            'temps_moyen_traitement' => $this->calculerTempsMoyen($candidatures),
        ];

        // Statistiques par concours
        $parConcours = $candidatures->groupBy('concours_id')->map(function ($group) {
            return [
                'concours_nom' => $group->first()->concours->nom,
                'total' => $group->count(),
                'validees' => $group->where('statut', 'valide_depot')->count(),
                'rejetees' => $group->where('statut', 'documents_a_corriger')->count(),
            ];
        })->values();

        // Créer le rapport
        $rapport = Rapport::create([
            'type' => 'depot',
            'agent_id' => $agent->id,
            'concours_id' => $validated['concours_id'] ?? null,
            'centre_depot_id' => $agent->centre_depot_id,
            'titre' => "Rapport {$validated['type_periode']} - {$agent->nom_complet}",
            'description' => "Rapport d'activité du {$dateDebut->format('d/m/Y')} au {$dateFin->format('d/m/Y')}",
            'periode_debut' => $dateDebut,
            'periode_fin' => $dateFin,
            'statistiques' => array_merge($stats, [
                'par_concours' => $parConcours,
                'candidatures' => $candidatures->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'code_candidat' => $c->code_candidat,
                        'nom' => $c->user->nom,
                        'prenom' => $c->user->prenom,
                        'concours_nom' => $c->concours->nom,
                        'statut' => $c->statut,
                        'date_traitement' => $c->updated_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray(),
            ]),
            'envoye_admin' => false,
        ]);

        return response()->json([
            'message' => 'Rapport généré avec succès',
            'rapport' => $rapport->load(['concours', 'centreDepot']),
        ], 201);
    }

    /**
     * Envoyer un rapport à l'admin
     */
    public function envoyerRapport($id)
    {
        $rapport = Rapport::with(['concours', 'centreDepot', 'centreExamen'])->findOrFail($id);
        $agent = auth()->user();

        // Vérifier que le rapport appartient à l'agent
        if ($rapport->agent_id !== $agent->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Vérifier qu'il n'est pas déjà envoyé
        if ($rapport->envoye_admin) {
            return response()->json(['message' => 'Ce rapport a déjà été envoyé'], 400);
        }

        // Marquer comme envoyé
        $rapport->update([
            'envoye_admin' => true,
            'envoye_admin_at' => now(),
        ]);

        // Notifier l'admin
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\RapportDepotNotification($rapport, $agent));
        }

        return response()->json([
            'message' => 'Rapport envoyé à l\'administrateur avec succès',
            'rapport' => $rapport->fresh(['concours', 'centreDepot', 'centreExamen']),
        ]);
    }

    /**
     * Télécharger un rapport en CSV
     */
    public function telechargerRapport($id, Request $request)
    {
        $rapport = Rapport::findOrFail($id);
        $agent = auth()->user();

        // Vérifier les permissions
        if ($agent->role !== 'admin' && $rapport->agent_id !== $agent->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $format = $request->get('format', 'csv');

        if ($format === 'csv') {
            return $this->exporterCSV($rapport);
        }

        return response()->json(['message' => 'Format non supporté'], 400);
    }

    /**
     * Supprimer un rapport
     */
    public function supprimerRapport($id)
    {
        $rapport = Rapport::findOrFail($id);
        $agent = auth()->user();

        // Vérifier que le rapport appartient à l'agent
        if ($rapport->agent_id !== $agent->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Ne pas permettre la suppression si déjà envoyé
        if ($rapport->envoye_admin) {
            return response()->json(['message' => 'Impossible de supprimer un rapport déjà envoyé'], 400);
        }

        $rapport->delete();

        return response()->json(['message' => 'Rapport supprimé avec succès']);
    }

    /**
     * Liste des rapports reçus (pour Admin)
     */
    public function rapportsRecus(Request $request)
    {
        $query = Rapport::envoye()
            ->with(['agent:id,nom,prenom,email', 'concours:id,nom,code', 'centreDepot:id,nom', 'centreExamen:id,nom'])
            ->latest('envoye_admin_at');

        // Filtres
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        if ($request->has('concours_id')) {
            $query->where('concours_id', $request->concours_id);
        }

        $rapports = $query->get();

        return response()->json(['rapports' => $rapports]);
    }

    /**
     * Détails d'un rapport
     */
    public function show($id)
    {
        $rapport = Rapport::with([
            'agent:id,nom,prenom,email',
            'concours:id,nom,code',
            'centreDepot:id,nom',
            'centreExamen:id,nom'
        ])->findOrFail($id);

        $agent = auth()->user();

        // Vérifier les permissions
        if ($agent->role !== 'admin' && $rapport->agent_id !== $agent->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json(['rapport' => $rapport]);
    }

    // Méthodes privées

    private function calculerPeriode($typePeriode, $validated)
    {
        $now = Carbon::now();

        switch ($typePeriode) {
            case 'quotidien':
                $dateDebut = $now->copy()->startOfDay();
                $dateFin = $now->copy()->endOfDay();
                $periode = "Aujourd'hui";
                break;

            case 'hebdomadaire':
                $dateDebut = $now->copy()->startOfWeek();
                $dateFin = $now->copy()->endOfWeek();
                $periode = "Cette semaine";
                break;

            case 'mensuel':
                $dateDebut = $now->copy()->startOfMonth();
                $dateFin = $now->copy()->endOfMonth();
                $periode = "Ce mois";
                break;

            case 'personnalise':
                $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
                $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
                $periode = $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');
                break;

            default:
                $dateDebut = $now->copy()->startOfDay();
                $dateFin = $now->copy()->endOfDay();
                $periode = "Aujourd'hui";
        }

        return [$dateDebut, $dateFin, $periode];
    }

    private function calculerTempsMoyen($candidatures)
    {
        if ($candidatures->isEmpty()) {
            return '0 min';
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($candidatures as $c) {
            if ($c->created_at && $c->updated_at) {
                $diff = $c->created_at->diffInMinutes($c->updated_at);
                if ($diff > 0) {
                    $totalMinutes += $diff;
                    $count++;
                }
            }
        }

        if ($count === 0) {
            return '0 min';
        }

        $moyenne = round($totalMinutes / $count);
        return "{$moyenne} min";
    }

    private function exporterCSV($rapport)
    {
        $filename = "rapport_{$rapport->type}_{$rapport->id}.csv";
        $path = storage_path("app/public/exports/{$filename}");

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');

        // Headers
        fputcsv($file, ['Code Candidat', 'Nom', 'Prénom', 'Concours', 'Statut', 'Date Traitement']);

        // Data
        $candidatures = $rapport->statistiques['candidatures'] ?? [];
        foreach ($candidatures as $c) {
            fputcsv($file, [
                $c['code_candidat'],
                $c['nom'],
                $c['prenom'],
                $c['concours_nom'],
                $c['statut'],
                $c['date_traitement'],
            ]);
        }

        fclose($file);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
