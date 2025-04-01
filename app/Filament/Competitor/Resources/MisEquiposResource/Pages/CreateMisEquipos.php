<?php

namespace App\Filament\Competitor\Resources\MisEquiposResource\Pages;

use App\Filament\Competitor\Resources\MisEquiposResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMisEquipos extends CreateRecord
{
    protected static string $resource = MisEquiposResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el usuario actual como propietario del equipo
        $data['user_id'] = Auth::id();
        
        return $data;
    }
} 