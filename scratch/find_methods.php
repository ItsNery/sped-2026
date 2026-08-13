<?php
$content = file_get_contents('c:/laragon/www/sped/app/Http/Controllers/IndicadorController.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (preg_match('/public\s+function\s+([a-zA-Z0-9_]+)/', $line, $matches)) {
        echo ($i + 1) . ": " . $matches[0] . "\n";
    }
}
