<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;

class PublicFicheController extends Controller
{
    /**
     * Télécharger une fiche publiquement avec token sécurisé
     */
    public function telechargerFiche($code_candidat, $token)
    {
        // Trouver la candidature
        $candidature = Candidature::where('code_candidat', $code_candidat)->first();

        if (!$candidature) {
            return response()->json(['message' => 'Candidature introuvable'], 404);
        }

        // Vérifier le token
        if (!$this->verifierToken($candidature, $token)) {
            return response()->json(['message' => 'Token invalide ou expiré'], 403);
        }

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
            return response()->json(['message' => 'Fiche introuvable'], 404);
        }

        return response()->download($path, $filename);
    }

    /**
     * Générer un token sécurisé pour une candidature
     */
    public static function genererToken(Candidature $candidature): string
    {
        $data = $candidature->code_candidat . '|' . $candidature->user->email . '|' . config('app.key');
        return hash('sha256', $data);
    }

    /**
     * Vérifier le token
     */
    private function verifierToken(Candidature $candidature, string $token): bool
    {
        $expectedToken = self::genererToken($candidature);
        return hash_equals($expectedToken, $token);
    }
}
