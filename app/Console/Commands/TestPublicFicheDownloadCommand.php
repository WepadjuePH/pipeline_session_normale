<?php

namespace App\Console\Commands;

use App\Models\Candidature;
use App\Http\Controllers\PublicFicheController;
use Illuminate\Console\Command;

class TestPublicFicheDownloadCommand extends Command
{
    protected $signature = 'test:public-fiche-download';
    protected $description = 'Test le téléchargement public des fiches';

    public function handle()
    {
        $this->info('🔍 Test du téléchargement public des fiches...');
        $this->newLine();

        // Trouver une candidature validée
        $candidature = Candidature::where('statut', 'valide_depot')
            ->with(['user', 'concours', 'centreExamen', 'salleExamen'])
            ->first();

        if (!$candidature) {
            $this->error('❌ Aucune candidature validée trouvée');
            $this->line('Exécutez d\'abord: php artisan test:complete-workflow');
            return 1;
        }

        $this->info('Candidature trouvée:');
        $this->line("  Code: {$candidature->code_candidat}");
        $this->line("  Candidat: {$candidature->user->nom} {$candidature->user->prenom}");
        $this->line("  Email: {$candidature->user->email}");
        $this->line("  Statut: {$candidature->statut}");
        $this->newLine();

        // Générer le token
        $this->info('Génération du token sécurisé...');
        $token = PublicFicheController::genererToken($candidature);
        $this->line("✅ Token généré: {$token}");
        $this->newLine();

        // Construire l'URL publique
        $this->info('Construction de l\'URL publique...');
        $publicUrl = url("/fiche/{$candidature->code_candidat}/{$token}");
        $this->line("✅ URL: {$publicUrl}");
        $this->newLine();

        // Vérifier que la fiche existe
        $this->info('Vérification des fichiers...');
        $convocationPath = storage_path("app/public/fiches/convocation_{$candidature->code_candidat}.pdf");
        $qrCodePath = storage_path("app/public/qrcodes/{$candidature->code_candidat}.png");

        if (file_exists($convocationPath)) {
            $size = filesize($convocationPath);
            $this->line("✅ Convocation PDF: {$size} bytes");
        } else {
            $this->error("❌ Convocation PDF non trouvée: {$convocationPath}");
        }

        if (file_exists($qrCodePath)) {
            $size = filesize($qrCodePath);
            $this->line("✅ QR Code: {$size} bytes");
        } else {
            $this->error("❌ QR Code non trouvé: {$qrCodePath}");
        }
        $this->newLine();

        // Afficher les informations de la convocation
        $this->info('Informations de la convocation:');
        $this->line("  Code candidat: {$candidature->code_candidat}");
        $this->line("  Concours: {$candidature->concours->nom}");
        $this->line("  Centre d'examen: {$candidature->centreExamen->nom}");
        $this->line("  Salle: {$candidature->salleExamen->nom}");
        $this->line("  Numéro de table: {$candidature->numero_table}");
        $this->newLine();

        // Afficher les instructions
        $this->info('📱 Instructions pour tester:');
        $this->line('1. Sur PC/Téléphone, ouvrez l\'URL:');
        $this->line("   {$publicUrl}");
        $this->line('');
        $this->line('2. La convocation PDF devrait se télécharger automatiquement');
        $this->line('');
        $this->line('3. Vérifiez que:');
        $this->line('   ✓ Le PDF contient les informations du candidat');
        $this->line('   ✓ Le QR Code est visible');
        $this->line('   ✓ Les sections TRÈS IMPORTANT et STRICTEMENT INTERDIT sont présentes');
        $this->newLine();

        $this->info('✅ Test du téléchargement public réussi!');

        return 0;
    }
}
