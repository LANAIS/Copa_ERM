<?php

namespace App\Filament\Competitor\Resources\MisRobotsResource\Pages;

use App\Filament\Competitor\Resources\MisRobotsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMisRobots extends ListRecords
{
    protected static string $resource = MisRobotsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
