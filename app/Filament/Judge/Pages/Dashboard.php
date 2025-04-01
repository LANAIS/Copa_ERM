<?php

namespace App\Filament\Judge\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Route;
use App\Models\Homologacion;
use App\Models\Enfrentamiento;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Panel Principal';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.judge.pages.dashboard';

    public function getColumns(): int | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Judge\Widgets\HomologacionesPendientesWidget::class,
            \App\Filament\Judge\Widgets\EnfrentamientosPendientesWidget::class,
            \App\Filament\Judge\Widgets\EstadisticasGeneralesWidget::class,
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return 'Panel de Jueces - Copa Robótica';
    }

    public function getSubheading(): string|Htmlable|null
    {
        $homologacionesPendientes = Homologacion::where('estado', Homologacion::ESTADO_PENDIENTE)->count();
        $enfrentamientosPendientes = Enfrentamiento::whereNull('ganador_id')->count();
        
        return "Tienes {$homologacionesPendientes} homologaciones y {$enfrentamientosPendientes} enfrentamientos pendientes";
    }

    protected function getHeaderActions(): array
    {
        $pendientes = Homologacion::where('estado', Homologacion::ESTADO_PENDIENTE)->count();
        
        return [
            Action::make('ir_a_homologaciones')
                ->label('Gestionar Homologaciones')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-check')
                ->badge($pendientes ?: null)
                ->badgeColor('warning')
                ->url(fn (): string => \App\Filament\Judge\Pages\GestionHomologaciones::getUrl()),
                
            Action::make('ver_brackets')
                ->label('Ver Brackets de Competencia')
                ->color('gray')
                ->icon('heroicon-o-trophy')
                ->url(fn (): string => \App\Filament\Judge\Pages\BracketsCompetencia::getUrl()),
        ];
    }
} 