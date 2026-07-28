<?php
// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Indicador;
use App\Models\CatProgramaDerivadoInstitucional;
use App\Models\CatEje;
use App\Models\CatProgramaDerivadoSectorial;
use App\Models\CatProgramaDerivadoEspecial;
use App\Models\CatProgramaDerivadoRegional;

echo "--- 1. Testing getSiglasAttribute accessor ---\n";
$prog = new CatProgramaDerivadoInstitucional();
$prog->nombre = "Programa Institucional del Instituto de Seguridad y Servicios Sociales de los Trabajadores al Servicio de los Poderes del Estado de Puebla";
echo "Name: " . $prog->nombre . "\n";
echo "Siglas (Generated): " . $prog->siglas . "\n";

$prog2 = new CatProgramaDerivadoInstitucional();
$prog2->nombre = "Programa Institucional de la Secretaría de Movilidad y Transporte";
echo "Name: " . $prog2->nombre . "\n";
echo "Siglas (Generated): " . $prog2->siglas . "\n";

$prog3 = new CatProgramaDerivadoInstitucional();
$prog3->nombre = "Programa Institucional de la Secretaría de Infraestructura";
echo "Name: " . $prog3->nombre . "\n";
echo "Siglas (Generated): " . $prog3->siglas . "\n";

echo "\n--- 2. Testing N:M Indicator - Institutional Program Relation ---\n";
$indicadores = Indicador::whereHas('programasInstitucionales')->with('programasInstitucionales')->get();
echo "Found " . $indicadores->count() . " indicators linked to institutional programs.\n";
foreach ($indicadores as $ind) {
    echo "Indicator ID {$ind->id}: {$ind->nombre}\n";
    foreach ($ind->programasInstitucionales as $p) {
        echo "  -> Linked to Program: {$p->nombre} (Siglas: {$p->siglas}, Grupo: {$p->grupo})\n";
    }
}

echo "\n--- 3. Testing Institutional Program - Indicators Relation ---\n";
$programas = CatProgramaDerivadoInstitucional::with('indicadores')->get();
foreach ($programas as $p) {
    echo "Program ID {$p->id}: {$p->nombre} (Siglas: {$p->siglas}, Grupo: {$p->grupo})\n";
    echo "  -> Has " . $p->indicadores->count() . " indicators.\n";
    foreach ($p->indicadores as $ind) {
        echo "     - Indicator ID {$ind->id}: {$ind->nombre}\n";
    }
}

echo "\n--- 4. Testing Dashboard Queries (\$planId = 3) ---\n";
$planId = 3;
$indicadoresPlan = Indicador::where(function ($query) use ($planId) {
    $query->whereHasMorph('indicadorable', [CatEje::class], function ($q) use ($planId) {
        $q->where('plan_id', $planId);
    })->orWhereHasMorph('indicadorable', [
        CatProgramaDerivadoSectorial::class,
        CatProgramaDerivadoEspecial::class,
        CatProgramaDerivadoRegional::class
    ], function ($q) use ($planId) {
        $q->where('plan_estatal', $planId);
    })->orWhereHas('programasInstitucionales', function ($q) use ($planId) {
        $q->where('plan_estatal', $planId);
    });
})->get();
echo "Dashboard Query found: " . $indicadoresPlan->count() . " indicators for Plan ID 3.\n";

$orphans = Indicador::whereIn('id', [2, 4])->get();
foreach ($orphans as $orphan) {
    echo "Orphan check Indicator ID {$orphan->id}: Eje ID is {$orphan->indicadorable_id}\n";
}
echo "\nAll checks completed!\n";
