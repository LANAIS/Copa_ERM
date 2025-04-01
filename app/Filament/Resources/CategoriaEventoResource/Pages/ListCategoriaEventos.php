<?php

namespace App\Filament\Resources\CategoriaEventoResource\Pages;

use App\Filament\Resources\CategoriaEventoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaEventos extends ListRecords
{
    protected static string $resource = CategoriaEventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('dashboard')
                ->label('Dashboard de Competencias')
                ->icon('heroicon-o-presentation-chart-bar')
                ->url(fn (): string => route('filament.admin.pages.gestion-competencias-dashboard'))
                ->color('secondary'),
        ];
    }
} 