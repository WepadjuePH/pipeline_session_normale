<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\CentreDepot;
use App\Models\SalleExamen;
use App\Services\CandidatureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class TestCompleteWorkflowCommand extends Command
{
    protected $signature = 'test:complete-workflow';
    protected $description = 'Test le workflow complet: inscription, validation, convocation';

    protected $candidatureService;

    public function __construct(CandidatureService $candidatureService)
    {
        parent::__construct();
        $this->candidatureService = $candidatureService;
    }

    public function handle()
    {
        $this->info('🚀 Démarrage du test complet du workflow...');
        $this->info('📧 Mode email: ' . config('mail.mailer'));
        $this->newLine();

        try {
            // ÉTAPE 1: Créer un candidat
            $this->info('ÉTAPE 1: Création du candidat');
            $uniqueEmail = 'test.workflow.' . uniqid() . '@example.com';
            $user = User::create([
                'nom' => 'Workflow',
                'prenom' => 'Test',
                'email' => $uniqueEmail,
                'password' => Hash::make('password'),
                'role' => 'candidat',
                'email_verified_at' => now(),
            ]);
            $this->line("✅ Candidat créé: {$user->nom} {$user->prenom} ({$user->email})");
            $this->newLine();

            // ÉTAPE 2: Créer une candidature
            $this->info('ÉTAPE 2: Soumission de la candidature');
            $concours = Concours::first();
            $centreDepot = CentreDepot::first();

            if (!$concours || !$centreDepot) {
                $this->error('❌ Erreur: Concours ou Centre de dépôt non trouvé');
                return 1;
            }

            // Créer des fichiers de test
            $testFile = $this->creerFichierTest();

            $candidatureData = [
                'concours_id' => $concours->id,
                'centre_depot_id' => $centreDepot->id,
                'date_naissance' => '1995-05-15',
                'lieu_naissance' => 'Yaoundé',
                'sexe' => 'masculin',
                'nationalite' => 'Camerounaise',
                'region_origine_id' => 1,
                'departement_origine_id' => 1,
                'telephone' => '237123456789',
                'adresse' => '123 Rue de Test, Yaoundé',
                'premiere_langue' => 'Français',
                'cni' => 'CM123456789',
                'filiere' => 'Informatique',
                'diplome_admission' => 'Licence',
                'mention_diplome' => 'bien',
                'annee_diplome' => 2023,
                'centre_examen_id' => 1,
                'nom_pere' => 'Père Test',
                'telephone_pere' => '237111111111',
                'nom_mere' => 'Mère Test',
                'telephone_mere' => '237222222222',
                'document_cni' => $testFile,
                'document_diplome' => $testFile,
                'document_acte_naissance' => $testFile,
                'document_recu_paiement' => $testFile,
                'photo_candidat' => $testFile,
            ];

            $candidature = $this->candidatureService->soumettre($candidatureData, $user);
            $this->line("✅ Candidature soumise: {$candidature->code_candidat}");
            $this->line("   Statut: {$candidature->statut}");
            $this->newLine();

            // ÉTAPE 3: Valider la candidature
            $this->info('ÉTAPE 3: Validation par agent dépôt');
            $salle = SalleExamen::first();

            if (!$salle) {
                $this->error('❌ Erreur: Salle d\'examen non trouvée');
                return 1;
            }

            $agent = User::where('role', 'agent_depot')->first();
            if (!$agent) {
                $agent = User::create([
                    'nom' => 'Agent',
                    'prenom' => 'Dépôt',
                    'email' => 'agent.depot@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'agent_depot',
                    'email_verified_at' => now(),
                ]);
            }

            $candidature = $this->candidatureService->valider(
                $candidature,
                ['salle_examen_id' => $salle->id],
                $agent
            );

            $this->line("✅ Candidature validée");
            $this->line("   Statut: {$candidature->statut}");
            $this->line("   Salle: {$candidature->salleExamen->nom}");
            $this->line("   Numéro de table: {$candidature->numero_table}");
            $this->newLine();

            // ÉTAPE 4: Vérifier les fichiers générés
            $this->info('ÉTAPE 4: Vérification des fichiers générés');
            $convocationPath = storage_path("app/public/fiches/convocation_{$candidature->code_candidat}.pdf");
            $qrCodePath = storage_path("app/public/qrcodes/{$candidature->code_candidat}.png");

            if (file_exists($convocationPath)) {
                $this->line("✅ Convocation PDF générée");
            } else {
                $this->error("❌ Convocation PDF non trouvée");
            }

            if (file_exists($qrCodePath)) {
                $this->line("✅ QR Code généré");
            } else {
                $this->error("❌ QR Code non trouvé");
            }
            $this->newLine();

            // ÉTAPE 5: Vérifier les notifications
            $this->info('ÉTAPE 5: Vérification des notifications');
            $notifications = $user->notifications()->latest()->take(2)->get();
            $this->line("✅ Notifications reçues: {$notifications->count()}");
            foreach ($notifications as $notif) {
                $this->line("   - {$notif->type}");
            }
            $this->newLine();

            // ÉTAPE 6: Tester le lien public de téléchargement
            $this->info('ÉTAPE 6: Test du lien public de téléchargement');
            $token = \App\Http\Controllers\PublicFicheController::genererToken($candidature);
            $publicUrl = url("/fiche/{$candidature->code_candidat}/{$token}");
            $this->line("✅ URL publique générée:");
            $this->line("   {$publicUrl}");
            $this->newLine();

            $this->info('✅ Test complet réussi!');
            $this->newLine();
            $this->line('Résumé:');
            $this->line("  - Candidat: {$user->nom} {$user->prenom}");
            $this->line("  - Code candidat: {$candidature->code_candidat}");
            $this->line("  - Concours: {$concours->nom}");
            $this->line("  - Centre dépôt: {$centreDepot->nom}");
            $this->line("  - Salle examen: {$candidature->salleExamen->nom}");
            $this->line("  - Statut: {$candidature->statut}");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    private function creerFichierTest()
    {
        $content = "Test file content - " . date('Y-m-d H:i:s');
        $filename = 'test_' . uniqid() . '.txt';
        $path = 'test_files/' . $filename;

        Storage::disk('public')->put($path, $content);

        // Retourner un objet UploadedFile simulé
        $fullPath = storage_path("app/public/{$path}");
        return new \Illuminate\Http\UploadedFile(
            $fullPath,
            $filename,
            'text/plain',
            null,
            true
        );
    }
}
