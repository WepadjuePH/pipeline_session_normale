<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreExamen;
use App\Models\CentreDepot;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgentExamenTest extends TestCase
{
    use RefreshDatabase;

    protected $agentExamen;
    protected $candidat;
    protected $concours;
    protected $centreExamen;
    protected $centreDepot;
    protected $region;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer une région
        $this->region = Region::create([
            'nom' => 'Centre',
            'code' => 'CE'
        ]);

        // Créer un centre d'examen
        $this->centreExamen = CentreExamen::create([
            'code' => 'CE-YDE-01',
            'nom' => 'Centre Examen Yaoundé',
            'ville' => 'Yaoundé',
            'adresse' => 'Test Address',
            'region_id' => $this->region->id
        ]);

        // Créer un centre de dépôt
        $this->centreDepot = CentreDepot::create([
            'code' => 'CD-YDE-01',
            'nom' => 'Centre Dépôt Yaoundé',
            'ville' => 'Yaoundé',
            'adresse' => 'Test Address',
            'region_id' => $this->region->id
        ]);

        // Créer un agent d'examen
        $this->agentExamen = User::factory()->create([
            'role' => 'agent_examen',
            'centre_examen_id' => $this->centreExamen->id
        ]);

        // Créer un candidat
        $this->candidat = User::factory()->create(['role' => 'candidat']);

        // Créer un concours
        $this->concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS-MAT-2026',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now(),
            'date_cloture' => now()->addMonths(2),
            'date_examen' => now()->addMonths(3),
            'heure_examen' => '08:00',
            'annee' => 2026,
            'inscription_ouverte' => true,
            'is_active' => true
        ]);
    }

    public function test_un_agent_examen_peut_voir_les_candidatures_de_son_centre()
    {
        // Créer une candidature validée pour ce centre
        Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'centre_examen_id' => $this->centreExamen->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'valide_depot',
            'nom' => 'Test',
            'prenom' => 'User',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'sexe' => 'masculin',
            'cni' => '123456',
            'telephone' => '237600000000',
            'adresse' => 'Test',
            'filiere' => 'Math',
            'diplome_admission' => 'Bac',
            'annee_diplome' => '2020',
            'premiere_langue' => 'Français',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi'
        ]);

        $token = auth('api')->login($this->agentExamen);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/agent/examen/candidatures');

        $response->assertStatus(200);
    }

    public function test_un_agent_examen_peut_scanner_un_qr_code()
    {
        $candidature = Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'centre_examen_id' => $this->centreExamen->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'valide_depot',
            'nom' => 'Test',
            'prenom' => 'User',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'sexe' => 'masculin',
            'cni' => '123456',
            'telephone' => '237600000000',
            'adresse' => 'Test',
            'filiere' => 'Math',
            'diplome_admission' => 'Bac',
            'annee_diplome' => '2020',
            'premiere_langue' => 'Français',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi'
        ]);

        $token = auth('api')->login($this->agentExamen);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/agent/examen/scan-qr', [
            'code_candidat' => 'TEST-001'
        ]);

        $response->assertStatus(200);
    }

    public function test_un_agent_examen_peut_marquer_presence()
    {
        $candidature = Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'centre_examen_id' => $this->centreExamen->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'valide_depot',
            'nom' => 'Test',
            'prenom' => 'User',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'sexe' => 'masculin',
            'cni' => '123456',
            'telephone' => '237600000000',
            'adresse' => 'Test',
            'filiere' => 'Math',
            'diplome_admission' => 'Bac',
            'annee_diplome' => '2020',
            'premiere_langue' => 'Français',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi'
        ]);

        $token = auth('api')->login($this->agentExamen);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/agent/examen/candidatures/{$candidature->id}/marquer-present");

        $response->assertStatus(200);

        $this->assertDatabaseHas('candidatures', [
            'id' => $candidature->id,
            'statut' => 'present'
        ]);
    }

    public function test_un_agent_examen_peut_generer_un_rapport()
    {
        $token = auth('api')->login($this->agentExamen);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/rapports', [
            'concours_id' => $this->concours->id,
            'type' => 'examen',
            'titre' => 'Rapport Test',
            'periode_debut' => now()->format('Y-m-d'),
            'periode_fin' => now()->format('Y-m-d'),
            'statistiques' => [],
            'observations' => 'Test rapport examen'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('rapports', [
            'agent_id' => $this->agentExamen->id,
            'concours_id' => $this->concours->id,
            'type' => 'examen'
        ]);
    }

    public function test_un_agent_examen_ne_peut_pas_scanner_candidat_autre_centre()
    {
        // Créer un autre centre
        $autreCentre = CentreExamen::create([
            'code' => 'CE-DLA-01',
            'nom' => 'Centre Douala',
            'ville' => 'Douala',
            'adresse' => 'Test',
            'region_id' => $this->region->id
        ]);

        $candidature = Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'centre_examen_id' => $autreCentre->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'valide_depot',
            'nom' => 'Test',
            'prenom' => 'User',
            'date_naissance' => '2000-01-01',
            'lieu_naissance' => 'Yaoundé',
            'sexe' => 'masculin',
            'cni' => '123456',
            'telephone' => '237600000000',
            'adresse' => 'Test',
            'filiere' => 'Math',
            'diplome_admission' => 'Bac',
            'annee_diplome' => '2020',
            'premiere_langue' => 'Français',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi'
        ]);

        $token = auth('api')->login($this->agentExamen);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/agent/examen/scan-qr', [
            'code_candidat' => 'TEST-001'
        ]);

        $response->assertStatus(403);
    }
}
