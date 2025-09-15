<?php

namespace App\Providers;

use App\Filament\Pages\Pos;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\PaymentInstallment;
use App\Models\Product;
use App\Observers\InvoiceObserver;
use App\Observers\InvoiceDetailObserver;
use App\Observers\PaymentInstallmentObserver;
use App\Observers\ProductObserver;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
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
        Invoice::observe(InvoiceObserver::class);
        InvoiceDetail::observe(InvoiceDetailObserver::class);
        PaymentInstallment::observe(PaymentInstallmentObserver::class);
        Product::observe(ProductObserver::class);
        
        // Ocultar header completo en la página POS
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_BEFORE,
            fn (): string => request()->routeIs('filament.admin.pages.pos') ? '<style>.fi-header { display: none !important; }</style>' : '',
            scopes: [Pos::class]
        );
    }
}
