<?php

namespace App\Filament\Judge\Widgets;

use App\Models\Enfrentamiento;
use App\Models\Llave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EnfrentamientosPendientesWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $totalEnfrentamientos = Enfrentamiento::count();
        $pendientes = Enfrentamiento::whereNull('ganador_id')
            ->whereNotNull('equipo1_id')
            ->whereNotNull('equipo2_id')
            ->count();
        
        $completados = Enfrentamiento::whereNotNull('ganador_id')->count();
        
        $porcentajeCompletados = $totalEnfrentamientos > 0 
            ? round(($completados / $totalEnfrentamientos) * 100) 
            : 0;
            
        // Obtener el número de llaves activas
        $llavesActivas = Llave::where('estado_torneo', 'en_curso')->count();

        return [
            Stat::make('Enfrentamientos Pendientes', $pendientes)
                ->description('Partidos pendientes de evaluación')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([5, 3, $pendientes])
                ->color('warning'),

            Stat::make('Enfrentamientos Completados', $completados)
                ->description('Partidos con resultados registrados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([2, 4, $completados])
                ->color('success'),

            Stat::make('Llaves Activas', $llavesActivas)
                ->description('Torneos actualmente en curso')
                ->descriptionIcon('heroicon-m-trophy')
                ->chart([1, 3, $llavesActivas])
                ->color('primary'),
        ];
    }
} 