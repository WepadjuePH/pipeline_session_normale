<?php

namespace Tests\Helpers;

class CandidatureTestData
{
    public static function getDefaultData(array $overrides = []): array
    {
        return array_merge([
            'code_candidat' => 'TEST-001',
            'statut' => 'en_attente',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'sexe' => 'masculin',
            'nationalite' => 'Camerounaise',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi',
            'telephone' => '237600000000',
            'adresse' => 'Test Address',
            'premiere_langue' => 'Français',
            'cni' => '123456789',
            'filiere' => 'Mathématiques',
            'cursus' => 'Licence',
            'diplome_admission' => 'Baccalauréat',
            'mention_diplome' => 'assez_bien',
            'annee_diplome' => 2020,
            'nom_pere' => 'Père Test',
            'telephone_pere' => '237600000001',
            'nom_mere' => 'Mère Test',
            'telephone_mere' => '237600000002',
        ], $overrides);
    }

    public static function getCompleteData(array $overrides = []): array
    {
        return array_merge(self::getDefaultData(), [
            'document_cni' => 'path/cni.jpg',
            'document_diplome' => 'path/diplome.jpg',
            'document_acte_naissance' => 'path/acte.jpg',
            'document_recu_paiement' => 'path/recu.jpg',
            'photo_candidat' => 'path/photo.jpg',
        ], $overrides);
    }
}
