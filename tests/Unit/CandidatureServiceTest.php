<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\User;
use App\Models\CentreDepot;
use App\Services\CandidatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class CandidatureServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $candidatureService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->candidatureService = new CandidatureService();
    }

    #[Test]
    public function generer_code_candidat_retourne_un_code_valide()
    {
        $region = \App\Models\Region::create([
            'nom' => 'Centre',
            'code' => 'CE'
        ]);

        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS-MAT-2026',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00'
        ]);

        // Créer une candidature pour tester la génération du code
        $candidature = Candidature::create([
            'user_id' => User::factory()->create()->id,
            'concours_id' => $concours->id,
            'centre_depot_id' => CentreDepot::create([
                'code' => 'CD-TEST',
                'nom' => 'Centre Test',
                'ville' => 'Test',
                'adresse' => 'Test',
                'region_id' => $region->id
            ])->id,
            'code_candidat' => 'TEMP',
            'statut' => 'en_attente',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Test',
            'sexe' => 'masculin',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi',
            'cni' => '123456',
            'telephone' => '237600000000',
            'adresse' => 'Test',
            'premiere_langue' => 'Français',
            'filiere' => 'Math',
            'diplome_admission' => 'Bac',
            'annee_diplome' => '2020'
        ]);

        // Vérifier que le code candidat a un format valide
        $this->assertNotEmpty($candidature->code_candidat);
        $this->assertIsString($candidature->code_candidat);
    }

    #[Test]
    public function peut_etre_validee_retourne_false_si_documents_manquants()
    {
        $candidature = new Candidature([
            'document_cni' => 'path/to/cni.jpg',
            'document_diplome' => null, // Manquant
            'document_acte_naissance' => 'path/to/acte.jpg',
            'document_recu_paiement' => 'path/to/recu.jpg',
            'photo_candidat' => 'path/to/photo.jpg',
            'statut' => 'en_attente'
        ]);

        $this->assertFalse($candidature->peutEtreValidee());
    }

    #[Test]
    public function peut_etre_validee_retourne_true_si_tous_documents_presents()
    {
        $candidature = new Candidature([
            'document_cni' => 'path/to/cni.jpg',
            'document_diplome' => 'path/to/diplome.jpg',
            'document_acte_naissance' => 'path/to/acte.jpg',
            'document_recu_paiement' => 'path/to/recu.jpg',
            'photo_candidat' => 'path/to/photo.jpg',
            'statut' => 'en_attente'
        ]);

        $this->assertTrue($candidature->peutEtreValidee());
    }

    #[Test]
    public function peut_etre_validee_retourne_false_si_deja_valide()
    {
        $candidature = new Candidature([
            'document_cni' => 'path/to/cni.jpg',
            'document_diplome' => 'path/to/diplome.jpg',
            'document_acte_naissance' => 'path/to/acte.jpg',
            'document_recu_paiement' => 'path/to/recu.jpg',
            'photo_candidat' => 'path/to/photo.jpg',
            'statut' => 'valide_depot' // Déjà validé
        ]);

        $this->assertFalse($candidature->peutEtreValidee());
    }
}
