<?php
$content = file_get_contents('c:/laragon/www/sped/app/Http/Controllers/HomeController.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (preg_match('/function\s+([a-zA-Z0-9_]*institucional[a-zA-Z0-9_]*)/i', $line, $matches)) {
        echo ($i + 1) . ": " . $matches[0] . "\n";
    }
}
