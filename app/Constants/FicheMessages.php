<?php

namespace App\Constants;

class FicheMessages
{
    public const FICHE_PROVISOIRE = [
        'titre' => 'FICHE PROVISOIRE D\'ENROULEMENT',
        'documents_a_deposer' => [
            'Les documents uploadés sur la plateforme doivent être imprimés et certifiés',
            'Photocopie certifiée de l\'acte de naissance (moins de 3 mois)',
            'Photocopie certifiée du diplôme/attestation requis',
            'Certificat médical (moins de 3 mois)',
            'Certificat de nationalité',
            'CNI ou Passeport',
            '4 photos d\'identité 4x4',
            'Casier judiciaire (moins de 3 mois)',
            'Reçu de paiement des frais de concours',
        ],
        'prochaines_etapes' => [
            'Téléchargez cette fiche depuis votre espace candidat',
            'Imprimez la fiche',
            'Rassemblez tous les documents uploadés sur la plateforme',
            'Déposez vos documents au centre: {centre}',
            'Vous recevrez votre convocation avec QR code par email après validation',
        ],
        'delai_depot' => 'Avant la clôture des inscriptions',
        'horaires' => 'Lundi - Vendredi: 8h - 16h',
        'important' => 'Conservez tous les emails reçus. Vous recevrez votre convocation officielle avec QR code par email après validation de vos documents au centre de dépôt.',
        'contact' => 'hybrelwepadju@gmail.com',
    ];

    public const FICHE_CONVOCATION = [
        'titre' => 'CONVOCATION À L\'EXAMEN',
        'avertissement' => 'TRÈS IMPORTANT: Présentez-vous 30 minutes avant l\'heure du concours. Aucun retard ne sera toléré!',
        'documents_a_apporter' => [
            'Cette fiche/convocation imprimée (avec QR code visible)',
            'Carte Nationale d\'Identité (originale)',
            'Reçu de paiement des frais de concours',
            '2 stylos à bille (bleu ou noir)',
            'Crayon à papier et gomme',
            'Calculatrice non programmable (si autorisée)',
        ],
        'strictement_interdit' => [
            'Téléphones portables et montres connectées',
            'Tout appareil électronique (sauf calculatrice autorisée)',
            'Documents, notes, livres',
            'Communication avec d\'autres candidats',
        ],
        'deroulement' => [
            'Arrivée: Présentez-vous 30 min avant au centre',
            'Contrôle: L\'agent scannera votre QR code à l\'entrée',
            'Installation: Dirigez-vous vers votre salle et table',
            'Vérification: Présentez votre CNI et convocation',
            'Composition: Suivez les instructions du surveillant',
        ],
        'horaires' => [
            'Ouverture des portes: 07:00',
            'Fermeture des portes: 08:00 (AUCUN RETARD)',
            'Début de l\'épreuve: 08:00',
        ],
        'conclusion' => 'Bonne chance pour votre concours!',
        'signature' => 'Cordialement, L\'équipe SGEE',
    ];

    public const FICHE_REJET = [
        'titre' => 'NOTIFICATION DE REJET DE CANDIDATURE',
        'message' => 'Votre candidature a été rejetée pour la raison suivante:',
        'motif' => '{motif}',
        'action' => 'Vous pouvez soumettre une nouvelle candidature après correction des documents.',
        'contact' => 'Pour toute question, contactez-nous à hybrelwepadju@gmail.com',
        'signature' => 'Cordialement, L\'équipe SGEE',
    ];

    public static function getFicheProvisoire($centre)
    {
        $messages = self::FICHE_PROVISOIRE;
        $prochaines_etapes = array_map(
            fn($etape) => str_replace('{centre}', $centre, $etape),
            $messages['prochaines_etapes']
        );

        return array_merge($messages, ['prochaines_etapes' => $prochaines_etapes]);
    }

    public static function getFicheConvocation()
    {
        return self::FICHE_CONVOCATION;
    }

    public static function getFicheRejet($motif)
    {
        $messages = self::FICHE_REJET;
        $messages['motif'] = str_replace('{motif}', $motif, $messages['motif']);

        return $messages;
    }
}
