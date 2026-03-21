<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidature;

class AjouterPhotosTestCommand extends Command
{
    protected $signature = 'test:ajouter-photos';
    protected $description = 'Ajouter des photos de test aux candidatures existantes';

    public function handle()
    {
        $this->info('🔄 Ajout des photos aux candidatures...');
        
        $candidatures = Candidature::all();
        
        if ($candidatures->isEmpty()) {
            $this->info('❌ Aucune candidature trouvée');
            return 0;
        }
        
        $photoDir = storage_path('app/public/documents/photo_candidat');
        if (!file_exists($photoDir)) {
            mkdir($photoDir, 0755, true);
        }
        
        foreach ($candidatures as $candidature) {
            // Créer une image simple de 400x500
            $image = imagecreatetruecolor(400, 500);
            
            // Couleur de fond (beige/crème)
            $bgColor = imagecolorallocate($image, 245, 235, 220);
            imagefill($image, 0, 0, $bgColor);
            
            // Dessiner un cercle pour simuler un visage
            $faceColor = imagecolorallocate($image, 220, 180, 140);
            imagefilledellipse($image, 200, 180, 150, 180, $faceColor);
            
            // Dessiner les yeux
            $eyeColor = imagecolorallocate($image, 50, 50, 50);
            imagefilledellipse($image, 170, 160, 20, 25, $eyeColor);
            imagefilledellipse($image, 230, 160, 20, 25, $eyeColor);
            
            // Dessiner la bouche
            imagearc($image, 200, 200, 80, 60, 0, 180, $eyeColor);
            
            // Couleur du texte
            $textColor = imagecolorallocate($image, 30, 60, 120);
            
            // Ajouter du texte
            imagestring($image, 5, 140, 450, 'PHOTO TEST', $textColor);
            
            // Nom du fichier
            $filename = 'photo_' . $candidature->id . '.jpg';
            $photoPath = $photoDir . '/' . $filename;
            
            // Sauvegarder
            imagejpeg($image, $photoPath, 95);
            imagedestroy($image);
            
            // Mettre à jour la candidature
            $candidature->update([
                'photo_candidat' => 'documents/photo_candidat/' . $filename
            ]);
            
            $this->info("✅ Photo ajoutée pour candidature {$candidature->code_candidat}");
        }
        
        $this->info('');
        $this->info('✅ Toutes les photos ont été ajoutées !');
        $this->info('🚀 Rafraîchissez : http://localhost:8000/test-fiches');
        
        return 0;
    }
}
