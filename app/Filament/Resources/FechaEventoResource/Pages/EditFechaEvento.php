<?php

namespace App\Filament\Resources\FechaEventoResource\Pages;

use App\Filament\Resources\FechaEventoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFechaEvento extends EditRecord
{
    protected static string $resource = FechaEventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 