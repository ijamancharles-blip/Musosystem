<?php
// Créer package.json
$package = [
    "name" => "muso",
    "version" => "1.0.0",
    "description" => "Un projet PHP dynamique",
    "author" => "Samuel ALectine"
];

file_put_contents("package.json", json_encode($package, JSON_PRETTY_PRINT));
echo "✅ package.json créé\n";

// Créer README.md
$readme = "# Mon Projet\n\nCeci est un README généré automatiquement.";
file_put_contents("README.md", $readme);
echo "✅ README.md créé\n";
?>
