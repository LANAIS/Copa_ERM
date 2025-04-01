<?php

namespace App\Filament\Resources\LlaveResource\Pages;

use App\Filament\Resources\LlaveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLlaves extends ListRecords
{
    protected static string $resource = LlaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 