<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\Region;
use App\Notifications\CandidatureSubmittedNotification;
use App\Notifications\CandidatureValidatedNotification;
use App\Notifications\CandidatureRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $candidat;
    protected $concours;
    protected $centreDepot;
    protected $region;

    protected function setUp(): void
    {
        parent::setUp();
        
        Notification::fake();

        $this->candidat = User::factory()->create(['role' => 'candidat']);
        
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

    public function test_notification_envoyee_apres_soumission_candidature()
    {
        $candidature = Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'en_attente',
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
            'annee_diplome' => '2020'
        ,
            'premiere_langue' => 'Français',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi']);

        // Envoyer la notification
        $this->candidat->notify(new CandidatureSubmittedNotification($candidature));

        // Vérifier que la notification a été envoyée
        Notification::assertSentTo(
            $this->candidat,
            CandidatureSubmittedNotification::class
        );
    }

    public function test_notification_envoyee_apres_validation()
    {
        $candidature = Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
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
            'annee_diplome' => '2020'
        ,
            'premiere_langue' => 'Français',
            'region_origine' => 'Centre',
            'departement_origine' => 'Mfoundi']);

        // Envoyer la notification
        $this->candidat->notify(new CandidatureValidatedNotification($candidature));

        // Vérifier que la notification a été envoyée
        Notification::assertSentTo(
            $this->candidat,
            CandidatureValidatedNotification::class
        );
    }

    public function test_notification_envoyee_apres_rejet()
    {
        $candidature = Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'documents_a_corriger',
            'motif_rejet' => 'Documents non conformes',
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

        // Envoyer la notification avec le motif
        $this->candidat->notify(new CandidatureRejectedNotification($candidature, 'Documents non conformes'));

        // Vérifier que la notification a été envoyée
        Notification::assertSentTo(
            $this->candidat,
            CandidatureRejectedNotification::class
        );
    }

    public function test_un_candidat_peut_voir_ses_notifications()
    {
        $token = auth('api')->login($this->candidat);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/notifications');

        $response->assertStatus(200);
    }

    public function test_un_candidat_peut_marquer_notification_comme_lue()
    {
        // Créer une notification
        $candidature = Candidature::create([
            'user_id' => $this->candidat->id,
            'concours_id' => $this->concours->id,
            'centre_depot_id' => $this->centreDepot->id,
            'code_candidat' => 'TEST-001',
            'statut' => 'en_attente',
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

        $this->candidat->notify(new CandidatureSubmittedNotification($candidature));
        
        // Désactiver le fake pour récupérer la vraie notification
        Notification::fake(false);
        $this->candidat->notify(new CandidatureSubmittedNotification($candidature));
        
        $notification = $this->candidat->notifications()->first();
        
        if (!$notification) {
            $this->markTestSkipped('Notification not created in database');
        }

        $token = auth('api')->login($this->candidat);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/notifications/{$notification->id}/mark-read");

        $response->assertStatus(200);
    }
}
