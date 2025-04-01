<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
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
use App\Http\Middleware\JudgeMiddleware;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use App\Filament\Judge\Pages\BracketListView;
use App\Filament\Judge\Pages\BracketsCompetencia;
use App\Filament\Judge\Pages\GestionHomologaciones;

class JudgePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('judge')
            ->path('judge')
            ->login()
            ->brandName('Panel de Jueces')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->favicon(\App\Models\SiteConfig::getLogo())
            ->resources([
                \App\Filament\Judge\Resources\EnfrentamientoResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Judge/Resources'), for: 'App\\Filament\\Judge\\Resources')
            ->pages([
                \App\Filament\Judge\Pages\Dashboard::class,
                \App\Filament\Judge\Pages\BracketsCompetencia::class,
                \App\Filament\Judge\Pages\GestionHomologaciones::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Judge/Widgets'), for: 'App\\Filament\\Judge\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Judge\Widgets\HomologacionesPendientesWidget::class,
                \App\Filament\Judge\Widgets\EnfrentamientosPendientesWidget::class,
                \App\Filament\Judge\Widgets\EstadisticasGeneralesWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Competencia')
                    ->icon('heroicon-o-trophy')
                    ->collapsed(false),
            ])
            ->navigationItems([
                NavigationItem::make('Gestión de Homologaciones')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.judge.pages.gestion-homologaciones'))
                    ->url(fn (): string => \App\Filament\Judge\Pages\GestionHomologaciones::getUrl())
                    ->group('Competencia')
                    ->sort(1),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                JudgeMiddleware::class,
            ])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full');
    }
} 