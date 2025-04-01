<?php

namespace App\Filament\Resources\FechaEventoResource\Pages;

use App\Filament\Resources\FechaEventoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFechaEvento extends CreateRecord
{
    protected static string $resource = FechaEventoResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 