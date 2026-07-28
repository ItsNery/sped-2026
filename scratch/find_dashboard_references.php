<?php
$files = [
    'c:/laragon/www/sped/app/Http/Controllers/DashboardController.php',
    'c:/laragon/www/sped/app/Http/Controllers/DashboardGeneralController.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (strpos($line, 'CatProgramaDerivadoInstitucional') !== false || strpos($line, 'programasInstitucionales') !== false) {
            echo basename($file) . ":" . ($i + 1) . ": " . trim($line) . "\n";
        }
    }
}
