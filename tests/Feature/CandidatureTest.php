<?php

namespace Tests\Feature;

use Tests\TestCase;
// use App\Models\User;
// use App\Models\Candidature;
// use App\Models\Concours;
// use App\Models\CentreDepot;
// use App\Models\CentreExamen;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Http\UploadedFile;
// use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class CandidatureTest extends TestCase
{
    use RefreshDatabase;

    protected $candidat;
    protected $concours;
    protected $centreDepot;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     // Créer un candidat de test
    //     $this->candidat = User::factory()->create([
    //         'nom' => 'Test',
    //         'prenom' => 'Candidat',
    //         'email' => 'candidat@test.com',
    //         'telephone' => '237600000000',
    //         'role' => 'candidat',
    //         'password' => bcrypt('password123')
    //     ]);

    //     // Créer un concours de test
    //     $this->concours = Concours::create([
    //         'code' => 'ENS-MAT',
    //         'nom' => 'ENS-MAT-2026',
    //         'description' => 'Test concours',
    //         'cursus' => 'Licence',
    //         'filiere' => 'Mathématiques',
    //         'date_ouverture' => now(),
    //         'date_cloture' => now()->addMonths(2),
    //         'date_examen' => now()->addMonths(3),
    //         'heure_examen' => '08:00',
    //         'annee' => 2026,
    //         'inscription_ouverte' => true,
    //         'is_active' => true
    //     ]);

    //     // Créer un centre de dépôt
    //     $this->centreDepot = CentreDepot::create([
    //         'code' => 'CD-YDE-01',
    //         'nom' => 'Centre Yaoundé',
    //         'ville' => 'Yaoundé',
    //         'adresse' => 'Test Address',
    //         'region_id' => \App\Models\Region::firstOrCreate(['nom' => 'Centre', 'code' => 'CE'])->id
    //     ]);

    //     Storage::fake('public');
    // }

    // protected function createFakeImageFile(string $name = 'photo.jpg')
    // {
    //     $jpegData = base64_decode(
    //         '/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCABkAGQDAREAAhEBAxEB/8QAFwAAAwEAAAAAAAAAAAAAAAAAAAMEBv/EABUBAQEAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAcT/AP/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQMBAT8A/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAgBAgEBPwA//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAGPwJ//8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPyA//9k='
    //     );

    //     $path = sys_get_temp_dir() . '/' . $name;
    //     file_put_contents($path, $jpegData);

    //     return new UploadedFile($path, $name, 'image/jpeg', null, true);
    // }

    /** @test */
    // public function un_candidat_peut_soumettre_une_candidature()
    // {
    //     // Authentifier le candidat via JWT
    //     $token = JWTAuth::fromUser($this->candidat);

    //     // Préparer les données de candidature
    //     $data = [
    //         'concours_id' => $this->concours->id,
    //         'centre_depot_id' => $this->centreDepot->id,
    //         'nom' => 'Test',
    //         'prenom' => 'Candidat',
    //         'date_naissance' => '2000-01-01',
    //         'lieu_naissance' => 'Yaoundé',
    //         'sexe' => 'masculin',
    //         'nationalite' => 'Camerounaise',
    //         'cni' => '123456789',
    //         'telephone' => '237600000000',
    //         'adresse' => 'Test Address',
    //         'region_origine' => 'Centre',
    //         'departement_origine' => 'Mfoundi',
    //         'premiere_langue' => 'Français',
    //         'nom_pere' => 'Père Test',
    //         'telephone_pere' => '237600000001',
    //         'nom_mere' => 'Mère Test',
    //         'telephone_mere' => '237600000002',
    //         'filiere' => 'Mathématiques',
    //         'diplome_admission' => 'Baccalauréat',
    //         'annee_diplome' => '2020',
    //         'mention_diplome' => 'bien',
    //         'document_cni' => UploadedFile::fake()->image('cni.jpg'),
    //         'document_diplome' => UploadedFile::fake()->image('diplome.jpg'),
    //         'document_acte_naissance' => UploadedFile::fake()->image('acte.jpg'),
    //         'document_recu_paiement' => UploadedFile::fake()->image('recu.jpg'),
    //         'photo_candidat' => UploadedFile::fake()->image('photo.jpg')
    //     ];

    //     // Envoyer la requête
    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->postJson('/api/candidat/candidatures', $data);

    //     // Vérifier la réponse
    //     $response->assertStatus(201);

    //     // Vérifier que la candidature existe en base
    //     $this->assertDatabaseHas('candidatures', [
    //         'user_id' => $this->candidat->id,
    //         'concours_id' => $this->concours->id,
    //         'statut' => 'en_attente'
    //     ]);
    // }

    /** @test */
    // public function un_candidat_ne_peut_pas_soumettre_sans_documents()
    // {
    //     $token = auth('api')->login($this->candidat);

    //     $data = [
    //         'concours_id' => $this->concours->id,
    //         'centre_depot_id' => $this->centreDepot->id,
    //         'nom' => 'Test',
    //         'prenom' => 'Candidat',
    //         // Documents manquants
    //     ];

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->postJson('/api/candidat/candidatures', $data);

    //     $response->assertStatus(422); // Validation error
    // }

    /** @test */
    // public function un_candidat_peut_voir_ses_candidatures()
    // {
    //     $token = auth('api')->login($this->candidat);

    //     // Créer une candidature
    //     $candidature = Candidature::create([
    //         'user_id' => $this->candidat->id,
    //         'concours_id' => $this->concours->id,
    //         'centre_depot_id' => $this->centreDepot->id,
    //         'code_candidat' => 'TEST-2026-001',
    //         'statut' => 'en_attente',
    //         'nom' => 'Test',
    //         'prenom' => 'Candidat',
    //         'date_naissance' => '2000-01-01',
    //         'lieu_naissance' => 'Yaoundé',
    //         'sexe' => 'masculin',
    //         'cni' => '123456789',
    //         'telephone' => '237600000000',
    //         'adresse' => 'Test',
    //         'filiere' => 'Math',
    //         'diplome_admission' => 'Bac',
    //         'annee_diplome' => '2020',
    //         'premiere_langue' => 'Français',
    //         'region_origine' => 'Centre',
    //         'departement_origine' => 'Mfoundi',
    //         'nom_pere' => 'Père Test',
    //         'telephone_pere' => '237600000001',
    //         'nom_mere' => 'Mère Test',
    //         'telephone_mere' => '237600000002'
    //     ]);

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->getJson('/api/candidat/candidatures');

    //     $response->assertStatus(200);
    // }

    /** @test */
    // public function un_agent_depot_peut_valider_une_candidature()
    // {
    //     // Créer un agent de dépôt
    //     $agent = User::factory()->create([
    //         'role' => 'agent_depot',
    //         'centre_depot_id' => $this->centreDepot->id
    //     ]);

    //     // Créer un centre d'examen et une salle
    //     $centreExamen = CentreExamen::create([
    //         'code' => 'CE-YDE-01',
    //         'nom' => 'Centre Examen Yaoundé',
    //         'ville' => 'Yaoundé',
    //         'adresse' => 'Test',
    //         'region_id' => \App\Models\Region::firstOrCreate(['nom' => 'Centre', 'code' => 'CE'])->id
    //     ]);

    //     $salleExamen = \App\Models\SalleExamen::create([
    //         'centre_examen_id' => $centreExamen->id,
    //         'nom' => 'Salle A',
    //         'code' => 'SA-01',
    //         'capacite' => 50
    //     ]);

    //     $token = auth('api')->login($agent);

    //     // Créer une candidature en attente
    //     $candidature = Candidature::create([
    //         'user_id' => $this->candidat->id,
    //         'concours_id' => $this->concours->id,
    //         'centre_depot_id' => $this->centreDepot->id,
    //         'code_candidat' => 'TEST-2026-001',
    //         'statut' => 'en_attente',
    //         'nom' => 'Test',
    //         'prenom' => 'Candidat',
    //         'date_naissance' => '2000-01-01',
    //         'lieu_naissance' => 'Yaoundé',
    //         'sexe' => 'masculin',
    //         'cni' => '123456789',
    //         'telephone' => '237600000000',
    //         'adresse' => 'Test',
    //         'filiere' => 'Math',
    //         'diplome_admission' => 'Bac',
    //         'annee_diplome' => '2020',
    //         'premiere_langue' => 'Français',
    //         'region_origine' => 'Centre',
    //         'departement_origine' => 'Mfoundi',
    //         'nom_pere' => 'Père Test',
    //         'telephone_pere' => '237600000001',
    //         'nom_mere' => 'Mère Test',
    //         'telephone_mere' => '237600000002',
    //         'document_cni' => 'path/to/cni.jpg',
    //         'document_diplome' => 'path/to/diplome.jpg',
    //         'document_acte_naissance' => 'path/to/acte.jpg',
    //         'document_recu_paiement' => 'path/to/recu.jpg',
    //         'photo_candidat' => 'path/to/photo.jpg'
    //     ]);

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->postJson("/api/agent/depot/candidatures/{$candidature->id}/valider", [
    //         'salle_examen_id' => $salleExamen->id
    //     ]);

    //     $response->assertStatus(200);

    //     // Vérifier que le statut a changé
    //     $this->assertDatabaseHas('candidatures', [
    //         'id' => $candidature->id,
    //         'statut' => 'valide_depot',
    //         'salle_examen_id' => $salleExamen->id
    //     ]);
    // }

    /** @test */
    // public function un_agent_depot_peut_rejeter_une_candidature()
    // {
    //     $agent = User::factory()->create([
    //         'role' => 'agent_depot',
    //         'centre_depot_id' => $this->centreDepot->id
    //     ]);

    //     $token = auth('api')->login($agent);

    //     $candidature = Candidature::create([
    //         'user_id' => $this->candidat->id,
    //         'concours_id' => $this->concours->id,
    //         'centre_depot_id' => $this->centreDepot->id,
    //         'code_candidat' => 'TEST-2026-001',
    //         'statut' => 'en_attente',
    //         'nom' => 'Test',
    //         'prenom' => 'Candidat',
    //         'date_naissance' => '2000-01-01',
    //         'lieu_naissance' => 'Yaoundé',
    //         'sexe' => 'masculin',
    //         'cni' => '123456789',
    //         'telephone' => '237600000000',
    //         'adresse' => 'Test',
    //         'filiere' => 'Math',
    //         'diplome_admission' => 'Bac',
    //         'annee_diplome' => '2020',
    //         'premiere_langue' => 'Français',
    //         'region_origine' => 'Centre',
    //         'departement_origine' => 'Mfoundi',
    //         'nom_pere' => 'Père Test',
    //         'telephone_pere' => '237600000001',
    //         'nom_mere' => 'Mère Test',
    //         'telephone_mere' => '237600000002'
    //     ]);

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->postJson("/api/agent/depot/candidatures/{$candidature->id}/rejeter", [
    //         'motif' => 'Documents non conformes'
    //     ]);

    //     $response->assertStatus(200);

    //     $this->assertDatabaseHas('candidatures', [
    //         'id' => $candidature->id,
    //         'statut' => 'documents_a_corriger'
    //     ]);
    // }

    /** @test */
    // public function un_candidat_ne_peut_pas_acceder_aux_candidatures_dun_autre()
    // {
    //     $token = auth('api')->login($this->candidat);

    //     // Créer un autre candidat
    //     $autreCandidatUser = User::factory()->create(['role' => 'candidat']);

    //     $autreCandidature = Candidature::create([
    //         'user_id' => $autreCandidatUser->id,
    //         'concours_id' => $this->concours->id,
    //         'centre_depot_id' => $this->centreDepot->id,
    //         'code_candidat' => 'TEST-2026-002',
    //         'statut' => 'en_attente',
    //         'nom' => 'Autre',
    //         'prenom' => 'Candidat',
    //         'date_naissance' => '2000-01-01',
    //         'lieu_naissance' => 'Yaoundé',
    //         'sexe' => 'masculin',
    //         'cni' => '987654321',
    //         'telephone' => '237600000001',
    //         'adresse' => 'Test',
    //         'filiere' => 'Math',
    //         'diplome_admission' => 'Bac',
    //         'annee_diplome' => '2020',
    //         'premiere_langue' => 'Français',
    //         'region_origine' => 'Centre',
    //         'departement_origine' => 'Mfoundi',
    //         'nom_pere' => 'Père Autre',
    //         'telephone_pere' => '237600000002',
    //         'nom_mere' => 'Mère Autre',
    //         'telephone_mere' => '237600000003'
    //     ]);

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->getJson("/api/candidat/candidatures/{$autreCandidature->id}");

    //     // 404 because the candidature is not found in the user's candidatures
    //     $response->assertStatus(404);
    // }
}

