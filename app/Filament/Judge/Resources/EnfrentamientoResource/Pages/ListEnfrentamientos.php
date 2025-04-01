<?php

namespace App\Filament\Judge\Resources\EnfrentamientoResource\Pages;

use App\Filament\Judge\Resources\EnfrentamientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnfrentamientos extends ListRecords
{
    protected static string $resource = EnfrentamientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 