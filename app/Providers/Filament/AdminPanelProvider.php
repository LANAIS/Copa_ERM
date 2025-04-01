<?php

namespace App\Providers\Filament;

use App\Filament\Pages\CountdownSettings;
use App\Filament\Pages\SiteSettings;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\LatestInscripciones;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationBuilder;
use App\Filament\Pages\BracketPublicView;
use App\Filament\Pages\BracketAdminView;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->brandName('Copa Robótica 2025')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->font('Poppins')
            ->favicon(\App\Models\SiteConfig::getLogo())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                CountdownSettings::class,
                SiteSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Competencia')
                    ->icon('heroicon-o-trophy')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Torneos')
                    ->icon('heroicon-o-trophy')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Administración')
                    ->icon('heroicon-o-cog')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Inscripciones')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Configuraciones')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make('Gestión de Competencias')
                    ->icon('heroicon-o-chart-bar') 
                    ->isActiveWhen(fn (): bool => request()->is('admin/gestion-competencias*')) 
                    ->url('/admin/gestion-competencias')
                    ->group('Competencia')
                    ->sort(10),
                NavigationItem::make('Brackets')
                    ->icon('heroicon-o-squares-plus')
                    ->url('/admin/llaves')
                    ->group('Torneos')
                    ->sort(1),
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
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\AdminMiddleware::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->spa();
    }
}
