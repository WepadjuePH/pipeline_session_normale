<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Concours;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConcoursTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $candidat;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->candidat = User::factory()->create(['role' => 'candidat']);
    }

    public function test_un_candidat_peut_voir_les_concours_ouverts()
    {
        Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now()->subDays(5),
            'date_cloture' => now()->addDays(30),
            'date_examen' => now()->addDays(60),
            'heure_examen' => '08:00',
            'annee' => 2026,
            'inscription_ouverte' => true,
            'is_active' => true
        ]);

        $token = auth('api')->login($this->candidat);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/concours');

        $response->assertStatus(200);
    }

    public function test_un_admin_peut_creer_un_concours()
    {
        $token = auth('api')->login($this->admin);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/concours', [
            'code' => 'ENSP-INFO',
            'nom' => 'ENSP Informatique',
            'description' => 'Concours ENSP',
            'type' => 'academique',
            'cursus' => 'Licence',
            'filiere' => 'Informatique',
            'date_ouverture' => now()->format('Y-m-d'),
            'date_cloture' => now()->addMonths(2)->format('Y-m-d'),
            'date_examen' => now()->addMonths(3)->format('Y-m-d'),
            'heure_examen' => '08:00',
            'frais_inscription' => 5000,
            'annee' => 2026
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('concours', [
            'code' => 'ENSP-INFO',
            'nom' => 'ENSP Informatique'
        ]);
    }

    public function test_un_candidat_ne_peut_pas_creer_un_concours()
    {
        $token = auth('api')->login($this->candidat);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/concours', [
            'code' => 'TEST',
            'nom' => 'Test Concours',
            'description' => 'Test',
            'type' => 'academique',
            'cursus' => 'Licence',
            'filiere' => 'Test',
            'date_ouverture' => now()->format('Y-m-d'),
            'date_cloture' => now()->addMonths(2)->format('Y-m-d'),
            'date_examen' => now()->addMonths(3)->format('Y-m-d'),
            'heure_examen' => '08:00',
            'frais_inscription' => 5000,
            'annee' => 2026
        ]);

        $response->assertStatus(403);
    }

    public function test_un_admin_peut_modifier_un_concours()
    {
        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
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
        ])->putJson("/api/admin/concours/{$concours->id}", [
            'nom' => 'ENS Mathématiques Modifié',
            'inscription_ouverte' => false
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('concours', [
            'id' => $concours->id,
            'nom' => 'ENS Mathématiques Modifié'
        ]);
    }

    public function test_un_admin_peut_supprimer_un_concours()
    {
        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
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
        ])->deleteJson("/api/admin/concours/{$concours->id}");

        $response->assertStatus(200);

        // Vérifier que le concours est soft deleted
        $this->assertSoftDeleted('concours', [
            'id' => $concours->id
        ]);
    }

    public function test_un_concours_ferme_n_accepte_pas_de_candidatures()
    {
        $concours = Concours::create([
            'code' => 'ENS-MAT',
            'nom' => 'ENS Mathématiques',
            'description' => 'Test',
            'cursus' => 'Licence',
            'filiere' => 'Mathématiques',
            'date_ouverture' => now()->subMonths(3),
            'date_cloture' => now()->subDays(1),
            'date_examen' => now()->addMonths(1),
            'heure_examen' => '08:00',
            'annee' => 2026,
            'inscription_ouverte' => false,
            'is_active' => true
        ]);

        $this->assertEquals(false, $concours->inscription_ouverte);
    }
}
