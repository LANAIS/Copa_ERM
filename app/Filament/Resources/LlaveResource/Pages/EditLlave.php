<?php

namespace App\Filament\Resources\LlaveResource\Pages;

use App\Filament\Resources\LlaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLlave extends EditRecord
{
    protected static string $resource = LlaveResource::class;

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