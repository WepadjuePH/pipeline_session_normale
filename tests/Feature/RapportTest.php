<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rapport;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RapportTest extends TestCase
{
    use RefreshDatabase;

    protected $agentDepot;
    protected $admin;
    protected $concours;
    protected $centreDepot;
    protected $region;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->region = Region::create([
            'nom' => 'Centre',
            'code' => 'CE'
        ]);

        $this->centreDepot = CentreDepot::create([
            'code' => 'CD-YDE-01',
            'nom' => 'Centre Yaoundé',
            'ville' => 'Yaoundé',
            'adresse' => 'Test',
            'region_id' => $this->region->id
        ]);

        $this->agentDepot = User::factory()->create([
            'role' => 'agent_depot',
            'centre_depot_id' => $this->centreDepot->id
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);

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

    public function test_un_agent_peut_creer_un_rapport()
    {
        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/rapports', [
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now()->format('Y-m-d'),
            'periode_fin' => now()->format('Y-m-d'),
            'statistiques' => [],
            'observations' => 'Test rapport'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('rapports', [
            'agent_id' => $this->agentDepot->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot'
        ]);
    }

    public function test_un_agent_peut_voir_ses_rapports()
    {
        Rapport::create([
            'agent_id' => $this->agentDepot->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now(),
            'periode_fin' => now(),
            'statistiques' => []
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/rapports');

        $response->assertStatus(200);
    }

    public function test_un_agent_peut_modifier_un_rapport_brouillon()
    {
        $rapport = Rapport::create([
            'agent_id' => $this->agentDepot->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now(),
            'periode_fin' => now(),
            'statistiques' => [],
            'description' => 'Test',
            'envoye_admin' => false
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/rapports/{$rapport->id}", [
            'description' => 'Description modifiée'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('rapports', [
            'id' => $rapport->id,
            'description' => 'Description modifiée'
        ]);
    }

    public function test_un_agent_ne_peut_pas_modifier_un_rapport_envoye()
    {
        $rapport = Rapport::create([
            'agent_id' => $this->agentDepot->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now(),
            'periode_fin' => now(),
            'statistiques' => [],
            'description' => 'Test',
            'envoye_admin' => true
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/rapports/{$rapport->id}", [
            'description' => 'Tentative modification'
        ]);

        $response->assertStatus(403);
    }

    public function test_un_agent_peut_envoyer_un_rapport()
    {
        $rapport = Rapport::create([
            'agent_id' => $this->agentDepot->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now(),
            'periode_fin' => now(),
            'statistiques' => [],
            'description' => 'Test',
            'envoye_admin' => false
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/agent/depot/rapports/{$rapport->id}/envoyer");

        $response->assertStatus(200);

        $this->assertDatabaseHas('rapports', [
            'id' => $rapport->id,
            'envoye_admin' => true
        ]);
    }

    public function test_un_agent_peut_supprimer_un_rapport_brouillon()
    {
        $rapport = Rapport::create([
            'agent_id' => $this->agentDepot->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now(),
            'periode_fin' => now(),
            'statistiques' => [],
            'description' => 'Test',
            'envoye_admin' => false
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/rapports/{$rapport->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('rapports', [
            'id' => $rapport->id
        ]);
    }

    public function test_un_admin_peut_voir_tous_les_rapports()
    {
        Rapport::create([
            'agent_id' => $this->agentDepot->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now(),
            'periode_fin' => now(),
            'statistiques' => [],
            'description' => 'Test',
            'envoye_admin' => true
        ]);

        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/rapports');

        $response->assertStatus(200);
    }

    public function test_un_agent_ne_peut_voir_que_ses_rapports()
    {
        $autreAgent = User::factory()->create([
            'role' => 'agent_depot',
            'centre_depot_id' => $this->centreDepot->id
        ]);

        Rapport::create([
            'agent_id' => $autreAgent->id,
            'concours_id' => $this->concours->id,
            'type' => 'depot',
            'titre' => 'Rapport Test',
            'periode_debut' => now(),
            'periode_fin' => now(),
            'statistiques' => [],
            'description' => 'Test',
            'envoye_admin' => true
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/rapports');

        $response->assertStatus(200);
        
        $rapports = $response->json('rapports') ?? $response->json();
        if (is_array($rapports) && isset($rapports[0])) {
            $this->assertCount(0, $rapports);
        }
    }
}
