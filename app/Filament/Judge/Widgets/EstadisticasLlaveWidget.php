<?php

namespace App\Filament\Judge\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Llave;
use App\Models\Enfrentamiento;

class EstadisticasLlaveWidget extends BaseWidget
{
    public ?Llave $llave = null;

    protected function getStats(): array
    {
        if (!$this->llave) {
            return [
                Stat::make('Selecciona una llave', '')
                    ->description('Para ver las estadísticas')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
            ];
        }

        $totalEnfrentamientos = $this->llave->enfrentamientos()->count();
        $enfrentamientosCompletados = $this->llave->enfrentamientos()->whereNotNull('ganador_id')->count();
        $porcentajeCompletado = $totalEnfrentamientos > 0 
            ? round(($enfrentamientosCompletados / $totalEnfrentamientos) * 100) 
            : 0;

        return [
            Stat::make('Total Enfrentamientos', $totalEnfrentamientos)
                ->description('Enfrentamientos en la llave')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),

            Stat::make('Enfrentamientos Completados', $enfrentamientosCompletados)
                ->description('Con resultados registrados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Progreso', $porcentajeCompletado . '%')
                ->description('De la llave completada')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($porcentajeCompletado === 100 ? 'success' : 'warning'),
        ];
    }
} 