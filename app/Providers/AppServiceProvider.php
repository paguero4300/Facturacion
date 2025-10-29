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
        // Registrar observadores
        \App\Models\InventoryMovement::observe(\App\Observers\InventoryMovementObserver::class);
        \App\Models\Invoice::observe(\App\Observers\InvoiceObserver::class);
        \App\Models\Invoice::observe(\App\Observers\InvoiceDeliveryObserver::class);
        \App\Models\InvoiceDetail::observe(\App\Observers\InvoiceDetailObserver::class);
        \App\Models\PaymentInstallment::observe(\App\Observers\PaymentInstallmentObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        
        // Compartir configuración web con todas las vistas
        View::composer('partials.footer', function ($view) {
            // Por ahora, tomar la primera configuración disponible
            // Luego se puede modificar para tomar según la empresa activa
            $webConfig = WebConfiguration::first();
            
            $view->with('webConfig', $webConfig);
        });
    }
}
