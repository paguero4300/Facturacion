<?php

namespace App\Providers;

use App\Services\QpseService;
use App\Services\QpseGreenterAdapter;
use App\Services\GreenterXmlService;
use Illuminate\Support\ServiceProvider;

class QpseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el servicio QPse
        $this->app->singleton(QpseService::class, function ($app) {
            return new QpseService();
        });

        // Registrar el servicio Greenter XML
        $this->app->singleton(GreenterXmlService::class, function ($app) {
            return new GreenterXmlService();
        });

        // Registrar el adaptador QPse-Greenter
        $this->app->singleton(QpseGreenterAdapter::class, function ($app) {
            return new QpseGreenterAdapter(
                $app->make(QpseService::class),
                $app->make(GreenterXmlService::class)
            );
        });

        // Alias para fácil acceso
        $this->app->alias(QpseService::class, 'qpse');
        $this->app->alias(QpseGreenterAdapter::class, 'qpse.greenter');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publicar configuración si es necesario
        $this->publishes([
            __DIR__.'/../../config/qpse.php' => config_path('qpse.php'),
        ], 'qpse-config');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            QpseService::class,
            QpseGreenterAdapter::class,
            'qpse',
            'qpse.greenter',
        ];
    }
}