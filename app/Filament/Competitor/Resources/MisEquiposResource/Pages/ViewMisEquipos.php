<?php

namespace App\Filament\Competitor\Resources\MisEquiposResource\Pages;

use App\Filament\Competitor\Resources\MisEquiposResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMisEquipos extends ViewRecord
{
    protected static string $resource = MisEquiposResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
} 