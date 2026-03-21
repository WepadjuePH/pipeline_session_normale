<?php

/**
 * Script pour corriger automatiquement tous les tests
 * Ajoute les champs manquants (premiere_langue, region_origine, departement_origine)
 */

$testFiles = [
    'tests/Feature/AgentDepotTest.php',
    'tests/Feature/AgentExamenTest.php',
    'tests/Feature/NotificationTest.php',
    'tests/Unit/CandidatureModelTest.php',
];

$fieldsToAdd = [
    "'premiere_langue' => 'Français'",
    "'region_origine' => 'Centre'",
    "'departement_origine' => 'Mfoundi'",
];

foreach ($testFiles as $file) {
    if (!file_exists($file)) {
        echo "❌ Fichier non trouvé: $file\n";
        continue;
    }

    $content = file_get_contents($file);
    $modified = false;

    // Trouver toutes les créations de Candidature::create([
    preg_match_all('/Candidature::create\(\[(.*?)\]\)/s', $content, $matches, PREG_OFFSET_CAPTURE);

    if (empty($matches[0])) {
        echo "⚠️  Aucune création de candidature trouvée dans: $file\n";
        continue;
    }

    echo "📝 Traitement de: $file\n";
    echo "   Trouvé " . count($matches[0]) . " création(s) de candidature\n";

    // Parcourir en ordre inverse pour ne pas décaler les offsets
    for ($i = count($matches[0]) - 1; $i >= 0; $i--) {
        $fullMatch = $matches[0][$i][0];
        $arrayContent = $matches[1][$i][0];

        // Vérifier si les champs sont déjà présents
        $hasPremiereLangue = strpos($arrayContent, 'premiere_langue') !== false;
        $hasRegionOrigine = strpos($arrayContent, 'region_origine') !== false;
        $hasDepartementOrigine = strpos($arrayContent, 'departement_origine') !== false;

        if ($hasPremiereLangue && $hasRegionOrigine && $hasDepartementOrigine) {
            continue; // Déjà corrigé
        }

        // Trouver la dernière ligne avant le ]
        $lines = explode("\n", $arrayContent);
        $lastLineIndex = count($lines) - 1;

        // Ajouter les champs manquants
        $fieldsToInsert = [];
        if (!$hasPremiereLangue) $fieldsToInsert[] = $fieldsToAdd[0];
        if (!$hasRegionOrigine) $fieldsToInsert[] = $fieldsToAdd[1];
        if (!$hasDepartementOrigine) $fieldsToInsert[] = $fieldsToAdd[2];

        if (!empty($fieldsToInsert)) {
            // Ajouter les champs à la fin
            $insertion = ",\n            " . implode(",\n            ", $fieldsToInsert);
            $newArrayContent = $arrayContent . $insertion;
            $newFullMatch = str_replace($arrayContent, $newArrayContent, $fullMatch);

            $content = str_replace($fullMatch, $newFullMatch, $content);
            $modified = true;
            echo "   ✅ Ajouté " . count($fieldsToInsert) . " champ(s)\n";
        }
    }

    if ($modified) {
        file_put_contents($file, $content);
        echo "   💾 Fichier sauvegardé\n";
    } else {
        echo "   ℹ️  Aucune modification nécessaire\n";
    }

    echo "\n";
}

echo "✅ Terminé!\n";
