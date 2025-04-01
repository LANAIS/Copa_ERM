<?php

namespace App\Filament\Resources\LlaveResource\Pages;

use App\Filament\Resources\LlaveResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLlave extends CreateRecord
{
    protected static string $resource = LlaveResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 