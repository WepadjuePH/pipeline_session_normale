<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmailVerification;

class GenerateVerificationCodeCommand extends Command
{
    protected $signature = 'verification:generate {email}';
    protected $description = 'Générer un code de vérification pour un email';

    public function handle()
    {
        $email = $this->argument('email');

        // Vérifier que l'utilisateur existe
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("Utilisateur avec l'email {$email} non trouvé");
            return;
        }

        // Générer un code de vérification
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Créer ou mettre à jour le code de vérification
        EmailVerification::updateOrCreate(
            ['email' => $email],
            ['code' => $code]
        );

        $this->info("✅ Code de vérification généré pour {$email}");
        $this->line("Code: {$code}");
        $this->line("");
        $this->line("Utilise ce code pour vérifier ton email:");
        $this->line("POST /api/auth/verify-email");
        $this->line("Body:");
        $this->line("{");
        $this->line("  \"email\": \"{$email}\",");
        $this->line("  \"code\": \"{$code}\"");
        $this->line("}");
    }
}
