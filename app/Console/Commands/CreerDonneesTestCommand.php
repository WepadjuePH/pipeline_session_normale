<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Concours;
use App\Models\Candidature;
use App\Models\CentreDepot;
use App\Models\CentreExamen;
use App\Models\SalleExamen;

class CreerDonneesTestCommand extends Command
{
    protected $signature = 'test:creer-donnees';
    protected $description = 'Créer des données de test pour visualiser les fiches';

    public function handle()
    {
        $this->info('🔄 Création des données de test...');
        
        // Vérifier si on a déjà des candidatures
        $existingCount = Candidature::count();
        if ($existingCount > 0) {
            $this->info("✅ {$existingCount} candidature(s) déjà présente(s)");
            
            $enAttente = Candidature::where('statut', 'en_attente')->count();
            $valideDepot = Candidature::where('statut', 'valide_depot')->count();
            
            $this->info("   - En attente: {$enAttente}");
            $this->info("   - Validées: {$valideDepot}");
            
            if ($enAttente > 0 && $valideDepot > 0) {
                $this->info('');
                $this->info('✅ Tout est prêt ! Ouvrez : http://localhost:8000/test-fiches');
                return 0;
            }
        }
        
        // Créer un utilisateur candidat si nécessaire
        $user = User::where('role', 'candidat')->first();
        if (!$user) {
            $this->info('📝 Création d\'un utilisateur candidat...');
            $user = User::create([
                'nom' => 'DUPONT',
                'prenom' => 'Jean',
                'email' => 'candidat.test@example.com',
                'password' => bcrypt('password'),
                'role' => 'candidat',
                'email_verified_at' => now(),
            ]);
            $this->info('✅ Utilisateur créé');
        }
        
        // Créer un concours si nécessaire
        $concours = Concours::first();
        if (!$concours) {
            $this->info('📝 Création d\'un concours...');
            $concours = Concours::create([
                'nom' => 'Concours ENS Mathématiques',
                'code' => 'ENS-MAT',
                'annee' => date('Y'),
                'type' => 'academique',
                'filiere' => 'Mathématiques',
                'cursus' => 'Licence 1',
                'date_ouverture' => now(),
                'date_cloture' => now()->addMonths(3),
                'date_examen' => now()->addMonths(4),
                'heure_examen' => '08:00',
                'frais_inscription' => 25000,
                'nombre_places' => 100,
                'inscription_ouverte' => true,
            ]);
            $this->info('✅ Concours créé');
        } else {
            // Mettre à jour le cursus si nécessaire
            if (!$concours->cursus) {
                $concours->update(['cursus' => 'Licence 1']);
                $this->info('✅ Cursus ajouté au concours existant');
            }
        }
        
        // Créer un centre de dépôt si nécessaire
        $centreDepot = CentreDepot::first();
        if (!$centreDepot) {
            $this->info('📝 Création d\'un centre de dépôt...');
            $centreDepot = CentreDepot::create([
                'nom' => 'Centre de Dépôt Yaoundé',
                'code' => 'CD-YDE',
                'ville' => 'Yaoundé',
                'adresse' => 'Yaoundé, Cameroun',
                'capacite' => 500,
            ]);
            $this->info('✅ Centre de dépôt créé');
        }
        
        // Créer un centre d'examen si nécessaire
        $centreExamen = CentreExamen::first();
        if (!$centreExamen) {
            $this->info('📝 Création d\'un centre d\'examen...');
            $centreExamen = CentreExamen::create([
                'nom' => 'ENS Yaoundé',
                'code' => 'ENS-YDE',
                'ville' => 'Yaoundé',
                'adresse' => 'Yaoundé, Cameroun',
                'capacite' => 1000,
            ]);
            $this->info('✅ Centre d\'examen créé');
        }
        
        // Créer une salle d'examen si nécessaire
        $salle = SalleExamen::first();
        if (!$salle) {
            $this->info('📝 Création d\'une salle d\'examen...');
            $salle = SalleExamen::create([
                'centre_examen_id' => $centreExamen->id,
                'nom' => 'Salle A',
                'code' => 'SALLE-A',
                'capacite' => 50,
            ]);
            $this->info('✅ Salle d\'examen créée');
        }
        
        // Créer candidature en attente
        $enAttente = Candidature::where('statut', 'en_attente')->count();
        if ($enAttente == 0) {
            $this->info('📝 Création d\'une candidature en attente...');
            
            // Créer une photo de test
            $photoPath = $this->creerPhotoTest('photo_test_1.jpg');
            
            Candidature::create([
                'user_id' => $user->id,
                'concours_id' => $concours->id,
                'code_candidat' => 'ENS-MAT-' . date('Y') . '-' . str_pad(1, 4, '0', STR_PAD_LEFT),
                'statut' => 'en_attente',
                'centre_depot_id' => $centreDepot->id,
                'centre_examen_id' => $centreExamen->id,
                'date_naissance' => '2000-01-01',
                'lieu_naissance' => 'Yaoundé',
                'sexe' => 'masculin',
                'nationalite' => 'Camerounaise',
                'region_origine_id' => 1,
                'departement_origine_id' => 1,
                'telephone' => '237699123456',
                'adresse' => 'Yaoundé, Cameroun',
                'premiere_langue' => 'Français',
                'cni' => '123456789',
                'filiere' => 'Mathématiques',
                'diplome_admission' => 'Baccalauréat',
                'annee_diplome' => '2020',
                'photo_candidat' => $photoPath,
            ]);
            $this->info('✅ Candidature en attente créée (avec photo)');
        }
        
        // Créer candidature validée
        $valideDepot = Candidature::where('statut', 'valide_depot')->count();
        if ($valideDepot == 0) {
            $this->info('📝 Création d\'une candidature validée...');
            
            // Créer une photo de test
            $photoPath = $this->creerPhotoTest('photo_test_2.jpg');
            
            Candidature::create([
                'user_id' => $user->id,
                'concours_id' => $concours->id,
                'code_candidat' => 'ENS-MAT-' . date('Y') . '-' . str_pad(2, 4, '0', STR_PAD_LEFT),
                'statut' => 'valide_depot',
                'centre_depot_id' => $centreDepot->id,
                'centre_examen_id' => $centreExamen->id,
                'salle_examen_id' => $salle->id,
                'numero_table' => '001',
                'date_naissance' => '2000-01-01',
                'lieu_naissance' => 'Yaoundé',
                'sexe' => 'masculin',
                'nationalite' => 'Camerounaise',
                'region_origine_id' => 1,
                'departement_origine_id' => 1,
                'telephone' => '237699123456',
                'adresse' => 'Yaoundé, Cameroun',
                'premiere_langue' => 'Français',
                'cni' => '987654321',
                'filiere' => 'Mathématiques',
                'diplome_admission' => 'Baccalauréat',
                'annee_diplome' => '2020',
                'photo_candidat' => $photoPath,
            ]);
            $this->info('✅ Candidature validée créée (avec photo)');
        }
        
        $this->info('');
        $this->info('✅ Données de test créées avec succès !');
        $this->info('');
        $this->info('🚀 Ouvrez maintenant : http://localhost:8000/test-fiches');
        
        return 0;
    }
    
    /**
     * Créer une photo de test (image simple)
     */
    private function creerPhotoTest($filename)
    {
        // Créer le dossier si nécessaire
        $photoDir = storage_path('app/public/documents/photo_candidat');
        if (!file_exists($photoDir)) {
            mkdir($photoDir, 0755, true);
        }
        
        // Créer une image simple de 400x500 (format photo 4x4)
        $image = imagecreatetruecolor(400, 500);
        
        // Couleur de fond (beige/crème pour ressembler à une vraie photo)
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
        
        // Couleur du texte (bleu foncé)
        $textColor = imagecolorallocate($image, 30, 60, 120);
        
        // Ajouter du texte en bas
        $text = "PHOTO TEST";
        imagestring($image, 5, 140, 450, $text, $textColor);
        
        // Sauvegarder l'image
        $photoPath = $photoDir . '/' . $filename;
        imagejpeg($image, $photoPath, 95);
        imagedestroy($image);
        
        // Retourner le chemin relatif pour la base de données
        return 'documents/photo_candidat/' . $filename;
    }
}
