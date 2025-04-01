<?php

namespace App\Filament\Competitor\Resources\MisRobotsResource\Pages;

use App\Filament\Competitor\Resources\MisRobotsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMisRobots extends EditRecord
{
    protected static string $resource = MisRobotsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
