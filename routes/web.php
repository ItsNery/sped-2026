<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\IndicadorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatosAnualesIndicadorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IndicadorMunicipalController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\MunicipioConvenioController;
use App\Http\Controllers\LogCambioController;
use App\Http\Controllers\DatosAbiertosController;
use App\Http\Controllers\CatPlanEstatalDesarrolloController;
use App\Http\Controllers\CatEjeController;
use App\Http\Controllers\CatProgramaDerivadoEspecialController;
use App\Http\Controllers\CatProgramaDerivadoSectorialController;
use App\Http\Controllers\CatProgramaDerivadoRegionalController;
use App\Http\Controllers\CatProgramaDerivadoInstitucionalController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\LoginAttemptController;
use App\Http\Controllers\DashboardGeneralController;
use App\Http\Controllers\DashboardExportController;
use App\Http\Controllers\DashboardDrillDownController;
// use App\Http\Controllers\PublicProgramasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Aquí es donde puedes registrar las rutas web para tu aplicación.
|
| Estructura recomendada:
| 1. Rutas Públicas (Home, Vistas estáticas)
| 2. Rutas de Datos Abiertos
| 3. Rutas del Plan Estatal de Desarrollo (PED) y Programas
| 4. Rutas de Fichas Técnicas
| 5. Rutas Protegidas (Dashboard / Administración)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 1. Rutas Públicas Principales
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'inicio']);
Route::get('/informacion-general/api', [HomeController::class, 'apiDocs'])->name('public.api_docs');

Route::get('/normatividad', function () {
    return view('normatividad');
});
Route::get('/agenda', [HomeController::class, 'indicadoresAgenda']);
Route::get('/capacitacion-2025', [HomeController::class, 'capacitacion2025'])->name('capacitacion-2025');

// Acceso directo al login
Route::get('/administrador', function () {
    return view('auth.login');
});

/*
|--------------------------------------------------------------------------
| 2. Rutas de Datos Abiertos (Publicación y Descarga)
|--------------------------------------------------------------------------
*/
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
    ->name('datos.municipio.descargar')
    ->where('formato', '(json|csv|xlsx)');

// Procesamiento de datos abiertos PED
Route::post('/datos-abiertos-ped', [IndicadorController::class, 'datosAbiertosPed'])->name('datos-abiertos-ped');
Route::post('/datos-abiertos-ped-csv', [IndicadorController::class, 'datosAbiertosPedCsv'])->name('datos-abiertos-ped.csv');
Route::post('/datos-abiertos-ped-json', [IndicadorController::class, 'datosAbiertosPedJson'])->name('datos-abiertos-ped.json');

/*
|--------------------------------------------------------------------------
| 3. Plan Estatal de Desarrollo (PED) y Programas Derivados
|--------------------------------------------------------------------------
*/
// Route::get('/ped', function () {
//     return view('ped');
// });
Route::get('/ped', [DashboardGeneralController::class, 'publicIndex'])->name('public.avance-general');
Route::get('/ped/eje-{num}', [HomeController::class, 'ped'])->where('num', '[1-6]');
Route::get('/buscar-indicadores', [HomeController::class, 'buscarIndicadores'])->name('public.buscar-indicadores');

// Programas Generales
Route::get('/ped-programas', function () {
    return view('mod-ped-programas');
});

// Programas Sectoriales
Route::get('/ped-programas/sectoriales', [HomeController::class, 'mostrarListadoSectoriales']);
Route::get('/ped-programas/sectoriales/{slug}', [HomeController::class, 'mostrarSectorial']);

// Programas Regionales
Route::get('/ped-programas/regionales', [HomeController::class, 'mostrarListadoRegionales']);
Route::get('/ped-programas/regionales/{slug}', [HomeController::class, 'mostrarRegional']);

// Programas Institucionales
Route::get('/ped-programas/institucionales', [HomeController::class, 'mostrarListadoInstitucionales']);
Route::get('/ped-programas/institucionales/{slug}', [HomeController::class, 'mostrarInstitucional']);

// Programas Especiales
Route::get('/ped-programas/especiales', [HomeController::class, 'mostrarListadoEspeciales']);
Route::get('/ped-programas/especiales/{slug}', [HomeController::class, 'mostrarEspecial']);

// Municipios Convenio
Route::get('/pm', [MunicipioConvenioController::class, 'mostrarMunicipiosConvenio']);
Route::get('/pm/{municipioConvenio}', [MunicipioConvenioController::class, 'show'])
    ->name('pm.show');

/*
|--------------------------------------------------------------------------
| 4. Fichas Técnicas (Vistas individuales de indicadores)
|--------------------------------------------------------------------------
*/
// Se utiliza Route Model Binding para inyectar el indicador automáticamente (asumiendo que busca por slug)
Route::get('/ficha-tecnica/{indicador}/vista-previa', [HomeController::class, 'fichaPreview'])
    ->name('ficha-tecnica.preview');
Route::get('/ficha-tecnica/{indicador}/descargar', [HomeController::class, 'downloadFicha'])
    ->name('ficha-tecnica.download');
Route::get('/ficha-tecnica/{indicador}', [HomeController::class, 'show'])
    ->name('ficha-tecnica.show');
Route::get('/ficha-tecnica-municipal/{indicador}/descargar', [IndicadorMunicipalController::class, 'descargarFicha'])->name('mostrarFicha.download');
Route::get('/ficha-tecnica-municipal/{indicador}', [IndicadorMunicipalController::class, 'mostrarFicha'])->name('mostrarFicha');
Route::get('/ficha-tecnica/generar/{id}', [HomeController::class, 'generarFicha'])->name('generarFicha');

/*
|--------------------------------------------------------------------------
| 5. Rutas de Administración Protegidas (Requieren autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // --- Dashboards Generales ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/exportar/pdf', [DashboardExportController::class, 'pdf'])->name('dashboard.export.pdf');
    Route::get('/dashboard/exportar/xlsx', [DashboardExportController::class, 'xlsx'])->name('dashboard.export.xlsx');
    Route::get('/dashboard/drill-down', [DashboardDrillDownController::class, 'index'])->name('dashboard.drill-down');
    Route::get('/panel-avance-general', [DashboardGeneralController::class, 'adminIndex'])
        ->middleware('permission:ver-panel-avance-general')
        ->name('admin.avance-general');
    Route::get('/usuarios/{id}/indicadores', [DashboardController::class, 'mostrarIndicadores'])
        ->name('usuarios.indicadores');

    // --- Gestión de Accesos y Usuarios ---
    Route::resource('panel-roles', RolController::class);
    Route::resource('panel-usuarios', UserController::class);
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::resource('panel-accesos', LoginAttemptController::class)->only(['index']);

    // --- Catálogos y Entidades Base ---
    Route::resource('panel-cat-planes', CatPlanEstatalDesarrolloController::class);
    Route::resource('panel-cat-ejes', CatEjeController::class);
    Route::resource('panel-cat-prog-der-esp', CatProgramaDerivadoEspecialController::class);
    Route::resource('panel-cat-prog-der-sect', CatProgramaDerivadoSectorialController::class);
    Route::resource('panel-cat-prog-der-reg', CatProgramaDerivadoRegionalController::class);
    Route::resource('panel-cat-prog-der-instit', CatProgramaDerivadoInstitucionalController::class);
    Route::resource('panel-cat-instituciones', InstitucionController::class)->parameters([
        'panel-cat-instituciones' => 'institucion'
    ]);

    // --- Indicadores Base y Datos Anuales ---
    Route::get('/filtros-indicadores/opciones', [IndicadorController::class, 'opcionesFiltro'])->name('filtros-indicadores.opciones');
    Route::resource('panel-indicadores', IndicadorController::class)->parameters([
        'panel-indicadores' => 'indicador'
    ]);
    Route::resource('panel-indicadores.datos-anuales', DatosAnualesIndicadorController::class)->shallow();
    Route::get('/subir-indicadores-masivo', function () {
        return view('panel-indicadores.prueba');
    })->middleware('permission:subida-masiva-indicador');
    // Acciones específicas para Indicadores
    Route::get('/filtrar-indicadores/{institucion}/{programa?}', [IndicadorController::class, 'filtrarIndicadores'])->name('filtrar-indicadores');
    Route::patch('/indicadores/{id}/toggle-validacion', [IndicadorController::class, 'toggleValidacion'])->name('indicadores.toggleValidacion');
    Route::patch('/indicadores/{id}/{year}/toggle-validacion-anual', [IndicadorController::class, 'toggleValidacionAnual'])->name('indicadores.toggleValidacionAnual');
    Route::put('indicador/{id}/{year}', [IndicadorController::class, 'updateAnualData'])->name('indicador.updateAnual');
    Route::post('indicador/{id}/anual', [IndicadorController::class, 'storeAnualData'])->name('indicador.storeAnual');
    Route::post('/finalizar-captura', [IndicadorController::class, 'finalizarCaptura'])->name('finalizar.captura');
    Route::get('/panel-indicadores/semaforizacion/{categoria}', [DashboardController::class, 'semaforizacion'])
        ->name('indicadores.semaforizacion');
    Route::get('/panel-indicadores/generar-reporte/{id}', [IndicadorController::class, 'generarReporte'])->name('generarReporte');

    // --- Indicadores Municipales ---
    Route::get('panel-municipios-convenio/{municipioConvenio}/indicadores', [MunicipioConvenioController::class, 'indicadores'])
        ->name('panel-municipios-convenio.indicadores');
    Route::resource('panel-municipios-convenio', MunicipioConvenioController::class);
    Route::resource('panel-indicadores-municipales', IndicadorMunicipalController::class);
    Route::get('/indicadores-municipales/{id}', [MunicipioConvenioController::class, 'showMunicipal'])->name('indicadores.show_municipal');
    Route::put('/actualizacion-resultados-indicadores-municipales/anio/{anio}', [IndicadorMunicipalController::class, 'actualizarResultadosIndMun'])->name('actualizarResultadosIndMun');
    Route::post('/resultados/nuevos', [IndicadorMunicipalController::class, 'guardarResultados'])->name('agregarResultadosNuevoAnio');
    Route::patch('/indicadores-municipales/{id}/toggle-validacion', [IndicadorMunicipalController::class, 'toggleValidacion'])
        ->name('indicadores-municipales.toggleValidacion');
    // Route::get('/reporte-indicadores-municipales', [IndicadorMunicipalController::class, 'reporteIndicadores'])->name('reporteIndicadoresMunicipales');

    // --- Importación/Exportación Masiva (Excel) ---
    Route::post('import-excel', [IndicadorController::class, 'import'])
        ->middleware('permission:subida-masiva-indicador')
        ->name('excel.import');
    Route::post('/excel/validate-file', [IndicadorController::class, 'validateFile'])
        ->middleware('permission:subida-masiva-indicador')
        ->name('excel.validateFile');
    Route::post('/excel/confirm-import', [IndicadorController::class, 'confirmImport'])
        ->middleware('permission:subida-masiva-indicador')
        ->name('excel.confirmImport');
    Route::get('/excel/download-template', [IndicadorController::class, 'downloadTemplate'])
        ->middleware('permission:subida-masiva-indicador')
        ->name('excel.downloadTemplate');
    Route::get('/excel/download-users', [IndicadorController::class, 'downloadUsuarios'])
        ->middleware('permission:subida-masiva-indicador')
        ->name('excel.downloadUsuarios');
    Route::get('/excel/download-institutions', [IndicadorController::class, 'downloadInstituciones'])
        ->middleware('permission:subida-masiva-indicador')
        ->name('excel.downloadInstituciones');

    // --- APIs Internas y Logs ---
    Route::get('/niveles/{tipoId}', [CatalogoController::class, 'getNiveles']);
    Route::get('/dimensiones/{nivelId}', [CatalogoController::class, 'getDimensiones']);
    Route::resource('/panel-logs', LogCambioController::class)->middleware('permission:ver-logs');
    Route::get('/api/programas-derivados', [IndicadorController::class, 'getProgramasDerivados'])->name('api.programas_derivados');
});

/*
|--------------------------------------------------------------------------
| Rutas de Pruebas
|--------------------------------------------------------------------------
*/
// Route::get('/pruebas-indicadores', function () {
//     return view('panel-indicadores.prueba');
// });
