<?php

namespace App\Filament\Widgets;

use App\Models\Inscripcion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Inscripciones', Inscripcion::count())
                ->description('Todas las inscripciones registradas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            
            Stat::make('Inscripciones Pendientes', Inscripcion::where('estado', 'pendiente')->count())
                ->description('Inscripciones sin aprobar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Inscripciones Aprobadas', Inscripcion::where('estado', 'aprobada')->count())
                ->description('Inscripciones aprobadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Inscripciones Rechazadas', Inscripcion::where('estado', 'rechazada')->count())
                ->description('Inscripciones rechazadas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
} 