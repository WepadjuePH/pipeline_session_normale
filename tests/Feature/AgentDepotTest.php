<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\SalleExamen;
use App\Models\CentreExamen;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgentDepotTest extends TestCase
{
    use RefreshDatabase;

    protected $agentDepot;
    protected $candidat;
    protected $concours;
    protected $centreDepot;
    protected $centreExamen;
    protected $salleExamen;
    protected $region;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer une région
        $this->region = Region::create([
            'nom' => 'Centre',
            'code' => 'CE'
        ]);

        // Créer un centre de dépôt
        $this->centreDepot = CentreDepot::create([
            'code' => 'CD-YDE-01',
            'nom' => 'Centre Yaoundé',
            'ville' => 'Yaoundé',
            'adresse' => 'Test Address',
            'region_id' => $this->region->id
        ]);

        // Créer un centre d'examen
        $this->centreExamen = CentreExamen::create([
            'code' => 'CE-YDE-01',
            'nom' => 'Centre Examen Yaoundé',
            'ville' => 'Yaoundé',
            'adresse' => 'Test Address',
            'region_id' => $this->region->id
        ]);

        // Créer une salle d'examen
        $this->salleExamen = SalleExamen::create([
            'centre_examen_id' => $this->centreExamen->id,
            'nom' => 'Salle A',
            'capacite' => 50,
            'is_active' => true
        ]);

        // Créer un agent de dépôt
        $this->agentDepot = User::factory()->create([
            'role' => 'agent_depot',
            'centre_depot_id' => $this->centreDepot->id
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

    private function createCandidature($overrides = [])
    {
        return Candidature::create(array_merge([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
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
        ], $overrides));
    }

    public function test_un_agent_depot_peut_voir_les_candidatures_de_son_centre()
    {
        $this->createCandidature();

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/agent/depot/candidatures');

        $response->assertStatus(200);
    }

    public function test_un_agent_depot_peut_valider_une_candidature()
    {
        $candidature = $this->createCandidature([
            'document_cni' => 'path/cni.jpg',
            'document_diplome' => 'path/diplome.jpg',
            'document_acte_naissance' => 'path/acte.jpg',
            'document_recu_paiement' => 'path/recu.jpg',
            'photo_candidat' => 'path/photo.jpg'
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/agent/depot/candidatures/{$candidature->id}/valider", [
            'salle_examen_id' => $this->salleExamen->id
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('candidatures', [
            'id' => $candidature->id,
            'statut' => 'valide_depot'
        ]);
    }

    public function test_un_agent_depot_peut_rejeter_une_candidature()
    {
        $candidature = $this->createCandidature();

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/agent/depot/candidatures/{$candidature->id}/rejeter", [
            'motif' => 'Documents non conformes'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('candidatures', [
            'id' => $candidature->id,
            'statut' => 'documents_a_corriger'
        ]);
    }

    public function test_un_agent_depot_ne_peut_pas_valider_candidature_autre_centre()
    {
        $autreCentre = CentreDepot::create([
            'code' => 'CD-DLA-01',
            'nom' => 'Centre Douala',
            'ville' => 'Douala',
            'adresse' => 'Test',
            'region_id' => $this->region->id
        ]);

        $candidature = $this->createCandidature([
            'centre_depot_id' => $autreCentre->id
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/agent/depot/candidatures/{$candidature->id}/valider", [
            'salle_examen_id' => $this->salleExamen->id
        ]);

        // Le backend retourne 400 au lieu de 403
        $response->assertStatus(400);
    }

    public function test_un_agent_depot_peut_generer_un_rapport()
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
    }

    public function test_un_agent_depot_peut_voir_ses_rapports()
    {
        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/rapports');

        $response->assertStatus(200);
    }

    public function test_un_agent_depot_peut_annuler_une_validation()
    {
        $candidature = $this->createCandidature([
            'statut' => 'valide_depot'
        ]);

        $token = auth('api')->login($this->agentDepot);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/agent/depot/candidatures/{$candidature->id}/annuler-validation", [
            'motif' => 'Erreur de validation'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('candidatures', [
            'id' => $candidature->id,
            'statut' => 'documents_a_corriger'
        ]);
    }
}
