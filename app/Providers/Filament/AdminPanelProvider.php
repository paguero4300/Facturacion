<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use AchyutN\FilamentLogViewer\FilamentLogViewer;
use Filafly\Themes\Brisk\BriskTheme;
use Filafly\Icons\Iconoir\IconoirIcons;
use Filament\Navigation\NavigationGroup;
use App\Models\Company;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn () => Company::where('is_active', true)->first()?->commercial_name ?? config('app.name'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                'panels::body.end',
                fn (): string => '<style>' . file_get_contents(resource_path('css/product-image-modal.css')) . '</style>'
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\PosStatsOverview::class,
                AccountWidget::class,
            ])
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Gestión Comercial')
                    ->icon('iconoir-shop')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Reportes de ventas')
                    ->icon('iconoir-stats-report')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Inventario')
                    ->icon('iconoir-packages')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Facturación')
                    ->icon('iconoir-credit-card')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Administración')
                    ->icon('iconoir-settings')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Sistema')
                    ->icon('iconoir-system-restart')
                    ->collapsible()
                    ->collapsed(),
            ])
            ->plugins([
                IconoirIcons::make()->regular(), // Set Iconoir icons as default
                BriskTheme::make(),
                FilamentLogViewer::make()
                    ->authorize(fn () => auth()->check())
                    ->navigationGroup(__('Sistema'))
                    ->navigationIcon('iconoir-page')
                    ->navigationLabel(__('Visor de Logs'))
                    ->navigationSort(100)
                    ->pollingTime(null), // Disable auto-refresh
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
