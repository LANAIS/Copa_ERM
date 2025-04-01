<?php

namespace App\Filament\Widgets;

use App\Models\CategoriaEvento;
use App\Models\Llave;
use App\Models\Robot;
use App\Models\Equipo;
use App\Models\InscripcionEvento;
use App\Models\Homologacion;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompetenciaStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $totalCategorias = CategoriaEvento::count();
        $enInscripciones = CategoriaEvento::where('estado_competencia', 'inscripciones')->count();
        $enHomologacion = CategoriaEvento::where('estado_competencia', 'homologacion')->count();
        $enArmadoLlaves = CategoriaEvento::where('estado_competencia', 'armado_llaves')->count();
        $enCurso = CategoriaEvento::where('estado_competencia', 'en_curso')->count();
        $finalizadas = CategoriaEvento::where('estado_competencia', 'finalizada')->count();
        
        $totalBrackets = Llave::count();
        $totalRobots = Robot::count();
        $totalEquipos = Equipo::count();
        $totalInscripciones = InscripcionEvento::count();
        $homologacionesPendientes = DB::table('homologaciones')->where('estado', 'pendiente')->count();

        return [
            Stat::make('Total Categorías', $totalCategorias)
                ->description('Categorías de eventos')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray')
                ->chart([
                    $enInscripciones, $enHomologacion, $enArmadoLlaves, $enCurso, $finalizadas
                ]),
                
            Stat::make('Inscripciones', $enInscripciones)
                ->description('Categorías en inscripciones')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50',
                    'wire:click' => 'dispatch("filterInscripciones")',
                ]),
                
            Stat::make('En Homologación', $enHomologacion)
                ->description($homologacionesPendientes . ' pendientes')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-warning-50',
                    'wire:click' => 'dispatch("filterHomologacion")',
                ]),
                
            Stat::make('Armado de Llaves', $enArmadoLlaves)
                ->description('Categorías en armado de llaves')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-info-50',
                    'wire:click' => 'dispatch("filterArmadoLlaves")',
                ]),
                
            Stat::make('En Curso', $enCurso)
                ->description('Competencias activas')
                ->descriptionIcon('heroicon-m-play')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-success-50',
                    'wire:click' => 'dispatch("filterEnCurso")',
                ]),
                
            Stat::make('Finalizadas', $finalizadas)
                ->description('Competencias completadas')
                ->descriptionIcon('heroicon-m-flag')
                ->color('purple')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-purple-50',
                    'wire:click' => 'dispatch("filterFinalizadas")',
                ]),
                
            Stat::make("Total Brackets", $totalBrackets)
                ->description("Para gestión de competencias")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('info'),
                
            Stat::make("Total Robots", $totalRobots)
                ->description("{$totalEquipos} equipos registrados")
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
                
            Stat::make("Inscripciones", $totalInscripciones)
                ->description("Total del sistema")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
} 