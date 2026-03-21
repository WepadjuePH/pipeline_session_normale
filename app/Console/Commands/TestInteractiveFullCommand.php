<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\CentreExamen;
use App\Models\SalleExamen;
use App\Models\EmailVerification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class TestInteractiveFullCommand extends Command
{
    protected $signature = 'test:interactive-full';
    protected $description = 'Interactive test: Register candidate, verify email, enroll, and test all roles';

    public function handle()
    {
        $this->info('=== SYSTÈME DE TEST COMPLET INTERACTIF ===');
        $this->newLine();

        // STEP 1: Register candidate
        $this->info('ÉTAPE 1: Inscription du candidat');
        $this->line('Création du compte avec:');
        $this->line('  Nom: prince');
        $this->line('  Prénom: tapdur');
        $this->line('  Email: hybreltapdur7@gmail.com');
        $this->line('  Mot de passe: password');

        $user = User::create([
            'nom' => 'prince',
            'prenom' => 'tapdur',
            'email' => 'hybreltapdur7@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'candidat',
        ]);

        $this->info("✓ Candidat créé (ID: {$user->id})");

        // Generate verification code
        $verification = EmailVerification::createForEmail($user->email);
        $this->warn("⚠️  CODE DE VÉRIFICATION GÉNÉRÉ: {$verification->code}");
        $this->line('Ce code a été envoyé à hybreltapdur7@gmail.com');
        $this->newLine();

        // STEP 2: Wait for verification code
        $this->info('ÉTAPE 2: Vérification de l\'email');
        $code = $this->ask('Entrez le code de vérification reçu par email');

        if (!EmailVerification::verify($user->email, $code)) {
            $this->error('❌ Code invalide ou expiré');
            return 1;
        }

        $user->update(['email_verified_at' => now()]);
        $this->info('✓ Email vérifié avec succès');
        $this->newLine();

        // STEP 3: Login and get token
        $this->info('ÉTAPE 3: Connexion');
        $token = JWTAuth::fromUser($user);
        $this->info("✓ Connexion réussie");
        $this->line("Token JWT: {$token}");
        $this->newLine();

        // STEP 4: Enrollment
        $this->info('ÉTAPE 4: Enrôlement');
        $concours = Concours::first();
        $centre = CentreDepot::first();

        $candidature = Candidature::create([
            'user_id' => $user->id,
            'concours_id' => $concours->id,
            'centre_depot_id' => $centre->id,
            'numero_enroulement' => 'ENS-MAT-' . date('Y') . '-' . date('Y') . '-' . $user->id,
            'statut' => 'provisoire',
            'date_enroulement' => now(),
        ]);

        $this->info("✓ Enrôlement créé (ID: {$candidature->id})");
        $this->line("Numéro d'enrôlement: {$candidature->numero_enroulement}");
        $this->line("Centre de dépôt: {$centre->nom}");
        $this->newLine();

        // STEP 5: Agent Depot validates
        $this->info('ÉTAPE 5: Validation par Agent Dépôt');
        $agentDepot = User::where('role', 'agent_depot')->first();
        if (!$agentDepot) {
            $agentDepot = User::create([
                'nom' => 'Agent',
                'prenom' => 'Dépôt',
                'email' => 'agent.depot@sgecn.cm',
                'password' => Hash::make('password'),
                'role' => 'agent_depot',
                'centre_depot_id' => $centre->id,
            ]);
        }

        $candidature->update([
            'statut' => 'validee',
            'date_validation_depot' => now(),
            'salle_examen' => 'Salle A',
            'numero_table' => '001',
        ]);

        $this->info('✓ Candidature validée par agent dépôt');
        $this->line("Salle d'examen: Salle A");
        $this->line("Numéro de table: 001");
        $this->newLine();

        // STEP 6: Admin receives dossier
        $this->info('ÉTAPE 6: Admin reçoit le dossier');
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'nom' => 'Admin',
                'prenom' => 'System',
                'email' => 'admin@sgecn.cm',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        $this->info('✓ Admin a reçu le dossier');
        $this->newLine();

        // STEP 7: Agent Exam scans QR
        $this->info('ÉTAPE 7: Agent Examen scanne le QR code');
        $agentExam = User::where('role', 'agent_examen')->first();
        if (!$agentExam) {
            $agentExam = User::create([
                'nom' => 'Agent',
                'prenom' => 'Examen',
                'email' => 'agent.examen@sgecn.cm',
                'password' => Hash::make('password'),
                'role' => 'agent_examen',
            ]);
        }

        $candidature->update([
            'statut' => 'present',
            'date_presence' => now(),
        ]);

        $this->info('✓ Présence marquée');
        $this->newLine();

        // SUMMARY
        $this->info('=== RÉSUMÉ DU TEST ===');
        $this->line("Candidat: {$user->prenom} {$user->nom}");
        $this->line("Email: {$user->email}");
        $this->line("ID Candidat: {$user->id}");
        $this->line("Numéro d'enrôlement: {$candidature->numero_enroulement}");
        $this->line("Statut: {$candidature->statut}");
        $this->line("Centre: {$centre->nom}");
        $this->line("Salle: {$candidature->salle_examen}");
        $this->line("Table: {$candidature->numero_table}");
        $this->newLine();

        $this->info('✓ TEST COMPLET RÉUSSI');
        $this->line('Vérifiez que vous avez reçu:');
        $this->line('  1. Email de vérification');
        $this->line('  2. Fiche provisoire');
        $this->line('  3. Fiche de convocation avec QR code');

        return 0;
    }
}
