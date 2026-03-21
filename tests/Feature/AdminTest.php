<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\CentreExamen;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $region;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        $this->region = Region::create([
            'nom' => 'Centre',
            'code' => 'CE'
        ]);
    }

    public function test_un_admin_peut_voir_toutes_les_candidatures()
    {
        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/candidatures');

        $response->assertStatus(200);
    }

    public function test_un_admin_peut_creer_un_utilisateur()
    {
        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/users', [
            'nom' => 'Nouvel',
            'prenom' => 'Agent',
            'email' => 'agent@test.com',
            'telephone' => '237600000000',
            'role' => 'agent_depot',
            'password' => 'password123'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'agent@test.com',
            'role' => 'agent_depot'
        ]);
    }

    public function test_un_admin_peut_modifier_un_utilisateur()
    {
        $user = User::factory()->create([
            'role' => 'agent_depot'
        ]);

        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/users/{$user->id}", [
            'nom' => 'Nom Modifié',
            'role' => 'agent_examen'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nom' => 'Nom Modifié',
            'role' => 'agent_examen'
        ]);
    }

    public function test_un_admin_peut_supprimer_un_utilisateur()
    {
        $user = User::factory()->create();

        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200);

        // Vérifier que l'utilisateur est soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }

    public function test_un_admin_peut_voir_les_statistiques()
    {
        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/statistiques/globales');

        $response->assertStatus(200);
    }

    public function test_un_admin_peut_creer_un_centre_depot()
    {
        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/centres-depot', [
            'code' => 'CD-TEST',
            'nom' => 'Centre Test',
            'ville' => 'Yaoundé',
            'adresse' => 'Test Address',
            'region_id' => $this->region->id
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('centres_depot', [
            'code' => 'CD-TEST',
            'nom' => 'Centre Test'
        ]);
    }

    public function test_un_admin_peut_creer_un_centre_examen()
    {
        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/centres-examen', [
            'code' => 'CE-TEST',
            'nom' => 'Centre Examen Test',
            'ville' => 'Yaoundé',
            'adresse' => 'Test Address',
            'region_id' => $this->region->id
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('centres_examen', [
            'code' => 'CE-TEST',
            'nom' => 'Centre Examen Test'
        ]);
    }

    public function test_un_admin_peut_voir_tous_les_rapports()
    {
        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/rapports');

        $response->assertStatus(200);
    }

    public function test_un_admin_peut_exporter_les_candidatures()
    {
        $concours = Concours::create([
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

        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/admin/concours/{$concours->id}/export");

        $response->assertStatus(200);
    }

    public function test_un_non_admin_ne_peut_pas_acceder_aux_fonctions_admin()
    {
        $candidat = User::factory()->create(['role' => 'candidat']);
        $token = auth('api')->login($candidat);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/candidatures');

        $response->assertStatus(403);
    }
}
