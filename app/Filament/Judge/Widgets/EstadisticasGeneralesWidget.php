<?php

namespace App\Filament\Judge\Widgets;

use App\Models\CategoriaEvento;
use App\Models\Enfrentamiento;
use App\Models\Equipo;
use App\Models\Robot;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EstadisticasGeneralesWidget extends ChartWidget
{
    protected static ?string $heading = 'Estadísticas por Categoría';
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $categoriasEventos = CategoriaEvento::with('categoria')->get();
        
        $labels = $categoriasEventos->map(fn ($ce) => $ce->categoria->nombre ?? 'Sin nombre')->toArray();
        
        // Robots por categoría
        $robotsPorCategoria = [];
        foreach ($categoriasEventos as $categoriaEvento) {
            $robotsPorCategoria[] = Robot::whereHas('homologaciones', function($query) use ($categoriaEvento) {
                $query->where('categoria_evento_id', $categoriaEvento->id);
            })->count();
        }
        
        // Enfrentamientos por categoría
        $enfrentamientosPorCategoria = [];
        foreach ($categoriasEventos as $categoriaEvento) {
            $enfrentamientosPorCategoria[] = Enfrentamiento::whereHas('llave', function($query) use ($categoriaEvento) {
                $query->where('categoria_evento_id', $categoriaEvento->id);
            })->count();
        }
        
        // Equipos por categoría
        $equiposPorCategoria = [];
        foreach ($categoriasEventos as $categoriaEvento) {
            $equiposPorCategoria[] = Equipo::whereHas('inscripcionesEvento', function($query) use ($categoriaEvento) {
                $query->where('categoria_evento_id', $categoriaEvento->id);
            })->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Robots',
                    'data' => $robotsPorCategoria,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgb(54, 162, 235)',
                ],
                [
                    'label' => 'Enfrentamientos',
                    'data' => $enfrentamientosPorCategoria,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgb(255, 99, 132)',
                ],
                [
                    'label' => 'Equipos',
                    'data' => $equiposPorCategoria,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgb(75, 192, 192)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
} 