<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Swift_TransportException;

class TestSmtpCommand extends Command
{
    protected $signature = 'test:smtp';
    protected $description = 'Test SMTP connection and email sending';

    public function handle()
    {
        $this->info('=== TEST CONNEXION SMTP ===');
        $this->newLine();

        // Afficher la configuration
        $this->info('Configuration actuelle:');
        $this->line('  MAIL_MAILER: ' . config('mail.default'));
        $this->line('  MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('  MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('  MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->line('  MAIL_FROM_ADDRESS: ' . config('mail.from.address'));
        $this->line('  MAIL_FROM_NAME: ' . config('mail.from.name'));
        $this->newLine();

        // Tester l'envoi
        $this->info('Tentative d\'envoi d\'un email de test...');
        
        try {
            Mail::raw('Ceci est un email de test du système SGECN', function ($message) {
                $message->to('hybreltapdur7@gmail.com')
                    ->subject('Test SMTP - SGECN');
            });
            
            $this->info('✅ Email envoyé avec succès!');
            $this->line('Vérifiez votre boîte Gmail dans quelques secondes.');
            
        } catch (Swift_TransportException $e) {
            $this->error('❌ Erreur SMTP:');
            $this->error($e->getMessage());
            $this->newLine();
            
            $this->warn('Causes possibles:');
            $this->line('1. Mot de passe Gmail expiré ou invalide');
            $this->line('2. 2FA non activé sur le compte Gmail');
            $this->line('3. Authentification à deux facteurs requise');
            $this->line('4. Compte Gmail bloqué');
            $this->newLine();
            
            $this->info('Solutions:');
            $this->line('1. Générez un nouveau mot de passe d\'application:');
            $this->line('   https://myaccount.google.com/apppasswords');
            $this->line('2. Sélectionnez "Mail" et "Windows"');
            $this->line('3. Copiez le mot de passe généré');
            $this->line('4. Mettez à jour .env avec MAIL_PASSWORD=<nouveau_mot_de_passe>');
            $this->line('5. Exécutez: php artisan config:clear');
            
            return 1;
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur générale:');
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
