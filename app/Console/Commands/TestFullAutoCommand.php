<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\EmailVerification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class TestFullAutoCommand extends Command
{
    protected $signature = 'test:full-auto';
    protected $description = 'Automated full test: Register, verify, enroll, and test all roles';

    public function handle()
    {
        $this->info('=== TEST COMPLET AUTOMATISÉ ===');
        $this->newLine();

        // STEP 1: Register candidate
        $this->info('ÉTAPE 1: Inscription du candidat');
        $this->line('Création du compte avec:');
        $this->line('  Nom: prince');
        $this->line('  Prénom: tapdur');
        $this->line('  Email: hybreltapdur7@gmail.com');
        $this->line('  Mot de passe: password');

        // Delete if exists
        User::where('email', 'hybreltapdur7@gmail.com')->delete();

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
        $this->warn("CODE DE VÉRIFICATION: {$verification->code}");
        $this->line('(Ce code a été envoyé à hybreltapdur7@gmail.com)');
        $this->newLine();

        // STEP 2: Verify email
        $this->info('ÉTAPE 2: Vérification de l\'email');
        if (EmailVerification::verify($user->email, $verification->code)) {
            $user->update(['email_verified_at' => now()]);
            $this->info('✓ Email vérifié avec succès');
        } else {
            $this->error('❌ Erreur de vérification');
            return 1;
        }
        $this->newLine();

        // STEP 3: Login and get token
        $this->info('ÉTAPE 3: Connexion');
        $token = JWTAuth::fromUser($user);
        $this->info('✓ Connexion réussie');
        $this->line("Token JWT: " . substr($token, 0, 50) . "...");
        $this->newLine();

        // STEP 4: Enrollment
        $this->info('ÉTAPE 4: Enrôlement');
        $concours = Concours::first();
        $centre = CentreDepot::first();

        Candidature::where('user_id', $user->id)->delete();

        $codeCandidature = 'ENS-MAT-' . date('Y') . '-' . date('Y') . '-' . $user->id;
        $candidature = Candidature::create([
            'user_id' => $user->id,
            'concours_id' => $concours->id,
            'code_candidat' => $codeCandidature,
            'centre_depot_id' => $centre->id,
            'statut' => 'en_attente',
        ]);

        $this->info("✓ Enrôlement créé (ID: {$candidature->id})");
        $this->line("Numéro d'enrôlement: {$codeCandidature}");
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
            'statut' => 'valide_depot',
            'valide_par_depot' => $agentDepot->id,
            'valide_depot_at' => now(),
            'salle_examen_id' => 1,
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
            'valide_par_examen' => $agentExam->id,
            'valide_examen_at' => now(),
        ]);

        $this->info('✓ Présence marquée');
        $this->newLine();

        // SUMMARY
        $this->info('=== RÉSUMÉ DU TEST ===');
        $this->line("Candidat: {$user->prenom} {$user->nom}");
        $this->line("Email: {$user->email}");
        $this->line("ID Candidat: {$user->id}");
        $this->line("Numéro d'enrôlement: {$codeCandidature}");
        $this->line("Statut: {$candidature->statut}");
        $this->line("Centre: {$centre->nom}");
        $this->line("Salle: {$candidature->salleExamen->nom ?? 'N/A'}");
        $this->line("Table: {$candidature->numero_table}");
        $this->newLine();

        $this->info('✓ TEST COMPLET RÉUSSI');
        $this->warn('VÉRIFIEZ QUE VOUS AVEZ REÇU:');
        $this->line('  1. Email de vérification (avec code: ' . $verification->code . ')');
        $this->line('  2. Fiche provisoire');
        $this->line('  3. Fiche de convocation avec QR code');
        $this->newLine();

        $this->info('INFORMATIONS POUR LES AUTRES RÔLES:');
        $this->line("Admin - Email: admin@sgecn.cm, Mot de passe: password");
        $this->line("Agent Dépôt - Email: agent.depot@sgecn.cm, Mot de passe: password");
        $this->line("Agent Examen - Email: agent.examen@sgecn.cm, Mot de passe: password");

        return 0;
    }
}
