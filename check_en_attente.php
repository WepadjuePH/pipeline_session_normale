<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Candidatures En Attente ===\n\n";

$candidatures = App\Models\Candidature::where('statut', 'en_attente')
    ->with('user')
    ->get();

if ($candidatures->isEmpty()) {
    echo "Aucune candidature en attente trouvée.\n";
} else {
    foreach ($candidatures as $c) {
        echo "ID: {$c->id}\n";
        echo "Code: {$c->code_candidat}\n";
        echo "Candidat: {$c->user->nom} {$c->user->prenom}\n";
        echo "Email: {$c->user->email}\n";
        echo "Statut: {$c->statut}\n";
        echo "Locked: " . ($c->locked ? 'OUI ❌' : 'NON ✅') . "\n";
        echo "Peut être modifiée: " . ($c->peutEtreModifiee() ? 'OUI ✅' : 'NON ❌') . "\n";
        echo "Documents complets: " . ($c->documentsComplets() ? 'OUI ✅' : 'NON ❌') . "\n";
        echo str_repeat('-', 60) . "\n\n";
    }
}

echo "\n=== Déverrouillage des candidatures en attente ===\n\n";

$locked = App\Models\Candidature::where('statut', 'en_attente')
    ->where('locked', true)
    ->get();

if ($locked->isEmpty()) {
    echo "Aucune candidature en attente verrouillée. Tout est OK! ✅\n";
} else {
    echo "⚠️ ATTENTION: {$locked->count()} candidature(s) en attente sont verrouillées!\n";
    echo "Déverrouillage en cours...\n\n";
    foreach ($locked as $c) {
        $c->deverrouiller();
        echo "✅ Candidature {$c->code_candidat} déverrouillée\n";
    }
}

echo "\n=== Vérification finale ===\n\n";

$candidatures = App\Models\Candidature::where('statut', 'en_attente')->get();

foreach ($candidatures as $c) {
    echo "Code: {$c->code_candidat} | ";
    echo "Locked: " . ($c->locked ? 'OUI ❌' : 'NON ✅') . " | ";
    echo "Peut modifier: " . ($c->peutEtreModifiee() ? 'OUI ✅' : 'NON ❌') . "\n";
}

echo "\n✅ Terminé!\n";
