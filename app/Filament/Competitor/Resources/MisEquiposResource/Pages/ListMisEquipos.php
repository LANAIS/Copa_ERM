<?php

namespace App\Filament\Competitor\Resources\MisEquiposResource\Pages;

use App\Filament\Competitor\Resources\MisEquiposResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMisEquipos extends ListRecords
{
    protected static string $resource = MisEquiposResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Equipo'),
        ];
    }
} 