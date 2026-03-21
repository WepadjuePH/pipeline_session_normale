<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concours;
use Illuminate\Http\Request;

class ConcoursController extends Controller
{
    /**
     * Liste des concours ouverts (route publique)
     */
    public function ouverts()
    {
        $concours = Concours::ouvert()
            ->with(['createur:id,nom,prenom'])
            ->latest('date_examen')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nom' => $c->nom,
                    'code' => $c->code,
                    'description' => $c->description,
                    'type' => $c->type,
                    'filiere' => $c->filiere,
                    'cursus' => $c->cursus,
                    'date_ouverture' => $c->date_ouverture->format('d/m/Y'),
                    'date_cloture' => $c->date_cloture->format('d/m/Y'),
                    'date_examen' => $c->date_examen->format('d/m/Y'),
                    'heure_examen' => $c->heure_examen_formattee,
                    'diplomes_requis' => $c->diplomes_requis,
                    'frais_inscription' => $c->frais_inscription,
                    'monnaie' => $c->monnaie,
                    'nombre_places' => $c->nombre_places,
                    'places_restantes' => $c->getNombrePlacesRestantes(),
                    'nombre_candidatures' => $c->getNombreCandidatures(),
                    'est_complet' => $c->estComplet(),
                ];
            });

        return response()->json([
            'concours' => $concours,
            'total' => $concours->count()
        ]);
    }

    /**
     * Détails d'un concours (route publique)
     */
    public function show($id)
    {
        $concours = Concours::with(['createur:id,nom,prenom'])->findOrFail($id);
        
        return response()->json([
            'concours' => [
                'id' => $concours->id,
                'nom' => $concours->nom,
                'code' => $concours->code,
                'description' => $concours->description,
                'type' => $concours->type,
                'filiere' => $concours->filiere,
                'cursus' => $concours->cursus,
                'date_ouverture' => $concours->date_ouverture->format('d/m/Y'),
                'date_cloture' => $concours->date_cloture->format('d/m/Y'),
                'date_examen' => $concours->date_examen->format('d/m/Y'),
                'heure_examen' => $concours->heure_examen_formattee,
                'diplomes_requis' => $concours->diplomes_requis,
                'documents_requis' => $concours->documents_requis,
                'age_minimum' => $concours->age_minimum,
                'age_maximum' => $concours->age_maximum,
                'frais_inscription' => $concours->frais_inscription,
                'monnaie' => $concours->monnaie,
                'nombre_places' => $concours->nombre_places,
                'places_restantes' => $concours->getNombrePlacesRestantes(),
                'nombre_candidatures' => $concours->getNombreCandidatures(),
                'est_ouvert' => $concours->estOuvert(),
                'est_complet' => $concours->estComplet(),
                'created_at' => $concours->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Liste de tous les concours (pour candidat authentifié)
     */
    public function index(Request $request)
    {
        $query = Concours::actif()->with(['createur:id,nom,prenom']);

        // Filtres
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('filiere')) {
            $query->where('filiere', 'like', "%{$request->filiere}%");
        }

        if ($request->has('ouvert_uniquement') && $request->ouvert_uniquement) {
            $query->ouvert();
        }

        $concours = $query->latest('date_examen')
            ->paginate($request->per_page ?? 15);

        return response()->json($concours);
    }
}
