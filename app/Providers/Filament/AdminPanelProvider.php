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
            ->id("admin")
            ->path("admin")
            ->login()
            ->brandName("") // Ocultar texto del nombre
            ->brandLogo(asset("logos/logopanel.png"))
            ->brandLogoHeight("4rem") // Logo más grande y vistoso
            ->colors([
                "primary" => Color::Rose, // Cambiar a rosa para combinar con el logo
            ])
            ->viteTheme("resources/css/filament/admin/theme.css")
            ->renderHook(
                "panels::body.end",
                fn(): string => "<style>" .
                    file_get_contents(
                        resource_path("css/product-image-modal.css"),
                    ) .
                    "</style>",
            )
            ->renderHook(
                "panels::auth.login.form.before",
                fn(): string => '
                <style>
                    /* Logo más grande solo en login */
                    .fi-simple-layout .fi-logo {
                        height: 5rem !important; /* Más grande que el 2.5rem normal */
                        width: auto !important;
                        max-width: 300px !important;
                        margin: 0 auto 2rem auto !important;
                        display: block !important;
                    }

                    /* Centrar el logo en login */
                    .fi-simple-layout .fi-topbar-logo-container {
                        justify-content: center !important;
                        margin-bottom: 1.5rem !important;
                    }

                    /* Mejorar spacing del login */
                    .fi-simple-layout .fi-simple-page {
                        padding-top: 2rem !important;
                    }
                </style>',
            )
            ->renderHook(
                "panels::head.start",
                fn(): string => '
                <style>
                    /* Logo en el header */
                    .fi-topbar .fi-logo {
                        height: 4rem !important;
                        width: auto !important;
                        max-width: none !important;
                        margin-right: 1rem !important;
                    }

                    /* Asegurar que el contenedor del logo se ajuste */
                    .fi-topbar-logo-container {
                        display: flex !important;
                        align-items: center !important;
                    }
                </style>',
            )
            ->discoverResources(
                in: app_path("Filament/Resources"),
                for: "App\Filament\Resources",
            )
            ->discoverPages(
                in: app_path("Filament/Pages"),
                for: "App\Filament\Pages",
            )
            ->pages([Dashboard::class])
            ->discoverWidgets(
                in: app_path("Filament/Widgets"),
                for: "App\Filament\Widgets",
            )
            ->widgets([
                \App\Filament\Widgets\PosStatsOverview::class,
                AccountWidget::class,
            ])
            ->globalSearch(true)
            ->globalSearchKeyBindings(["command+k", "ctrl+k"])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make("Gestión Comercial")
                    ->icon("iconoir-shop")
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make("Inventario")
                    ->icon("iconoir-packages")
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make("Facturación")
                    ->icon("iconoir-credit-card")
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make("Administración")
                    ->icon("iconoir-settings")
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make("Sistema")
                    ->icon("iconoir-system-restart")
                    ->collapsible()
                    ->collapsed(),
            ])
            ->plugins([
                IconoirIcons::make()->regular(), // Set Iconoir icons as default
                BriskTheme::make(),
                FilamentLogViewer::make()
                    ->authorize(fn() => auth()->check())
                    ->navigationGroup(__("Sistema"))
                    ->navigationIcon("iconoir-page")
                    ->navigationLabel(__("Visor de Logs"))
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
            ->authMiddleware([Authenticate::class]);
    }
}
