<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IndicadorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatosAnualesIndicadorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CarruselIndicadorController;
use App\Http\Controllers\IndicadorMunicipalController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\MunicipioConvenioController;
use App\Http\Controllers\SliderInicioController;
use App\Http\Controllers\LogCambioController;
use App\Http\Controllers\DatosAbiertosController;
use App\Http\Controllers\CatPlanEstatalDesarrolloController;
use App\Http\Controllers\CatProgramaDerivadoEspecialController;
use App\Http\Controllers\CatProgramaDerivadoSectorialController;
use App\Http\Controllers\CatProgramaDerivadoRegionalController;
use App\Http\Controllers\CatProgramaDerivadoInstitucionalController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\LoginAttemptController;
// use App\Http\Controllers\PublicProgramasController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'mostrarCarrusel']);

Route::get('/informacion-general', function () {
    return view('informacion-general');
});
Route::get('/informacion-general/api', [HomeController::class, 'apiDocs'])->name('public.api_docs');
Route::get('/informacion-general/api/indicador/{id_or_slug}', [HomeController::class, 'apiIndicatorDetail'])->name('public.api_indicator_detail');

Route::get('/normatividad', function () {
    return view('normatividad');
});

Route::get('/datos-abiertos-ped', function () {
    return view('datos-abiertos-ped1');
});

Route::get('/datos-abiertos-hist-ped', function () {
    return view('datos-abiertos-ped2');
});
Route::get('/datos-abiertos-hist-ped-mod-2019', function () {
    return view('datos-abiertos-ped2');
});

Route::get('/datos-abiertos-mun', [DatosAbiertosController::class, 'municipiosIndicadores']);

Route::get('/datos-abiertos-hist-mun', function () {
    return view('datos-abiertos-hist-mun');
});
Route::get('/datos-abiertos/descargar/{municipioId}/{formato}', [DatosAbiertosController::class, 'descargarDatosMunicipio'])
    ->name('datos.municipio.descargar') // Nombre que usamos en route()
    ->where('formato', '(json|csv|xlsx)');

Route::get('/ped', function () {
    return view('ped');
});

Route::get('/ped/eje-{num}', [HomeController::class, 'ped'])->where('num', '[1-6]');

Route::get('/pm', [MunicipioConvenioController::class, 'mostrarMunicipiosConvenio']);
Route::get('/pm/{municipioConvenio}', [MunicipioConvenioController::class, 'show'])
    ->name('pm.show');

// Route::get('/agenda', [HomeController::class, 'indicadoresAgenda2']);

Route::get('/ped-programas', function () {
    return view('mod-ped-programas');
});

Route::get('/ped-programas/sectoriales', [HomeController::class, 'mostrarListadoSectoriales']);
Route::get('/ped-programas/sectoriales/{slug}', [HomeController::class, 'mostrarSectorial']);
// Route::get('/ped-programas/sectoriales/{programa}', [HomeController::class, 'mostrarSectoriales']);

Route::get('/ped-programas/regionales', [HomeController::class, 'mostrarListadoRegionales']);
Route::get('/ped-programas/regionales/{slug}', [HomeController::class, 'mostrarRegional']);

Route::get('/ped-programas/institucionales', function () {
    return view('ped-programas-institucionales');
});

Route::get('/ped-programas/especiales', [HomeController::class, 'mostrarListadoEspeciales']);

Route::get('/ped-programas/especiales/{slug}', [HomeController::class, 'mostrarEspecial']);

Route::get('/ped-programas/institucionales', [HomeController::class, 'mostrarListadoInstitucionales']);

Route::get('/ped-programas/institucionales/{slug}', [HomeController::class, 'mostrarInstitucional']);

Route::get('/ficha-tecnica/{indicador}', [HomeController::class, 'show'])
    ->name('ficha-tecnica.show');

Route::get('/ficha-tecnica-municipal/{id}', [IndicadorMunicipalController::class, 'mostrarFicha'])->name('mostrarFicha');
Route::post('/datos-abiertos-ped', [IndicadorController::class, 'datosAbiertosPed'])->name('datos-abiertos-ped');
Route::post('/datos-abiertos-ped-csv', [IndicadorController::class, 'datosAbiertosPedCsv'])->name('datos-abiertos-ped.csv');
Route::post('/datos-abiertos-ped-json', [IndicadorController::class, 'datosAbiertosPedJson'])->name('datos-abiertos-ped.json');

Route::get('/ficha-tecnica/generar/{id}', [HomeController::class, 'generarFicha'])->name('generarFicha');
Route::get('/capacitacion-2025', [HomeController::class, 'capacitacion2025'])->name('capacitacion-2025');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('panel-roles', RolController::class);
    Route::resource('panel-usuarios', UserController::class);
    Route::resource('panel-cat-planes', CatPlanEstatalDesarrolloController::class);
    Route::resource('panel-cat-prog-der-esp', CatProgramaDerivadoEspecialController::class);
    Route::resource('panel-cat-prog-der-sect', CatProgramaDerivadoSectorialController::class);
    Route::resource('panel-cat-prog-der-reg', CatProgramaDerivadoRegionalController::class);
    Route::resource('panel-cat-prog-der-instit', CatProgramaDerivadoInstitucionalController::class);
    // Route::resource('panel-indicadores', IndicadorController::class);
    Route::resource('panel-indicadores', IndicadorController::class)->parameters([
        'panel-indicadores' => 'indicador' // Aquí le dices que para 'panel-indicadores', el parámetro se llame 'indicador'
    ]);
    Route::resource('panel-municipios-convenio', MunicipioConvenioController::class);
    Route::get('/indicadores-municipales/{id}', [MunicipioConvenioController::class, 'showMunicipal'])->name('indicadores.show_municipal');
    Route::resource('panel-indicadores.datos-anuales', DatosAnualesIndicadorController::class)->shallow();
    Route::resource('panel-slider-inicio', SliderInicioController::class);
    Route::get('/filtrar-indicadores/{institucion}/{programa?}', [IndicadorController::class, 'filtrarIndicadores'])->name('filtrar-indicadores');
    Route::patch('/indicadores/{id}/toggle-validacion', [IndicadorController::class, 'toggleValidacion'])->name('indicadores.toggleValidacion');
    Route::put('indicador/{id}/{year}', [IndicadorController::class, 'updateAnualData'])->name('indicador.updateAnual');
    Route::post('indicador/{id}/anual', [IndicadorController::class, 'storeAnualData'])->name('indicador.storeAnual');
    Route::post('/finalizar-captura', [IndicadorController::class, 'finalizarCaptura'])->name('finalizar.captura');
    Route::get('/panel-indicadores/generar-reporte/{id}', [IndicadorController::class, 'generarReporte'])->name('generarReporte');
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::resource('panel-carrusel-indicadores', CarruselIndicadorController::class);
    Route::put('/actualizacion-resultados-indicadores-municipales/anio/{anio}', [IndicadorMunicipalController::class, 'actualizarResultadosIndMun'])->name('actualizarResultadosIndMun');
    Route::post('/resultados/nuevos', [IndicadorMunicipalController::class, 'guardarResultados'])->name('agregarResultadosNuevoAnio');
    Route::resource('panel-indicadores-municipales', IndicadorMunicipalController::class);
    Route::get('/niveles/{tipoId}', [CatalogoController::class, 'getNiveles']);
    Route::get('/dimensiones/{nivelId}', [CatalogoController::class, 'getDimensiones']);
    Route::patch('/indicadores-municipales/{id}/toggle-validacion', [IndicadorMunicipalController::class, 'toggleValidacion'])
        ->name('indicadores-municipales.toggleValidacion');
    Route::get('/usuarios/{id}/indicadores', [DashboardController::class, 'mostrarIndicadores'])
        ->name('usuarios.indicadores');
    Route::get('/panel-indicadores/semaforizacion/{categoria}', [DashboardController::class, 'semaforizacion'])
        ->name('indicadores.semaforizacion');
    // Route::get('/reporte-indicadores-municipales', [IndicadorMunicipalController::class, 'reporteIndicadores'])->name('reporteIndicadoresMunicipales');
    Route::post('import-excel', [IndicadorController::class, 'import'])->name('excel.import');
    Route::post('/excel/validate-file', [IndicadorController::class, 'validateFile'])->name('excel.validateFile');
    Route::post('/excel/confirm-import', [IndicadorController::class, 'confirmImport'])->name('excel.confirmImport');
    Route::resource('/panel-logs', LogCambioController::class);
    Route::get('/api/programas-derivados', [IndicadorController::class, 'getProgramasDerivados'])->name('api.programas_derivados');
    Route::get('/excel/download-template', [IndicadorController::class, 'downloadTemplate'])->name('excel.downloadTemplate');
    Route::get('/excel/download-users', [IndicadorController::class, 'downloadUsuarios'])->name('excel.downloadUsuarios');
    Route::get('/excel/download-institutions', [IndicadorController::class, 'downloadInstituciones'])->name('excel.downloadInstituciones');
    Route::resource('panel-cat-instituciones', InstitucionController::class)->parameters([
        'panel-cat-instituciones' => 'institucion'
    ]);
    Route::resource('panel-accesos', LoginAttemptController::class)->only(['index']);
});

// Route::get('/pruebas-indicadores', function () {
//     return view('panel-indicadores.prueba');
// });
Route::get('/administrador', function () {
    return view('auth.login');
});
// Old configuration
// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', function () {
//     return view('inicio');
// });
// Route::get('/ped/eje-{num}', [HomeController::class, 'show'])->where('num', '[1-6]');
// Route::get('/mod-ped/eje-{num}', [HomeController::class, 'show'])->where('num', '[1-5]');
// Route::get('/ped/eje-{num}', function ($num) {
//     return view('eje' . $num . '-ped1');
// })->where('num', '[1-6]');

// Route::get('/mod-ped/eje-{num}', function ($num) {
//     return view('eje' . $num . '-ped2');
// })->where('num', '[1-5]');
// Route::get('/pm', function () {
//     return view('planes-mun');
// });
// Route::get('/agenda', function () {
//     return view('agenda1');
// });
// Route::get('/agenda-mod', function () {
//     return view('agenda2');
// });


// Route::get('/ped-programas/sectoriales', [PublicProgramasController::class, 'indexSectoriales']);
// Route::get('/ped-programas/sectoriales/{slug}', [PublicProgramasController::class, 'showSectorial']);

// Route::get('/ped-programas/especiales', function () {
//     return view('ped-programas-especiales');
// });
// Route::get('/ped-programas/institucionales', function () {
//     return view('ped-programas-institucionales');
// });
// Route::get('/ped-programas/regionales', function () {
//     return view('ped-programas-regionales');
// });
// Route::get('/mod-ped-programas/sectoriales', function () {
//     return view('mod-ped-programas-sectoriales');
// });
// Route::get('/mod-ped-programas/regional', function () {
//     return view('mod-ped-programas-regional');
// });
// Route::get('/mod-ped-programas/especiales', function () {
//     return view('mod-ped-programas-especiales');
// });
// Route::get('/mod-ped-programas/institucionales', function () {
//     return view('mod-ped-programas-institucionales');
// });
// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified'
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });
