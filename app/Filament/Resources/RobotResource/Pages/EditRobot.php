<?php

namespace App\Filament\Resources\RobotResource\Pages;

use App\Filament\Resources\RobotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRobot extends EditRecord
{
    protected static string $resource = RobotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 