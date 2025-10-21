<?php

namespace App\Providers;

use App\Models\WebConfiguration;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartir configuración web con todas las vistas
        View::composer('partials.footer', function ($view) {
            // Por ahora, tomar la primera configuración disponible
            // Luego se puede modificar para tomar según la empresa activa
            $webConfig = WebConfiguration::first();
            
            $view->with('webConfig', $webConfig);
        });
    }
}
