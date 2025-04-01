<?php

namespace App\Filament\Resources\InscripcionesEventoResource\Widgets;

use App\Models\InscripcionEvento;
use App\Models\Evento;
use App\Models\CategoriaEvento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InscripcionesEstadisticasWidget extends BaseWidget
{
    protected function getStats(): array
    {
        try {
            // Total de inscripciones
            $totalInscripciones = InscripcionEvento::count();
            
            // Inscripciones homologadas
            $inscripcionesHomologadas = InscripcionEvento::where('estado', 'homologada')
                ->orWhere('estado', 'participando')
                ->orWhere('estado', 'finalizada')
                ->count();
                
            // Porcentaje de homologación
            $porcentajeHomologacion = $totalInscripciones > 0 
                ? round(($inscripcionesHomologadas / $totalInscripciones) * 100, 2) 
                : 0;
                
            // Inscripciones por estado
            $porEstado = InscripcionEvento::selectRaw('estado, count(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado')
                ->toArray();
                
            // Eventos activos con inscripciones
            $eventosActivos = Evento::whereHas('inscripciones')
                ->where('estado', '!=', 'finalizado')
                ->count();
                
            // Categorías más populares
            $categoriasMasPopulares = CategoriaEvento::withCount('inscripciones')
                ->with('categoria')
                ->orderBy('inscripciones_count', 'desc')
                ->limit(3)
                ->get();
                
            $categoriasTexto = $categoriasMasPopulares->isEmpty() 
                ? 'No hay categorías con inscripciones'
                : $categoriasMasPopulares->map(function ($cat) {
                    $nombre = $cat->categoria->nombre ?? 'Sin categoría';
                    return $nombre . ' (' . $cat->inscripciones_count . ')';
                })->implode(', ');
            
            return [
                Stat::make('Total Inscripciones', $totalInscripciones)
                    ->description('Inscripciones registradas')
                    ->descriptionIcon('heroicon-m-clipboard-document-list')
                    ->color('primary'),
                    
                Stat::make('Inscripciones Homologadas', $inscripcionesHomologadas)
                    ->description($porcentajeHomologacion . '% del total')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('success'),
                    
                Stat::make('Eventos Activos', $eventosActivos)
                    ->description('Con inscripciones abiertas')
                    ->descriptionIcon('heroicon-m-calendar')
                    ->color('warning'),
                    
                Stat::make('Categorías Populares', $categoriasTexto)
                    ->description('Mayor número de inscripciones')
                    ->descriptionIcon('heroicon-m-trophy')
                    ->color('info'),
                    
                Stat::make('Estado Inscripciones', '')
                    ->description(
                        'Pendientes: ' . ($porEstado['pendiente'] ?? 0) . 
                        ' | Confirmadas: ' . ($porEstado['confirmada'] ?? 0) . 
                        ' | Homologadas: ' . ($porEstado['homologada'] ?? 0)
                    )
                    ->chart([
                        $porEstado['pendiente'] ?? 0,
                        $porEstado['confirmada'] ?? 0, 
                        $porEstado['homologada'] ?? 0,
                        $porEstado['participando'] ?? 0,
                        $porEstado['finalizada'] ?? 0,
                    ])
                    ->color('success'),
            ];
        } catch (\Exception $e) {
            // En caso de error, mostrar estadísticas básicas
            return [
                Stat::make('Información', 'Error al cargar estadísticas')
                    ->description('Intente refrescar la página')
                    ->color('danger'),
            ];
        }
    }
} 