<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use App\Observers\ModeloObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        setlocale(LC_ALL, 'es_ES');
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        // Buscar todos los archivos dentro de app/Models
        $modelos = File::files(app_path('Models'));

        foreach ($modelos as $modelo) {
            $nombreModelo = pathinfo($modelo->getFilename(), PATHINFO_FILENAME);
            $claseModelo = "App\\Models\\$nombreModelo";

            // Verificar si la clase realmente existe y extiende Model
            if (class_exists($claseModelo) && is_subclass_of($claseModelo, Model::class)) {
                $claseModelo::observe(ModeloObserver::class);
            }
        }
    }
}
