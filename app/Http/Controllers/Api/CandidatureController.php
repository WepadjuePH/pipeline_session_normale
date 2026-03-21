<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Services\CandidatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CandidatureController extends Controller
{
    protected $candidatureService;

    public function __construct(CandidatureService $candidatureService)
    {
        $this->candidatureService = $candidatureService;
    }

    /**
     * Soumettre une nouvelle candidature
     */
    public function soumettre(Request $request)
    {
        // Log pour déboguer
        info('Soumission candidature - Fichiers reçus:', [
            'files' => array_keys($request->allFiles()),
            'document_cni' => $request->hasFile('document_cni') ? $request->file('document_cni')->getClientOriginalName() : 'absent',
            'document_diplome' => $request->hasFile('document_diplome') ? $request->file('document_diplome')->getClientOriginalName() : 'absent',
            'document_acte_naissance' => $request->hasFile('document_acte_naissance') ? $request->file('document_acte_naissance')->getClientOriginalName() : 'absent',
            'document_recu_paiement' => $request->hasFile('document_recu_paiement') ? $request->file('document_recu_paiement')->getClientOriginalName() : 'absent',
            'photo_candidat' => $request->hasFile('photo_candidat') ? $request->file('photo_candidat')->getClientOriginalName() : 'absent',
        ]);

        $validated = $request->validate([
            'concours_id' => 'required|exists:concours,id',
            'centre_depot_id' => 'required|exists:centres_depot,id',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:masculin,feminin',
            'nationalite' => 'nullable|string|max:100',
            'region_origine' => 'required|string|max:100',
            'departement_origine' => 'required|string|max:100',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
            'premiere_langue' => 'required|string|max:50',
            'cni' => 'required|string|max:50',
            'filiere' => 'required|string|max:100',
            'cursus' => 'nullable|string|max:100',
            'diplome_admission' => 'required|string|max:100',
            'mention_diplome' => 'required|in:passable,assez_bien,bien,tres_bien,non_applicable',
            'annee_diplome' => 'required|integer|min:1990|max:' . date('Y'),
            'centre_examen_id' => 'nullable|exists:centres_examen,id',
            'nom_pere' => 'required|string|max:255',
            'telephone_pere' => 'required|string|max:20',
            'nom_mere' => 'required|string|max:255',
            'telephone_mere' => 'required|string|max:20',
            'document_cni' => 'required|file|max:5120',
            'document_diplome' => 'required|file|max:5120',
            'document_acte_naissance' => 'required|file|max:5120',
            'document_recu_paiement' => 'required|file|max:5120',
            'photo_candidat' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $candidature = $this->candidatureService->soumettre($validated, auth()->user());

            return response()->json([
                'message' => 'Candidature soumise avec succès',
                'candidature' => $candidature,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Liste des candidatures du candidat connecté
     */
    public function mesCandidatures()
    {
        $candidatures = auth()->user()->candidatures()
            ->with([
                'concours',
                'centreDepot',
                'centreExamen',
                'salleExamen'
            ])
            ->latest()
            ->get();

        return response()->json([
            'candidatures' => $candidatures
        ]);
    }

    /**
     * Détails d'une candidature
     */
    public function show($id)
    {
        $candidature = auth()->user()->candidatures()
            ->with([
                'concours',
                'centreDepot',
                'centreExamen',
                'salleExamen',
                'validateurDepot',
                'validateurExamen'
            ])
            ->findOrFail($id);

        return response()->json([
            'candidature' => $candidature
        ]);
    }

    /**
     * Modifier une candidature (uniquement si en_attente ou documents_a_corriger)
     */
    public function update(Request $request, $id)
    {
        $candidature = auth()->user()->candidatures()->findOrFail($id);

        if (!$candidature->peutEtreModifiee()) {
            return response()->json([
                'message' => 'Cette candidature ne peut plus être modifiée'
            ], 403);
        }

        $validated = $request->validate([
            'centre_depot_id' => 'sometimes|exists:centres_depot,id',
            'date_naissance' => 'sometimes|date|before:today',
            'lieu_naissance' => 'sometimes|string|max:255',
            'sexe' => 'sometimes|in:masculin,feminin',
            'region_origine_id' => 'sometimes|exists:regions,id',
            'departement_origine_id' => 'sometimes|exists:departements,id',
            'telephone' => 'sometimes|string|max:20',
            'adresse' => 'sometimes|string',
            'premiere_langue' => 'sometimes|string|max:50',
            'filiere' => 'sometimes|string|max:100',
            'diplome_admission' => 'sometimes|string|max:100',
            'mention_diplome' => 'nullable|in:passable,assez_bien,bien,tres_bien,non_applicable',
            'annee_diplome' => 'sometimes|integer|min:1990|max:' . date('Y'),
            'nom_pere' => 'nullable|string|max:255',
            'telephone_pere' => 'nullable|string|max:20',
            'nom_mere' => 'nullable|string|max:255',
            'telephone_mere' => 'nullable|string|max:20',
            'document_cni' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'document_diplome' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'document_acte_naissance' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'document_recu_paiement' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'photo_candidat' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload nouveaux documents si fournis
        $documents = [];
        $types = ['document_cni', 'document_diplome', 'document_acte_naissance', 'document_recu_paiement', 'photo_candidat'];

        foreach ($types as $type) {
            if ($request->hasFile($type)) {
                // Supprimer l'ancien document
                if ($candidature->$type) {
                    Storage::disk('public')->delete($candidature->$type);
                }

                $file = $request->file($type);
                $path = $file->store('documents/' . $type, 'public');
                $documents[$type] = $path;
            }
        }

        $candidature->update(array_merge($validated, $documents));

        // Si tous les documents sont présents, repasser en "en_attente"
        if ($candidature->statut === 'documents_a_corriger' && $candidature->documentsComplets()) {
            $candidature->update(['statut' => 'en_attente']);
        }

        return response()->json([
            'message' => 'Candidature mise à jour avec succès',
            'candidature' => $candidature->fresh()
        ]);
    }

    /**
     * Télécharger la fiche de candidature
     */
    public function telechargerFiche($id)
    {
        $candidature = auth()->user()->candidatures()->findOrFail($id);

        // Déterminer quel type de fiche télécharger
        $filename = match($candidature->statut) {
            'en_attente', 'documents_a_corriger' => "fiche_provisoire_{$candidature->code_candidat}.pdf",
            'valide_depot', 'convoque', 'present', 'absent' => "convocation_{$candidature->code_candidat}.pdf",
            default => null,
        };

        if (!$filename) {
            return response()->json(['message' => 'Aucune fiche disponible'], 404);
        }

        $path = storage_path("app/public/fiches/{$filename}");

        if (!file_exists($path)) {
            // Régénérer la fiche si elle n'existe pas
            if ($candidature->statut === 'en_attente' || $candidature->statut === 'documents_a_corriger') {
                $this->candidatureService->genererFicheProvisoire($candidature);
            } else {
                $this->candidatureService->genererConvocation($candidature);
            }
        }

        if (!file_exists($path)) {
            return response()->json(['message' => 'Erreur lors de la génération de la fiche'], 500);
        }

        return response()->download($path, $filename);
    }

    /**
     * Télécharger le QR Code seul
     */
    public function telechargerQRCode($id)
    {
        $candidature = auth()->user()->candidatures()->findOrFail($id);

        if (!$candidature->qr_code_data) {
            return response()->json(['message' => 'QR Code non disponible. Votre candidature doit être validée d\'abord.'], 404);
        }

        $qrCodePath = storage_path("app/public/qrcodes/{$candidature->code_candidat}.png");

        if (!file_exists($qrCodePath)) {
            return response()->json(['message' => 'QR Code introuvable'], 404);
        }

        return response()->download($qrCodePath, "qrcode_{$candidature->code_candidat}.png");
    }

    /**
     * Télécharger la fiche de candidature (version publique pour tests - À SUPPRIMER EN PRODUCTION)
     */
    public function telechargerFichePublic($id)
    {
        $candidature = Candidature::with([
            'concours',
            'centreDepot',
            'centreExamen',
            'salleExamen',
            'user'
        ])->findOrFail($id);

        // Déterminer quel type de fiche télécharger
        $filename = match($candidature->statut) {
            'en_attente', 'documents_a_corriger' => "fiche_provisoire_{$candidature->code_candidat}.pdf",
            'valide_depot', 'convoque', 'present', 'absent' => "convocation_{$candidature->code_candidat}.pdf",
            default => null,
        };

        if (!$filename) {
            return response()->json(['message' => 'Aucune fiche disponible'], 404);
        }

        $path = storage_path("app/public/fiches/{$filename}");

        if (!file_exists($path)) {
            // Régénérer la fiche si elle n'existe pas
            if ($candidature->statut === 'en_attente' || $candidature->statut === 'documents_a_corriger') {
                $this->candidatureService->genererFicheProvisoire($candidature);
            } else {
                $this->candidatureService->genererConvocation($candidature);
            }
        }

        if (!file_exists($path)) {
            return response()->json(['message' => 'Erreur lors de la génération de la fiche'], 500);
        }

        return response()->download($path, $filename);
    }
}
