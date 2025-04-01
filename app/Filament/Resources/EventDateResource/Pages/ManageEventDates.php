<?php

namespace App\Filament\Resources\EventDateResource\Pages;

use App\Filament\Resources\EventDateResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEventDates extends ManageRecords
{
    protected static string $resource = EventDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 