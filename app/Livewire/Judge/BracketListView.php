<?php

namespace App\Livewire\Judge;

use App\Models\Llave;
use App\Models\CategoriaEvento;
use App\Models\Enfrentamiento;
use App\Models\Evento;
use App\Models\Fecha;
use App\Models\Categoria;
use Livewire\Component;
use Filament\Notifications\Notification;

class BracketListView extends Component
{
    // Variables públicas
    public ?int $selectedEventoId = null;
    public ?int $selectedFechaId = null;
    public ?int $selectedCategoriaId = null;
    public ?int $selectedLlaveId = null;
    public ?Llave $selectedLlave = null;
    public array $enfrentamientos = [];
    public int $maxRonda = 1;
    public bool $showFilters = true;
    public array $eventos = [];
    public array $fechas = [];
    public array $categorias = [];
    public array $llaves = [];
    
    public function mount(): void
    {
        // Inicializar arrays vacíos
        $this->eventos = [];
        $this->fechas = [];
        $this->categorias = [];
        $this->llaves = [];
        $this->enfrentamientos = [];
    }
    
    public function render()
    {
        return view('livewire.judge.bracket-list-view');
    }
    
    public function loadEventos(): void
    {
        try {
            $this->eventos = Evento::orderBy('fecha_inicio', 'desc')
                ->take(20)
                ->get()
                ->map(function ($evento) {
                    return [
                        'id' => $evento->id,
                        'nombre' => $evento->nombre,
                        'fecha' => $evento->fecha_inicio->format('d/m/Y'),
                        'estado' => $evento->estado,
                    ];
                })
                ->toArray();
                
            Notification::make()
                ->title('Datos cargados correctamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cargar los datos')
                ->body('Detalles: ' . $e->getMessage())
                ->danger()
                ->send();
            
            $this->eventos = [];
        }
    }
    
    public function updatedSelectedEventoId(): void
    {
        $this->reset(['selectedFechaId', 'selectedCategoriaId', 'selectedLlaveId', 'selectedLlave', 'enfrentamientos']);
        $this->loadFechas();
    }
    
    public function loadFechas(): void
    {
        if (!$this->selectedEventoId) {
            $this->fechas = [];
            return;
        }
        
        try {
            $this->fechas = Fecha::where('evento_id', $this->selectedEventoId)
                ->orderBy('fecha')
                ->limit(50)
                ->get()
                ->map(function ($fecha) {
                    return [
                        'id' => $fecha->id,
                        'nombre' => $fecha->nombre,
                        'fecha' => $fecha->fecha->format('d/m/Y'),
                        'estado' => $fecha->estado,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cargar las fechas')
                ->body('Detalles: ' . $e->getMessage())
                ->danger()
                ->send();
            
            $this->fechas = [];
        }
    }
    
    public function updatedSelectedFechaId(): void
    {
        $this->reset(['selectedCategoriaId', 'selectedLlaveId', 'selectedLlave', 'enfrentamientos']);
        $this->loadCategorias();
    }
    
    public function loadCategorias(): void
    {
        if (!$this->selectedFechaId) {
            $this->categorias = [];
            return;
        }
        
        try {
            $this->categorias = CategoriaEvento::whereHas('llaves', function($query) {
                    $query->where('fecha_id', $this->selectedFechaId);
                })
                ->with(['categoria', 'evento'])
                ->limit(50)
                ->get()
                ->map(function ($categoriaEvento) {
                    return [
                        'id' => $categoriaEvento->id,
                        'nombre' => $categoriaEvento->categoria->nombre ?? 'Sin nombre',
                        'evento' => $categoriaEvento->evento->nombre ?? 'Sin evento',
                        'participantes' => $categoriaEvento->inscripciones_count ?? 0,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cargar las categorías')
                ->body('Detalles: ' . $e->getMessage())
                ->danger()
                ->send();
            
            $this->categorias = [];
        }
    }
    
    public function updatedSelectedCategoriaId(): void
    {
        $this->reset(['selectedLlaveId', 'selectedLlave', 'enfrentamientos']);
        $this->loadLlaves();
        
        if (count($this->llaves) === 1) {
            $this->selectedLlaveId = $this->llaves[0]['id'];
            $this->cargarLlave();
        }
    }
    
    public function loadLlaves(): void
    {
        if (!$this->selectedCategoriaId) {
            $this->llaves = [];
            return;
        }
        
        try {
            $this->llaves = Llave::where('categoria_evento_id', $this->selectedCategoriaId)
                ->where('fecha_id', $this->selectedFechaId)
                ->with(['categoriaEvento.categoria'])
                ->limit(20)
                ->get()
                ->map(function ($llave) {
                    return [
                        'id' => $llave->id,
                        'categoria' => $llave->categoriaEvento->categoria->nombre ?? 'Sin categoría',
                        'tipo_fixture' => $this->formatTipoFixture($llave->tipo_fixture),
                        'estado' => $llave->estado_torneo,
                        'equipos' => count($llave->estructura['equipos'] ?? []),
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cargar las llaves')
                ->body('Detalles: ' . $e->getMessage())
                ->danger()
                ->send();
            
            $this->llaves = [];
        }
    }
    
    public function formatTipoFixture(string $tipo): string
    {
        $tipos = [
            'eliminacion_directa' => 'Eliminación Directa',
            'eliminacion_doble' => 'Eliminación Doble',
            'todos_contra_todos' => 'Todos contra Todos',
            'suizo' => 'Sistema Suizo',
            'grupos' => 'Fase de Grupos',
            'fase_grupos_eliminacion' => 'Grupos + Eliminación',
        ];
        
        return $tipos[$tipo] ?? $tipo;
    }
    
    public function updatedSelectedLlaveId(): void
    {
        $this->cargarLlave();
    }
    
    public function cargarLlave(): void
    {
        if (!$this->selectedLlaveId) {
            $this->selectedLlave = null;
            $this->enfrentamientos = [];
            return;
        }
        
        try {
            $this->selectedLlave = Llave::with([
                    'categoriaEvento.categoria',
                    'categoriaEvento.evento',
                    'fecha'
                ])->find($this->selectedLlaveId);
            
            if (!$this->selectedLlave) {
                Notification::make()
                    ->title('Llave no encontrada')
                    ->danger()
                    ->send();
                return;
            }
            
            $enfrentamientosDB = Enfrentamiento::where('llave_id', $this->selectedLlaveId)
                ->with(['equipo1', 'equipo2', 'ganador'])
                ->orderBy('ronda')
                ->orderBy('posicion')
                ->limit(100)
                ->get();
                
            $this->enfrentamientos = $enfrentamientosDB->toArray();
            $this->maxRonda = $enfrentamientosDB->max('ronda') ?? 1;
            
            Notification::make()
                ->title('Bracket cargado correctamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cargar el bracket')
                ->body('Detalles: ' . $e->getMessage())
                ->danger()
                ->send();
            
            $this->selectedLlave = null;
            $this->enfrentamientos = [];
            $this->maxRonda = 1;
        }
    }
    
    public function getEnfrentamientosPorRonda(int $ronda): array
    {
        return array_filter($this->enfrentamientos, function ($e) use ($ronda) {
            return $e['ronda'] === $ronda;
        });
    }
    
    public function getNombreLlave(): string
    {
        if (!$this->selectedLlave) {
            return 'Administración de Torneos';
        }
        
        $evento = $this->selectedLlave->categoriaEvento->evento->nombre ?? '';
        $fecha = $this->selectedLlave->fecha->nombre ?? '';
        $categoria = $this->selectedLlave->categoriaEvento->categoria->nombre ?? 'Sin categoría';
        
        return "{$evento} - {$fecha} - {$categoria}";
    }
    
    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }
} 