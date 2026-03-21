<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Candidature;
use App\Models\User;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidatureModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $concours;
    protected $centreDepot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $region = Region::create([
            'nom' => 'Centre',
            'code' => 'CE'
        ]);

        $this->centreDepot = CentreDepot::create([
            'code' => 'CD-TEST',
            'nom' => 'Centre Test',
            'ville' => 'Test',
            'adresse' => 'Test',
            'region_id' => $region->id
        ]);

        $this->concours = Concours::create([
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
    }

    protected function candidatureData($overrides = [])
    {
        return array_merge([
            'user_id' => $this->user->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'en_attente',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
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
        ], $overrides);
    }

    public function test_une_candidature_peut_etre_creee()
    {
        $candidature = Candidature::create($this->candidatureData());

        $this->assertInstanceOf(Candidature::class, $candidature);
        $this->assertEquals('TEST-001', $candidature->code_candidat);
    }

    public function test_le_code_candidat_doit_etre_unique()
    {
        Candidature::create($this->candidatureData());

        $this->expectException(\Illuminate\Database\QueryException::class);

        Candidature::create($this->candidatureData(['cni' => '654321', 'telephone' => '237600000001']));
    }

    public function test_une_candidature_appartient_a_un_utilisateur()
    {
        $candidature = Candidature::create($this->candidatureData());

        $this->assertInstanceOf(User::class, $candidature->user);
        $this->assertEquals($this->user->id, $candidature->user->id);
    }

    public function test_une_candidature_appartient_a_un_concours()
    {
        $candidature = Candidature::create($this->candidatureData());

        $this->assertInstanceOf(Concours::class, $candidature->concours);
        $this->assertEquals($this->concours->id, $candidature->concours->id);
    }

    public function test_le_statut_par_defaut_est_en_attente()
    {
        $data = $this->candidatureData();
        unset($data['statut']); // Don't set statut to test default value
        
        $candidature = Candidature::create($data);
        $candidature->refresh(); // Refresh to get default value from database

        $this->assertEquals('en_attente', $candidature->statut);
    }

    public function test_verification_des_statuts_possibles()
    {
        $statuts = [
            'en_attente',
            'valide_depot',
            'documents_a_corriger',
            'present',
            'absent',
            'rejete'
        ];

        foreach ($statuts as $index => $statut) {
            $candidature = Candidature::create($this->candidatureData([
                'code_candidat' => 'TEST-' . str_pad($index, 3, '0', STR_PAD_LEFT),
                'statut' => $statut,
                'cni' => '123456' . $index,
                'telephone' => '23760000000' . $index
            ]));

            $this->assertEquals($statut, $candidature->statut);
        }
    }

    public function test_la_date_naissance_est_convertie_en_carbon()
    {
        $candidature = Candidature::create($this->candidatureData());

        $this->assertInstanceOf(\Carbon\Carbon::class, $candidature->date_naissance);
    }
}
