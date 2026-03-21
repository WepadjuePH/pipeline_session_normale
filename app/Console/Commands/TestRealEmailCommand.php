<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\Region;
use App\Models\Departement;
use App\Services\CandidatureService;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class TestRealEmailCommand extends Command
{
    protected $signature = 'test:real-email';
    protected $description = 'Test real email sending with actual candidature submission';

    public function handle()
    {
        $this->info('=== TEST RÉEL D\'ENVOI D\'EMAILS ===');
        $this->newLine();

        // Delete existing user and candidatures
        Candidature::whereHas('user', function ($q) {
            $q->where('email', 'hybreltapdur7@gmail.com');
        })->delete();
        User::where('email', 'hybreltapdur7@gmail.com')->forceDelete();

        // Create user
        $this->info('ÉTAPE 1: Création du candidat');
        $user = User::create([
            'nom' => 'prince',
            'prenom' => 'tapdur',
            'email' => 'hybreltapdur7@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'candidat',
            'email_verified_at' => now(),
        ]);
        $this->info("✓ Candidat créé (ID: {$user->id})");
        $this->newLine();

        // Get concours and centre
        $concours = Concours::where('inscription_ouverte', true)->first();
        $centre = CentreDepot::first();
        $region = Region::first();
        $departement = Departement::first();

        if (!$concours || !$centre || !$region || !$departement) {
            $this->error('Données manquantes en base');
            return 1;
        }

        // Prepare candidature data
        $this->info('ÉTAPE 2: Soumission de candidature');
        
        // Create dummy files for documents
        $dummyFile = new \Illuminate\Http\UploadedFile(
            storage_path('app/public/dummy.pdf'),
            'dummy.pdf',
            'application/pdf',
            null,
            true
        );

        $data = [
            'concours_id' => $concours->id,
            'centre_depot_id' => $centre->id,
            'date_naissance' => now()->subYears(25)->format('Y-m-d'),
            'lieu_naissance' => 'Yaoundé',
            'sexe' => 'masculin',
            'nationalite' => 'Camerounaise',
            'region_origine_id' => $region->id,
            'departement_origine_id' => $departement->id,
            'telephone' => '237600000000',
            'adresse' => 'Yaoundé',
            'premiere_langue' => 'Français',
            'cni' => '123456789',
            'filiere' => 'Scientifique',
            'cursus' => 'Licence',
            'diplome_admission' => 'Baccalauréat',
            'mention_diplome' => 'bien',
            'annee_diplome' => 2024,
            'document_cni' => $dummyFile,
            'document_diplome' => $dummyFile,
            'document_acte_naissance' => $dummyFile,
            'document_recu_paiement' => $dummyFile,
            'photo_candidat' => $dummyFile,
        ];

        try {
            $service = new CandidatureService();
            $candidature = $service->soumettre($data, $user);
            $this->info("✓ Candidature créée (Code: {$candidature->code_candidat})");
            $this->info("✓ Email de candidature soumise ENVOYÉ");
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
        $this->newLine();

        // Validate candidature
        $this->info('ÉTAPE 3: Validation par agent dépôt');
        try {
            $agent = User::where('role', 'agent_depot')->first();
            $candidature = $service->valider($candidature, [
                'salle_examen_id' => 1,
            ], $agent);
            $this->info("✓ Candidature validée");
            $this->info("✓ Email de convocation ENVOYÉ");
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
        $this->newLine();

        $this->info('=== RÉSUMÉ ===');
        $this->line("Candidat: {$user->prenom} {$user->nom}");
        $this->line("Email: {$user->email}");
        $this->line("Code candidat: {$candidature->code_candidat}");
        $this->newLine();

        $this->warn('EMAILS ENVOYÉS:');
        $this->line('1. ✅ Email de candidature soumise');
        $this->line('2. ✅ Email de convocation avec QR code');
        $this->newLine();

        $this->info('Vérifiez votre boîte Gmail pour les emails');

        return 0;
    }
}
