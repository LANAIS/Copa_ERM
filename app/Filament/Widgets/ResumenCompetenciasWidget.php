<?php

namespace App\Filament\Widgets;

use App\Models\CategoriaEvento;
use App\Models\Homologacion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ResumenCompetenciasWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Contar categorías por estado
        $porEstado = CategoriaEvento::select('estado_competencia', DB::raw('count(*) as total'))
            ->groupBy('estado_competencia')
            ->pluck('total', 'estado_competencia')
            ->toArray();
            
        // Contar homologaciones pendientes
        $homologacionesPendientes = DB::table('homologaciones')
            ->where('estado', 'pendiente')
            ->count();
            
        // Contar brackets
        $totalBrackets = DB::table('llaves')->count();
        $bracketsActivos = DB::table('llaves')->where('estado_torneo', 'en_curso')->count();
        
        return [
            Stat::make('Competencias Creadas', $porEstado['creada'] ?? 0)
                ->description('Esperando iniciar inscripciones')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray')
                ->chart([0, 0, 0, 0, 0, $porEstado['creada'] ?? 0]),
                
            Stat::make('En Inscripciones', $porEstado['inscripciones'] ?? 0)
                ->description('Aceptando participantes')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('blue')
                ->chart([0, 0, 0, 0, $porEstado['inscripciones'] ?? 0, 0]),
                
            Stat::make('En Homologación', $porEstado['homologacion'] ?? 0)
                ->description("{$homologacionesPendientes} pendientes")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->chart([0, 0, 0, $porEstado['homologacion'] ?? 0, 0, 0]),
                
            Stat::make('Armado de Llaves', $porEstado['armado_llaves'] ?? 0)
                ->description('Preparando brackets')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info')
                ->chart([0, 0, $porEstado['armado_llaves'] ?? 0, 0, 0, 0]),
                
            Stat::make('En Curso', $porEstado['en_curso'] ?? 0)
                ->description("{$bracketsActivos} brackets activos")
                ->descriptionIcon('heroicon-m-play')
                ->color('success')
                ->chart([0, $porEstado['en_curso'] ?? 0, 0, 0, 0, 0]),
                
            Stat::make('Finalizadas', $porEstado['finalizada'] ?? 0)
                ->description('Competencias completadas')
                ->descriptionIcon('heroicon-m-flag')
                ->color('purple')
                ->chart([$porEstado['finalizada'] ?? 0, 0, 0, 0, 0, 0]),
        ];
    }
} 