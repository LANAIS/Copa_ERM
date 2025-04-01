<?php

namespace App\Filament\Competitor\Resources\MisEquiposResource\Pages;

use App\Filament\Competitor\Resources\MisEquiposResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMisEquipos extends EditRecord
{
    protected static string $resource = MisEquiposResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }
} 