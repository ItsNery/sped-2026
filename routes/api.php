<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API pública versionada. El grupo api aplica throttle:api por defecto.
Route::prefix('v1')->group(function () {
    Route::get('/indicadores', [\App\Http\Controllers\Api\IndicadorApiController::class, 'index'])
        ->name('api.v1.indicadores.index');
    Route::get('/indicadores/{id_or_slug}', [\App\Http\Controllers\Api\IndicadorApiController::class, 'show'])
        ->name('api.v1.indicadores.show');
});

// Compatibilidad con consumidores existentes.
Route::get('/indicadores', [\App\Http\Controllers\Api\IndicadorApiController::class, 'index']);
Route::get('/indicadores/{id_or_slug}', [\App\Http\Controllers\Api\IndicadorApiController::class, 'show']);

