<?php

namespace App\Filament\Resources\RobotResource\Pages;

use App\Filament\Resources\RobotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRobots extends ListRecords
{
    protected static string $resource = RobotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 