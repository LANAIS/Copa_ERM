<?php

namespace App\Filament\Resources\CategoriaEventoResource\Pages;

use App\Filament\Resources\CategoriaEventoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaEvento extends EditRecord
{
    protected static string $resource = CategoriaEventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 