<?php

namespace App\Filament\Competitor\Resources\MisInscripcionesResource\Pages;

use App\Filament\Competitor\Resources\MisInscripcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMisInscripciones extends ListRecords
{
    protected static string $resource = MisInscripcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
