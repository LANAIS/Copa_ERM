<?php

namespace App\Filament\Judge\Pages;

use App\Models\Llave;
use App\Models\CategoriaEvento;
use App\Models\Enfrentamiento;
use App\Models\Evento;
use App\Models\Fecha;
use App\Models\Categoria;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class BracketsCompetencia extends Page implements HasForms
{
    use InteractsWithForms;
    
    // Propiedades de navegación
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Visualizar Brackets';
    protected static ?string $navigationGroup = 'Competencia';
    protected static ?int $navigationSort = 2;
    
    // Propiedades de la página
    protected static string $view = 'filament.judge.pages.brackets-competencia';
    protected static ?string $slug = 'llaves-competencia';
    protected static ?string $title = 'Brackets de Competencia';
    
    // Variables públicas para la vista
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
    public array $resultados = [];
    
    public function mount(): void
    {
        // Solo inicializar arrays vacíos
        $this->eventos = [];
        $this->fechas = [];
        $this->categorias = [];
        $this->llaves = [];
        $this->enfrentamientos = [];
        $this->resultados = [];
        
        // Cargar eventos inicialmente
        $this->loadEventos();
    }
    
    protected function getHeaderActions(): array
    {
        $actions = [];
        
        if ($this->selectedLlaveId) {
            // Acciones para administrar la llave
            $actions[] = Action::make('administrarLlave')
                ->label('Administrar Llave')
                ->icon('heroicon-o-cog')
                ->url(route('filament.judge.pages.bracket-admin'))
                ->color('primary')
                ->openUrlInNewTab();

            // Acciones para ver enfrentamientos
            $actions[] = Action::make('verEnfrentamientos')
                ->label('Ver Enfrentamientos')
                ->icon('heroicon-o-trophy')
                ->url('/judge/resources/enfrentamientos')
                ->color('warning');

            // Acciones para ver vista pública
            $actions[] = Action::make('verPublico')
                ->label('Ver Bracket Público')
                ->icon('heroicon-o-eye')
                ->url(route('judge.brackets.public', $this->selectedLlaveId))
                ->color('success')
                ->openUrlInNewTab();

            // Acciones para gestionar la llave
            $actions[] = Action::make('gestionarLlave')
                ->label('Gestionar Llave')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('info')
                ->action(function () {
                    $this->dispatch('open-modal', id: 'gestionar-llave');
                });
        }
        
        return $actions;
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Judge\Widgets\EstadisticasLlaveWidget::class,
        ];
    }

    public function getViewData(): array
    {
        return [
            'showFilters' => $this->showFilters,
            'selectedLlave' => $this->selectedLlave,
            'enfrentamientos' => $this->enfrentamientos,
            'maxRonda' => $this->maxRonda,
        ];
    }

    public function getActions(): array
    {
        $actions = [];

        if ($this->selectedLlaveId) {
            $actions[] = Action::make('crearEnfrentamiento')
                ->label('Crear Enfrentamiento')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->action(function () {
                    $this->dispatch('open-modal', id: 'crear-enfrentamiento');
                });

            $actions[] = Action::make('cargarResultados')
                ->label('Cargar Resultados')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->action(function () {
                    $this->dispatch('open-modal', id: 'cargar-resultados');
                });

            $actions[] = Action::make('reiniciarLlave')
                ->label('Reiniciar Llave')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $this->reiniciarLlave();
                });
        }

        return $actions;
    }

    protected function reiniciarLlave(): void
    {
        try {
            $llave = Llave::find($this->selectedLlaveId);
            if (!$llave) {
                throw new \Exception('Llave no encontrada');
            }

            // Eliminar todos los enfrentamientos
            $llave->enfrentamientos()->delete();

            // Reiniciar estructura
            $llave->update([
                'estado_torneo' => 'pendiente',
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'estructura' => [
                    'ronda_actual' => 1,
                    'total_rondas' => $llave->estructura['total_rondas'] ?? 1,
                    'equipos' => $llave->estructura['equipos'] ?? [],
                ]
            ]);

            Notification::make()
                ->title('Llave reiniciada correctamente')
                ->success()
                ->send();

            $this->cargarLlave();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al reiniciar la llave')
                ->body('Detalles: ' . $e->getMessage())
                ->danger()
                ->send();
        }
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
                ->orderBy('fecha_inicio')
                ->limit(50)
                ->get()
                ->map(function ($fecha) {
                    return [
                        'id' => $fecha->id,
                        'nombre' => $fecha->nombre,
                        'fecha' => $fecha->fecha_inicio->format('d/m/Y'),
                        'estado' => $fecha->activo ? 'activa' : 'inactiva',
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
            $this->categorias = CategoriaEvento::where('fecha_evento_id', $this->selectedFechaId)
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
            // Obtener la categoría evento seleccionada
            $categoriaEvento = CategoriaEvento::where('id', $this->selectedCategoriaId)
                ->where('fecha_evento_id', $this->selectedFechaId)
                ->first();
                
            if (!$categoriaEvento) {
                $this->llaves = [];
                return;
            }
            
            $this->llaves = Llave::where('categoria_evento_id', $this->selectedCategoriaId)
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
            $this->resultados = [];
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

            // Inicializar resultados con los valores actuales
            $this->resultados = $enfrentamientosDB->mapWithKeys(function ($enfrentamiento) {
                return [$enfrentamiento->id => [
                    'puntaje_equipo1' => $enfrentamiento->puntaje_equipo1,
                    'puntaje_equipo2' => $enfrentamiento->puntaje_equipo2,
                    'ganador_id' => $enfrentamiento->ganador_id,
                    'observaciones' => $enfrentamiento->observaciones,
                ]];
            })->toArray();
            
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
            $this->resultados = [];
            $this->maxRonda = 1;
        }
    }
    
    public function getEnfrentamientosPorRonda(int $ronda): array
    {
        return array_filter($this->enfrentamientos, function ($e) use ($ronda) {
            return $e['ronda'] === $ronda;
        });
    }
    
    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    public function guardarResultado($enfrentamientoId)
    {
        try {
            $enfrentamiento = Enfrentamiento::findOrFail($enfrentamientoId);
            $resultado = $this->resultados[$enfrentamientoId] ?? null;

            if (!$resultado) {
                Notification::make()
                    ->title('Error')
                    ->body('No se encontraron datos para guardar')
                    ->danger()
                    ->send();
                return;
            }

            // Validar que el ganador coincida con uno de los equipos
            if (!in_array($resultado['ganador_id'], [$enfrentamiento->equipo1_id, $enfrentamiento->equipo2_id])) {
                Notification::make()
                    ->title('Error')
                    ->body('El ganador debe ser uno de los equipos participantes')
                    ->danger()
                    ->send();
                return;
            }

            // Actualizar el enfrentamiento
            $enfrentamiento->update([
                'puntaje_equipo1' => $resultado['puntaje_equipo1'],
                'puntaje_equipo2' => $resultado['puntaje_equipo2'],
                'ganador_id' => $resultado['ganador_id'],
                'observaciones' => $resultado['observaciones'] ?? null,
            ]);

            // Actualizar la estructura de la llave
            $this->actualizarEstructuraLlave($enfrentamiento->llave_id);

            // Recargar la llave para actualizar los datos
            $this->cargarLlave();

            Notification::make()
                ->title('Éxito')
                ->body('Resultado guardado correctamente')
                ->success()
                ->send();

            $this->dispatch('close-modal', ['id' => 'cargar-resultado-' . $enfrentamientoId]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error al guardar el resultado: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function actualizarResultado($enfrentamientoId)
    {
        try {
            $enfrentamiento = Enfrentamiento::findOrFail($enfrentamientoId);
            $resultado = $this->resultados[$enfrentamientoId] ?? null;

            if (!$resultado) {
                Notification::make()
                    ->title('Error')
                    ->body('No se encontraron datos para actualizar')
                    ->danger()
                    ->send();
                return;
            }

            // Validar que el ganador coincida con uno de los equipos
            if (!in_array($resultado['ganador_id'], [$enfrentamiento->equipo1_id, $enfrentamiento->equipo2_id])) {
                Notification::make()
                    ->title('Error')
                    ->body('El ganador debe ser uno de los equipos participantes')
                    ->danger()
                    ->send();
                return;
            }

            // Actualizar el enfrentamiento
            $enfrentamiento->update([
                'puntaje_equipo1' => $resultado['puntaje_equipo1'],
                'puntaje_equipo2' => $resultado['puntaje_equipo2'],
                'ganador_id' => $resultado['ganador_id'],
                'observaciones' => $resultado['observaciones'] ?? null,
            ]);

            // Actualizar la estructura de la llave
            $this->actualizarEstructuraLlave($enfrentamiento->llave_id);

            // Recargar la llave para actualizar los datos
            $this->cargarLlave();

            Notification::make()
                ->title('Éxito')
                ->body('Resultado actualizado correctamente')
                ->success()
                ->send();

            $this->dispatch('close-modal', ['id' => 'editar-resultado-' . $enfrentamientoId]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error al actualizar el resultado: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function actualizarEstructuraLlave($llaveId)
    {
        $llave = Llave::findOrFail($llaveId);
        $enfrentamientos = $llave->enfrentamientos()->orderBy('ronda')->orderBy('posicion')->get();
        
        $estructura = [
            'equipos' => [],
            'enfrentamientos' => [],
            'rondas' => [],
        ];

        // Obtener todos los equipos únicos
        $equipos = collect();
        foreach ($enfrentamientos as $enfrentamiento) {
            if ($enfrentamiento->equipo1) {
                $equipos->push([
                    'id' => $enfrentamiento->equipo1->id,
                    'nombre' => $enfrentamiento->equipo1->nombre,
                ]);
            }
            if ($enfrentamiento->equipo2) {
                $equipos->push([
                    'id' => $enfrentamiento->equipo2->id,
                    'nombre' => $enfrentamiento->equipo2->nombre,
                ]);
            }
        }
        $estructura['equipos'] = $equipos->unique('id')->values()->all();

        // Organizar enfrentamientos por rondas
        foreach ($enfrentamientos as $enfrentamiento) {
            $ronda = $enfrentamiento->ronda;
            if (!isset($estructura['rondas'][$ronda])) {
                $estructura['rondas'][$ronda] = [];
            }

            $estructura['rondas'][$ronda][] = [
                'id' => $enfrentamiento->id,
                'posicion' => $enfrentamiento->posicion,
                'equipo1' => $enfrentamiento->equipo1 ? [
                    'id' => $enfrentamiento->equipo1->id,
                    'nombre' => $enfrentamiento->equipo1->nombre,
                ] : null,
                'equipo2' => $enfrentamiento->equipo2 ? [
                    'id' => $enfrentamiento->equipo2->id,
                    'nombre' => $enfrentamiento->equipo2->nombre,
                ] : null,
                'puntaje_equipo1' => $enfrentamiento->puntaje_equipo1,
                'puntaje_equipo2' => $enfrentamiento->puntaje_equipo2,
                'ganador_id' => $enfrentamiento->ganador_id,
                'observaciones' => $enfrentamiento->observaciones,
            ];
        }

        // Ordenar enfrentamientos por posición en cada ronda
        foreach ($estructura['rondas'] as &$ronda) {
            usort($ronda, function($a, $b) {
                return $a['posicion'] <=> $b['posicion'];
            });
        }

        $estructura['enfrentamientos'] = $enfrentamientos->map(function($enfrentamiento) {
            return [
                'id' => $enfrentamiento->id,
                'ronda' => $enfrentamiento->ronda,
                'posicion' => $enfrentamiento->posicion,
                'equipo1_id' => $enfrentamiento->equipo1_id,
                'equipo2_id' => $enfrentamiento->equipo2_id,
                'puntaje_equipo1' => $enfrentamiento->puntaje_equipo1,
                'puntaje_equipo2' => $enfrentamiento->puntaje_equipo2,
                'ganador_id' => $enfrentamiento->ganador_id,
                'observaciones' => $enfrentamiento->observaciones,
                'equipo1' => $enfrentamiento->equipo1 ? [
                    'id' => $enfrentamiento->equipo1->id,
                    'nombre' => $enfrentamiento->equipo1->nombre,
                ] : null,
                'equipo2' => $enfrentamiento->equipo2 ? [
                    'id' => $enfrentamiento->equipo2->id,
                    'nombre' => $enfrentamiento->equipo2->nombre,
                ] : null,
            ];
        })->toArray();

        $llave->update(['estructura' => $estructura]);
    }
} 