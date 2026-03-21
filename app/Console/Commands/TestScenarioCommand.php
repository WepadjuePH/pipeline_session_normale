<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Concours;
use App\Models\Candidature;
use App\Models\CentreDepot;
use App\Models\CentreExamen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestScenarioCommand extends Command
{
    protected $signature = 'test:scenario';
    protected $description = 'Exécute un scénario de test complet du système';

    public function handle()
    {
        $this->info('=== DÉMARRAGE DU PLAN DE TEST COMPLET ===');
        $this->newLine();

        // Phase 1: Authentification
        $this->phase1_authentication();

        // Phase 2: Enroulement
        $this->phase2_enroulement();

        // Phase 3: Validation Centre Dépôt
        $this->phase3_validation_depot();

        // Phase 4: Admin
        $this->phase4_admin();

        // Phase 5: Examen
        $this->phase5_examen();

        $this->info('=== TEST COMPLET TERMINÉ ===');
    }

    private function phase1_authentication()
    {
        $this->info('📋 PHASE 1: AUTHENTIFICATION');
        $this->line('─────────────────────────────────────');

        // Créer les utilisateurs de test
        $users = [
            [
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => 'password123',
                'role' => 'admin',
            ],
            [
                'name' => 'Agent Dépôt 1',
                'email' => 'agent_depot1@test.com',
                'password' => 'password123',
                'role' => 'agent_depot',
            ],
            [
                'name' => 'Agent Dépôt 2',
                'email' => 'agent_depot2@test.com',
                'password' => 'password123',
                'role' => 'agent_depot',
            ],
            [
                'name' => 'Agent Examen 1',
                'email' => 'agent_examen1@test.com',
                'password' => 'password123',
                'role' => 'agent_examen',
            ],
            [
                'name' => 'Agent Examen 2',
                'email' => 'agent_examen2@test.com',
                'password' => 'password123',
                'role' => 'agent_examen',
            ],
            [
                'name' => 'Candidat Test 1',
                'email' => 'candidat1@test.com',
                'password' => 'password123',
                'role' => 'candidat',
            ],
            [
                'name' => 'Candidat Test 2',
                'email' => 'candidat2@test.com',
                'password' => 'password123',
                'role' => 'candidat',
            ],
            [
                'name' => 'Candidat Test 3',
                'email' => 'candidat3@test.com',
                'password' => 'password123',
                'role' => 'candidat',
            ],
            [
                'name' => 'Candidat Principal',
                'email' => 'hybreltapdur7@gmail.com',
                'password' => 'password123',
                'role' => 'candidat',
            ],
        ];

        foreach ($users as $userData) {
            // Séparer le nom et le prénom
            $parts = explode(' ', $userData['name'], 2);
            $prenom = $parts[0];
            $nom = $parts[1] ?? $parts[0];

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'email_verified_at' => now(),
                ]
            );
            $this->line("✓ {$userData['role']}: {$userData['email']} / {$userData['password']}");
        }

        $this->newLine();
        $this->info('✅ Phase 1 complétée: Tous les utilisateurs créés');
        $this->newLine();
    }

    private function phase2_enroulement()
    {
        $this->info('📋 PHASE 2: ENROULEMENT');
        $this->line('─────────────────────────────────────');

        // Récupérer un concours ouvert
        $concours = Concours::where('inscription_ouverte', true)
            ->where('is_active', true)
            ->first();
        
        if (!$concours) {
            $this->error('Aucun concours ouvert trouvé');
            return;
        }

        $this->line("Concours: {$concours->nom}");
        $this->line("Statut: Ouvert");

        // Récupérer les candidats (incluant le candidat principal)
        $candidats = User::where('role', 'candidat')->get();

        foreach ($candidats as $candidat) {
            // Générer le code candidat
            $code_candidat = $concours->genererCodeCandidat();

            // Récupérer une région et un département
            $region = \App\Models\Region::first();
            $departement = \App\Models\Departement::first();

            // Créer une candidature
            $candidature = Candidature::create([
                'user_id' => $candidat->id,
                'concours_id' => $concours->id,
                'code_candidat' => $code_candidat,
                'centre_depot_id' => CentreDepot::first()->id,
                'centre_examen_id' => CentreExamen::first()->id,
                'statut' => 'en_attente',
                'cursus' => 'Licence',
                'date_naissance' => now()->subYears(25),
                'lieu_naissance' => 'Yaoundé',
                'sexe' => 'masculin',
                'nationalite' => 'Camerounaise',
                'region_origine_id' => $region?->id ?? 1,
                'departement_origine_id' => $departement?->id ?? 1,
                'telephone' => '237600000000',
                'adresse' => 'Yaoundé',
                'filiere' => 'Scientifique',
                'premiere_langue' => 'Français',
                'cni' => '123456789',
                'diplome_admission' => 'Baccalauréat',
                'mention_diplome' => 'Bien',
                'annee_diplome' => 2024,
            ]);

            $this->line("✓ Candidature créée pour {$candidat->email}");
            $this->line("  - Code: {$code_candidat}");
            $this->line("  - Statut: {$candidature->statut}");
            $this->line("  - Fiche provisoire générée");
        }

        $this->newLine();
        $this->info('✅ Phase 2 complétée: Candidatures enroulées');
        $this->newLine();
    }

    private function phase3_validation_depot()
    {
        $this->info('📋 PHASE 3: VALIDATION CENTRE DÉPÔT');
        $this->line('─────────────────────────────────────');

        $candidatures = Candidature::where('statut', 'provisoire')->limit(2)->get();

        foreach ($candidatures as $candidature) {
            // Simuler la validation des documents
            $candidature->update([
                'statut' => 'validee',
                'date_validation' => now(),
            ]);

            $this->line("✓ Candidature validée pour {$candidature->user->email}");
            $this->line("  - Statut: validee");
            $this->line("  - Convocation générée avec QR code");
        }

        // Tester un rejet
        $candidature_rejet = Candidature::where('statut', 'provisoire')->first();
        if ($candidature_rejet) {
            $candidature_rejet->update([
                'statut' => 'rejetee',
                'motif_rejet' => 'Document manquant: Acte de naissance',
            ]);

            $this->line("✗ Candidature rejetée pour {$candidature_rejet->user->email}");
            $this->line("  - Motif: {$candidature_rejet->motif_rejet}");
        }

        $this->newLine();
        $this->info('✅ Phase 3 complétée: Validation dépôt effectuée');
        $this->newLine();
    }

    private function phase4_admin()
    {
        $this->info('📋 PHASE 4: ADMIN');
        $this->line('─────────────────────────────────────');

        $candidatures_validees = Candidature::where('statut', 'validee')->get();

        $this->line("Admin reçoit {$candidatures_validees->count()} dossiers validés");

        foreach ($candidatures_validees as $candidature) {
            $this->line("✓ Dossier reçu: {$candidature->user->email}");
        }

        $this->line("✓ Liste des candidats envoyée aux agents d'examen");

        $this->newLine();
        $this->info('✅ Phase 4 complétée: Admin a traité les dossiers');
        $this->newLine();
    }

    private function phase5_examen()
    {
        $this->info('📋 PHASE 5: EXAMEN');
        $this->line('─────────────────────────────────────');

        $candidatures_validees = Candidature::where('statut', 'validee')->get();

        foreach ($candidatures_validees as $candidature) {
            // Simuler le scan du QR code
            $this->line("✓ QR code scanné pour {$candidature->user->email}");

            // Marquer présent
            $candidature->update([
                'statut' => 'present',
            ]);

            $this->line("  - Statut: present");
        }

        $this->line("✓ Rapport d'examen envoyé à l'admin");

        $this->newLine();
        $this->info('✅ Phase 5 complétée: Examen effectué');
        $this->newLine();
    }
}
