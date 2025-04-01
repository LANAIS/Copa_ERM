<?php

namespace App\Filament\Judge\Resources\EnfrentamientoResource\Pages;

use App\Filament\Judge\Resources\EnfrentamientoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEnfrentamiento extends CreateRecord
{
    protected static string $resource = EnfrentamientoResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 