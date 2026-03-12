<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$blade = $app['view']->getEngineResolver()->resolve('blade')->getCompiler();
$aliases = Illuminate\Support\Facades\Blade::getClassComponentAliases();

echo "Component Aliases:\n";
foreach ($aliases as $alias => $class) {
    echo "- $alias => $class\n";
}

// Check anonymous components if possible
// Note: Anonymous components are harder to list as they are resolved at runtime by path.
