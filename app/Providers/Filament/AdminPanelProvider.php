<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Enums\ThemeMode;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Le back-office ne repond que sur le portail : sans cela il
            // serait aussi servi sur chaque sous-domaine de book.
            ->domain(config('ubdf.portail_domain'))
            ->authGuard('admin')
            ->brandName('Ultra-book classique V3')
            ->login()
            ->colors([
                'primary' => Color::Slate,
            ])
            // Barre laterale repliable : sur un portable de 1024 px, elle
            // rend ses 16 rem au tableau quand on n'en a pas besoin.
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->defaultThemeMode(ThemeMode::System)
            // Le selecteur clair / sombre, remonte du menu utilisateur vers
            // la barre du haut.
            // Site ferme au public : badge rouge a cote du nom du site.
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): View => view('filament.badge-maintenance'),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): View => view('filament.bascule-theme'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): View => view('filament.styles'),
            )
            // Ouverture en serie des books : le serveur envoie les
            // adresses, le navigateur ouvre les onglets.
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): View => view('filament.scripts'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            /*
             | Ordre de lecture du tableau de bord : les chiffres du jour,
             | les quatre courbes de l'annee comparees a la precedente,
             | puis les dernieres inscriptions, sur lesquelles on agit.
             */
            ->widgets([
                \App\Filament\Widgets\ChiffresCles::class,
                \App\Filament\Widgets\ChiffreAffairesAnnuel::class,
                \App\Filament\Widgets\Encaissements::class,
                \App\Filament\Widgets\InscriptionsCreatifs::class,
                \App\Filament\Widgets\InscriptionsVisiteurs::class,
                \App\Filament\Widgets\Desinscriptions::class,
                \App\Filament\Widgets\DernieresInscriptionsCreatifs::class,
                \App\Filament\Widgets\DernieresInscriptionsVisiteurs::class,
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
