<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicFicheController;
use App\Models\Candidature;
use App\Helpers\QrCodeHelper;

Route::get('/', function () {
    return view('welcome');
});

// Routes publiques pour télécharger les fiches
Route::get('/fiche/{code_candidat}/{token}', [PublicFicheController::class, 'telechargerFiche'])->name('fiche.public');

// ============================================
// ROUTES DE TEST - À RETIRER EN PRODUCTION
// ============================================

// Test Fiche Provisoire (verte)
Route::get('/test-fiche-provisoire', function () {
    $candidature = Candidature::where('statut', 'en_attente')->first();
    
    if (!$candidature) {
        return "<h1>❌ Aucune candidature en attente trouvée</h1>
                <p>Créez d'abord une candidature avec statut 'en_attente' dans la base de données.</p>
                <p>Ou utilisez : <code>php artisan db:seed</code></p>";
    }
    
    $qrCodePath = QrCodeHelper::generate($candidature);
    
    return view('fiches.provisoire', [
        'candidature' => $candidature->load(['user', 'concours', 'centreDepot', 'centreExamen', 'salleExamen']),
        'qr_code_path' => $qrCodePath,
    ]);
})->name('test.fiche.provisoire');

// Test Convocation (rouge)
Route::get('/test-convocation', function () {
    $candidature = Candidature::where('statut', 'valide_depot')->first();
    
    if (!$candidature) {
        // Essayer de créer une candidature de test
        $candidature = Candidature::where('statut', 'en_attente')->first();
        
        if ($candidature) {
            // Mettre à jour pour test
            $candidature->update([
                'statut' => 'valide_depot',
                'salle_examen_id' => 1,
                'numero_table' => '001'
            ]);
            
            return redirect('/test-convocation');
        }
        
        return "<h1>❌ Aucune candidature validée trouvée</h1>
                <p>Créez d'abord une candidature avec statut 'valide_depot' dans la base de données.</p>
                <p>Ou modifiez une candidature existante.</p>";
    }
    
    $qrCodePath = QrCodeHelper::generate($candidature);
    
    return view('fiches.convocation', [
        'candidature' => $candidature->load(['user', 'concours', 'centreDepot', 'centreExamen', 'salleExamen']),
        'qr_code_path' => $qrCodePath,
    ]);
})->name('test.convocation');

// Page d'index pour les tests
Route::get('/test-fiches', function () {
    $enAttente = Candidature::where('statut', 'en_attente')->count();
    $valideDepot = Candidature::where('statut', 'valide_depot')->count();
    $total = Candidature::count();
    
    return view('test-fiches', [
        'enAttente' => $enAttente,
        'valideDepot' => $valideDepot,
        'total' => $total,
    ]);
})->name('test.fiches');

// Page pour voir TOUS les templates de fiches
Route::get('/test-all-fiches', function () {
    $enAttente = Candidature::where('statut', 'en_attente')->count();
    $valideDepot = Candidature::where('statut', 'valide_depot')->count();
    $total = Candidature::count();
    
    return view('test-all-fiches', [
        'enAttente' => $enAttente,
        'valideDepot' => $valideDepot,
        'total' => $total,
    ]);
})->name('test.all.fiches');

// Routes pour rendre chaque template individuellement
Route::get('/test-render/{template}', function ($template) {
    // Récupérer une candidature de test
    $candidature = Candidature::with(['user', 'concours', 'centreDepot', 'centreExamen', 'salleExamen'])->first();
    
    if (!$candidature) {
        return "<h1>❌ Aucune candidature trouvée</h1><p>Créez d'abord des candidatures de test.</p>";
    }
    
    $qrCodePath = QrCodeHelper::generate($candidature);
    
    // Mapper les noms de templates
    $templateMap = [
        'provisoire' => 'fiches.provisoire',
        'convocation' => 'fiches.convocation',
    ];
    
    if (!isset($templateMap[$template])) {
        return "<h1>❌ Template non trouvé</h1>";
    }
    
    return view($templateMap[$template], [
        'candidature' => $candidature,
        'qr_code_path' => $qrCodePath,
    ]);
})->name('test.render');

// Page pour voir le rendu final des 2 fiches
Route::get('/test-final-fiches', function () {
    return view('test-final-fiches');
})->name('test.final.fiches');

// Routes pour rendre les 2 templates finaux
Route::get('/test-render-final/{template}', function ($template) {
    // Récupérer une candidature selon le template
    if ($template === 'provisoire') {
        // Pour fiche provisoire : candidature en attente
        $candidature = Candidature::where('statut', 'en_attente')
            ->with(['user', 'concours', 'centreDepot', 'centreExamen', 'salleExamen'])
            ->first();
    } else {
        // Pour convocation : candidature validée
        $candidature = Candidature::where('statut', 'valide_depot')
            ->with(['user', 'concours', 'centreDepot', 'centreExamen', 'salleExamen'])
            ->first();
    }
    
    if (!$candidature) {
        return "<h1>❌ Aucune candidature trouvée</h1><p>Créez d'abord des candidatures de test avec le bon statut.</p>";
    }
    
    $qrCodePath = QrCodeHelper::generate($candidature);
    
    // Mapper les templates finaux
    $templateMap = [
        'provisoire' => 'fiches.provisoire',
        'convocation' => 'fiches.convocation',
    ];
    
    if (!isset($templateMap[$template])) {
        return "<h1>❌ Template non trouvé</h1>";
    }
    
    return view($templateMap[$template], [
        'candidature' => $candidature,
        'qr_code_path' => $qrCodePath,
    ]);
})->name('test.render.final');