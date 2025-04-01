<?php

namespace App\Filament\Judge\Widgets;

use App\Models\Homologacion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class HomologacionesPendientesWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $totalHomologaciones = Homologacion::count();
        $pendientes = Homologacion::where('estado', Homologacion::ESTADO_PENDIENTE)->count();
        $aprobadas = Homologacion::where('estado', Homologacion::ESTADO_APROBADO)->count();
        $rechazadas = Homologacion::where('estado', Homologacion::ESTADO_RECHAZADO)->count();
        
        $porcentajeCompletadas = $totalHomologaciones > 0 
            ? round((($aprobadas + $rechazadas) / $totalHomologaciones) * 100) 
            : 0;

        return [
            Stat::make('Homologaciones Pendientes', $pendientes)
                ->description('Robots pendientes de evaluación')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([7, 4, $pendientes])
                ->color('warning'),

            Stat::make('Homologaciones Aprobadas', $aprobadas)
                ->description('Robots que cumplen los requisitos')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([3, 5, $aprobadas])
                ->color('success'),

            Stat::make('Porcentaje Completado', $porcentajeCompletadas . '%')
                ->description('Total de homologaciones evaluadas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([2, 5, 7, $porcentajeCompletadas])
                ->color('info'),
        ];
    }
} 