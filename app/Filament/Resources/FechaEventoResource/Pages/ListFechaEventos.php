<?php

namespace App\Filament\Resources\FechaEventoResource\Pages;

use App\Filament\Resources\FechaEventoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFechaEventos extends ListRecords
{
    protected static string $resource = FechaEventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 