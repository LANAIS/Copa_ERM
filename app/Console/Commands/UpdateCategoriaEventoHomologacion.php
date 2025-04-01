<?php

namespace App\Console\Commands;

use App\Models\CategoriaEvento;
use Illuminate\Console\Command;

class UpdateCategoriaEventoHomologacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categoria-evento:update-homologacion {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de homologación de una o todas las categorías de eventos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        
        if ($id) {
            // Actualizar una categoría específica
            $categoriaEvento = CategoriaEvento::find($id);
            
            if (!$categoriaEvento) {
                $this->error("No se encontró la categoría de evento con ID {$id}");
                return 1;
            }
            
            $categoriaEvento->update(['estado_competencia' => CategoriaEvento::ESTADO_HOMOLOGACION]);
            $this->info("Categoría de evento con ID {$id} actualizada a estado de homologación");
        } else {
            // Mostrar una lista de categorías para seleccionar
            $categoriasEventos = CategoriaEvento::with(['evento', 'categoria'])
                ->whereHas('evento')
                ->whereHas('categoria')
                ->get();
            
            if ($categoriasEventos->isEmpty()) {
                $this->error('No hay categorías de eventos activas con inscripciones abiertas');
                return 1;
            }
            
            $this->info('Categorías de eventos disponibles:');
            
            $categoriasList = $categoriasEventos->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'evento' => $cat->evento ? $cat->evento->nombre : 'N/A',
                    'categoria' => $cat->categoria ? $cat->categoria->nombre : 'N/A',
                    'estado' => $cat->estado_competencia
                ];
            })->toArray();
            
            $this->table(['ID', 'Evento', 'Categoría', 'Estado actual'], $categoriasList);
            
            $selectedId = $this->ask('Ingrese el ID de la categoría que desea actualizar a estado de homologación (o "todos" para actualizar todas)');
            
            if ($selectedId === 'todos') {
                foreach ($categoriasEventos as $categoriaEvento) {
                    $categoriaEvento->update(['estado_competencia' => CategoriaEvento::ESTADO_HOMOLOGACION]);
                }
                $this->info('Todas las categorías de eventos listadas han sido actualizadas a estado de homologación');
            } else {
                $categoriaEvento = $categoriasEventos->firstWhere('id', $selectedId);
                
                if (!$categoriaEvento) {
                    $this->error("No se encontró la categoría de evento con ID {$selectedId} en la lista");
                    return 1;
                }
                
                $categoriaEvento->update(['estado_competencia' => CategoriaEvento::ESTADO_HOMOLOGACION]);
                $this->info("Categoría de evento con ID {$selectedId} actualizada a estado de homologación");
            }
        }
        
        return 0;
    }
}
