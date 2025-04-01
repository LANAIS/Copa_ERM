<?php

namespace App\Filament\Competitor\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Facades\Auth;
use App\Models\Robot;
use App\Models\Equipo;
use App\Models\Registration;
use App\Models\Competition;
use Filament\Support\Enums\IconPosition;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.competitor.pages.dashboard';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationGroup = null;

    protected static ?string $title = 'Dashboard de Competidor';

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('create-robot')
                    ->label('Nuevo Robot')
                    ->icon('heroicon-o-cog')
                    ->iconPosition(IconPosition::After)
                    ->url(route('filament.competitor.resources.mis-robots.create')),
                Action::make('create-team')
                    ->label('Nuevo Equipo')
                    ->icon('heroicon-o-user-group')
                    ->iconPosition(IconPosition::After)
                    ->url(route('filament.competitor.resources.mis-equipos.create')),
                Action::make('create-inscription')
                    ->label('Nueva Inscripción')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->iconPosition(IconPosition::After)
                    ->url(route('filament.competitor.resources.mis-inscripciones.create')),
            ])
            ->label('Acciones Rápidas')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->button(),
        ];
    }
} 