<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;
use App\Observers\ModeloObserver;
use App\Models\User;
use Illuminate\View\View as ViewInstance;

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

        View::composer('layouts.admin-navigation', function (ViewInstance $view): void {
            $sidebarUser = auth()->user();
            $sidebarLiaisons = collect();

            if ($sidebarUser?->hasRole('Enlace dependencia') && $sidebarUser->id_institucion) {
                $sidebarLiaisons = User::role('Enlace')
                    ->whereHas('instituciones', fn ($query) => $query->whereKey($sidebarUser->id_institucion))
                    ->orderBy('name')
                    ->get(['id', 'name', 'email', 'telefono']);
            }

            $view->with('sidebarLiaisons', $sidebarLiaisons);
        });
    }
}
