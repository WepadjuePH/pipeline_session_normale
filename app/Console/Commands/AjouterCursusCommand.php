<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Concours;
use App\Models\Candidature;

class AjouterCursusCommand extends Command
{
    protected $signature = 'test:ajouter-cursus';
    protected $description = 'Ajouter le cursus aux concours et candidatures';

    public function handle()
    {
        $this->info('🔄 Ajout du cursus...');
        
        // Ajouter cursus aux concours
        $concours = Concours::all();
        foreach ($concours as $c) {
            if (!$c->cursus) {
                $c->update(['cursus' => 'Licence']);
                $this->info("✅ Cursus 'Licence' ajouté au concours: {$c->nom}");
            }
        }
        
        // Ajouter cursus aux candidatures
        $candidatures = Candidature::all();
        foreach ($candidatures as $cand) {
            if (!$cand->cursus) {
                $cand->update(['cursus' => 'Licence']);
                $this->info("✅ Cursus 'Licence' ajouté à la candidature: {$cand->code_candidat}");
            }
        }
        
        $this->info('');
        $this->info('✅ Cursus ajouté avec succès !');
        $this->info('🚀 Rafraîchissez : http://localhost:8000/test-fiches');
        
        return 0;
    }
}
