<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CatProgramaDerivadoInstitucional;

$programas = CatProgramaDerivadoInstitucional::whereNull('siglas')
    ->orWhere('siglas', '')
    ->get();

echo "Generando siglas para " . $programas->count() . " programas institucionales...\n";

foreach ($programas as $p) {
    $siglasAutogeneradas = $p->siglas; 
    $p->siglas = $siglasAutogeneradas;
    $p->save();
    
    echo "ID {$p->id}: {$p->nombre} -> Siglas: {$siglasAutogeneradas}\n";
}

echo "Proceso finalizado con éxito.\n";
