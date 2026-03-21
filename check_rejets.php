<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Candidatures Rejetées ===\n\n";

$candidatures = App\Models\Candidature::where('statut', 'documents_a_corriger')
    ->with('user')
    ->get();

if ($candidatures->isEmpty()) {
    echo "Aucune candidature rejetée trouvée.\n";
} else {
    foreach ($candidatures as $c) {
        echo "ID: {$c->id}\n";
        echo "Code: {$c->code_candidat}\n";
        echo "Candidat: {$c->user->nom} {$c->user->prenom}\n";
        echo "Locked: " . ($c->locked ? 'OUI ❌' : 'NON ✅') . "\n";
        echo "Peut être modifiée: " . ($c->peutEtreModifiee() ? 'OUI ✅' : 'NON ❌') . "\n";
        echo "Motif rejet: " . ($c->motif_rejet ?? 'N/A') . "\n";
        echo "Documents complets: " . ($c->documentsComplets() ? 'OUI ✅' : 'NON ❌') . "\n";
        echo str_repeat('-', 60) . "\n\n";
    }
}

echo "\n=== Déverrouillage des candidatures rejetées ===\n\n";

$locked = App\Models\Candidature::where('statut', 'documents_a_corriger')
    ->where('locked', true)
    ->get();

if ($locked->isEmpty()) {
    echo "Aucune candidature rejetée verrouillée. Tout est OK! ✅\n";
} else {
    echo "Déverrouillage de {$locked->count()} candidature(s)...\n";
    foreach ($locked as $c) {
        $c->deverrouiller();
        echo "✅ Candidature {$c->code_candidat} déverrouillée\n";
    }
}

echo "\n=== Vérification finale ===\n\n";

$candidatures = App\Models\Candidature::where('statut', 'documents_a_corriger')->get();

foreach ($candidatures as $c) {
    echo "Code: {$c->code_candidat} | ";
    echo "Locked: " . ($c->locked ? 'OUI ❌' : 'NON ✅') . " | ";
    echo "Peut modifier: " . ($c->peutEtreModifiee() ? 'OUI ✅' : 'NON ❌') . "\n";
}

echo "\n✅ Terminé!\n";
