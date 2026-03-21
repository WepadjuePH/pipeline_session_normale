<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Candidature;
use App\Notifications\CandidatureSubmittedNotification;
use App\Notifications\CandidatureValidatedNotification;

class TestNotificationCommand extends Command
{
    protected $signature = 'test:notification';
    protected $description = 'Test sending notifications';

    public function handle()
    {
        $this->info('=== TEST NOTIFICATIONS ===');
        $this->newLine();

        // Get or create test user
        $user = User::where('email', 'hybreltapdur7@gmail.com')->first();
        
        if (!$user) {
            $this->error('Utilisateur non trouvé');
            return 1;
        }

        $this->info("Utilisateur trouvé: {$user->prenom} {$user->nom}");
        $this->newLine();

        // Get a candidature
        $candidature = $user->candidatures()->first();
        
        if (!$candidature) {
            $this->error('Aucune candidature trouvée');
            return 1;
        }

        $this->info("Candidature trouvée: {$candidature->code_candidat}");
        $this->newLine();

        // Test 1: Send CandidatureSubmittedNotification
        $this->info('Test 1: Envoi de CandidatureSubmittedNotification');
        try {
            $user->notify(new CandidatureSubmittedNotification($candidature));
            $this->info('✅ Notification envoyée');
        } catch (\Exception $e) {
            $this->error('❌ Erreur: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 2: Send CandidatureValidatedNotification
        $this->info('Test 2: Envoi de CandidatureValidatedNotification');
        try {
            $user->notify(new CandidatureValidatedNotification($candidature));
            $this->info('✅ Notification envoyée');
        } catch (\Exception $e) {
            $this->error('❌ Erreur: ' . $e->getMessage());
        }
        $this->newLine();

        $this->info('Vérifiez votre boîte Gmail pour les notifications');

        return 0;
    }
}
