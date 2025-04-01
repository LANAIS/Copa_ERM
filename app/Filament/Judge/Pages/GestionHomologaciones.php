<?php

namespace App\Filament\Judge\Pages;

use Filament\Pages\Page;
use App\Models\Homologacion;
use App\Models\CategoriaEvento;
use App\Models\Evento;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Filament\Support\Enums\IconPosition;
use Filament\Navigation\NavigationItem;

class GestionHomologaciones extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Gestión de Homologaciones';
    protected static ?string $title = 'Homologaciones por Competencia';
    protected static ?string $navigationGroup = 'Competencia';
    protected static ?int $navigationSort = 1;
    
    protected static string $view = 'filament.judge.pages.gestion-homologaciones';
    
    public ?array $data = [];
    
    #[Url]
    public ?int $eventoId = null;
    
    #[Url]
    public ?int $fechaId = null;
    
    #[Url]
    public ?int $categoriaId = null;
    
    public array $observaciones = [];
    public array $robotsSeleccionados = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('eventoId')
                            ->label('Evento')
                            ->options(Evento::activos()->pluck('nombre', 'id'))
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->eventoId = $state;
                                $this->fechaId = null;
                                $this->categoriaId = null;
                            }),
                            
                        Forms\Components\Select::make('fechaId')
                            ->label('Fecha')
                            ->options(function (callable $get) {
                                if (!$get('eventoId')) {
                                    return [];
                                }
                                
                                return \App\Models\FechaEvento::where('evento_id', $get('eventoId'))
                                    ->activos()
                                    ->ordenado()
                                    ->get()
                                    ->mapWithKeys(function ($fecha) {
                                        return [
                                            $fecha->id => $fecha->nombre . ' - ' . $fecha->lugar . ' (' . $fecha->fecha_inicio->format('d/m/Y') . ')'
                                        ];
                                    });
                            })
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->fechaId = $state;
                                $this->categoriaId = null;
                            }),
                            
                        Forms\Components\Select::make('categoriaId')
                            ->label('Categoría')
                            ->options(function (callable $get) {
                                if (!$get('eventoId') || !$get('fechaId')) {
                                    return [];
                                }
                                
                                return CategoriaEvento::query()
                                    ->where('evento_id', $get('eventoId'))
                                    ->where('fecha_evento_id', $get('fechaId'))
                                    ->join('categorias', 'categoria_eventos.categoria_id', '=', 'categorias.id')
                                    ->select('categoria_eventos.id', 'categorias.nombre')
                                    ->pluck('nombre', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->categoriaId = $state;
                            }),
                    ]),
            ]);
    }
    
    public function getHomologaciones()
    {
        if (!$this->categoriaId || !$this->fechaId) {
            return collect();
        }
        
        // Obtener las inscripciones confirmadas, pagadas o ya homologadas para esta categoría
        $inscripciones = \App\Models\InscripcionEvento::where('categoria_evento_id', $this->categoriaId)
            ->whereIn('estado', [
                \App\Models\InscripcionEvento::ESTADO_CONFIRMADA,
                \App\Models\InscripcionEvento::ESTADO_PAGADA,
                \App\Models\InscripcionEvento::ESTADO_HOMOLOGADA
            ])
            ->with(['robot', 'equipo', 'categoriaEvento.categoria', 'categoriaEvento.evento'])
            ->get();
            
        $resultados = collect();
        
        // Para cada inscripción, vamos a obtener el robot asociado y su estado de homologación
        foreach ($inscripciones as $inscripcion) {
            // Si la inscripción tiene un robot directamente asociado
            if ($inscripcion->robot_id) {
                $robot = $inscripcion->robot;
                
                // Obtener la homologación para este robot
                $homologacion = \App\Models\Homologacion::where([
                    'robot_id' => $robot->id,
                    'categoria_evento_id' => $this->categoriaId
                ])->first();
                
                // Si no existe la homologación, la creamos
                if (!$homologacion) {
                    $homologacion = \App\Models\Homologacion::create([
                        'robot_id' => $robot->id,
                        'categoria_evento_id' => $this->categoriaId,
                        'estado' => \App\Models\Homologacion::ESTADO_PENDIENTE,
                        'peso' => 0,
                        'alto' => 0,
                        'ancho' => 0,
                        'largo' => 0
                    ]);
                }
                
                // Cargar las relaciones necesarias para la vista
                $homologacion->loadMissing('evaluador');
                $homologacion->robot = $robot;
                $homologacion->categoriaEvento = $inscripcion->categoriaEvento;
                $homologacion->equipoInscripcion = (object)[
                    'equipo' => $inscripcion->equipo
                ];
                
                $resultados->push($homologacion);
            }
            
            // Si la inscripción tiene robots participantes en un array
            if (!empty($inscripcion->robots_participantes)) {
                $robotsIds = collect($inscripcion->robots_participantes)
                    ->where('participante', true)
                    ->pluck('id')
                    ->toArray();
                    
                if (!empty($robotsIds)) {
                    $robots = \App\Models\Robot::whereIn('id', $robotsIds)->get();
                    
                    foreach ($robots as $robot) {
                        // Obtener la homologación para este robot
                        $homologacion = \App\Models\Homologacion::where([
                            'robot_id' => $robot->id,
                            'categoria_evento_id' => $this->categoriaId
                        ])->first();
                        
                        // Si no existe la homologación, la creamos
                        if (!$homologacion) {
                            $homologacion = \App\Models\Homologacion::create([
                                'robot_id' => $robot->id,
                                'categoria_evento_id' => $this->categoriaId,
                                'estado' => \App\Models\Homologacion::ESTADO_PENDIENTE,
                                'peso' => 0,
                                'alto' => 0,
                                'ancho' => 0,
                                'largo' => 0
                            ]);
                        }
                        
                        // Cargar las relaciones necesarias para la vista
                        $homologacion->loadMissing('evaluador');
                        $homologacion->robot = $robot;
                        $homologacion->categoriaEvento = $inscripcion->categoriaEvento;
                        $homologacion->equipoInscripcion = (object)[
                            'equipo' => $inscripcion->equipo
                        ];
                        
                        $resultados->push($homologacion);
                    }
                }
            }
        }
        
        // Eliminar duplicados y ordenar por nombre del robot
        return $resultados->unique('id')->sortBy('robot.nombre');
    }
    
    public function aprobarHomologacion($homologacionId)
    {
        $homologacion = Homologacion::findOrFail($homologacionId);
        
        // Actualizar la homologación
        $homologacion->update([
            'estado' => Homologacion::ESTADO_APROBADO,
            'juez_id' => Auth::id(),
        ]);
        
        // Actualizar las inscripciones con este robot
        $inscripciones = \App\Models\InscripcionEvento::where('categoria_evento_id', $homologacion->categoria_evento_id)
            ->whereIn('estado', [
                \App\Models\InscripcionEvento::ESTADO_CONFIRMADA,
                \App\Models\InscripcionEvento::ESTADO_PAGADA,
                \App\Models\InscripcionEvento::ESTADO_HOMOLOGADA
            ])
            ->get();
            
        foreach ($inscripciones as $inscripcion) {
            // Si el robot está directamente asociado a la inscripción
            if ($inscripcion->robot_id == $homologacion->robot_id) {
                $inscripcion->marcarHomologada();
            }
            
            // Si el robot está en el array de robots_participantes
            if (!empty($inscripcion->robots_participantes)) {
                $robotsParticipantes = $inscripcion->robots_participantes;
                $actualizado = false;
                
                foreach ($robotsParticipantes as $key => $robotData) {
                    if ($robotData['id'] == $homologacion->robot_id && $robotData['participante']) {
                        $robotsParticipantes[$key]['homologado'] = true;
                        $actualizado = true;
                    }
                }
                
                if ($actualizado) {
                    $inscripcion->robots_participantes = $robotsParticipantes;
                    $inscripcion->save();
                    
                    // Si todos los robots están homologados, marcar la inscripción como homologada
                    if ($inscripcion->todosRobotsHomologados()) {
                        $inscripcion->marcarHomologada();
                    }
                }
            }
        }
        
        Notification::make()
            ->title('Homologación aprobada con éxito')
            ->success()
            ->send();
    }
    
    public function rechazarHomologacion($homologacionId, $observaciones)
    {
        $homologacion = Homologacion::findOrFail($homologacionId);
        
        // Actualizar la homologación
        $homologacion->update([
            'estado' => Homologacion::ESTADO_RECHAZADO,
            'juez_id' => Auth::id(),
            'observaciones' => $observaciones,
        ]);
        
        // Actualizar las inscripciones con este robot para marcar como no homologado
        $inscripciones = \App\Models\InscripcionEvento::where('categoria_evento_id', $homologacion->categoria_evento_id)
            ->whereIn('estado', [
                \App\Models\InscripcionEvento::ESTADO_CONFIRMADA,
                \App\Models\InscripcionEvento::ESTADO_PAGADA,
                \App\Models\InscripcionEvento::ESTADO_HOMOLOGADA
            ])
            ->get();
            
        foreach ($inscripciones as $inscripcion) {
            // Si el robot está en el array de robots_participantes
            if (!empty($inscripcion->robots_participantes)) {
                $robotsParticipantes = $inscripcion->robots_participantes;
                $actualizado = false;
                
                foreach ($robotsParticipantes as $key => $robotData) {
                    if ($robotData['id'] == $homologacion->robot_id && $robotData['participante']) {
                        $robotsParticipantes[$key]['homologado'] = false;
                        $actualizado = true;
                    }
                }
                
                if ($actualizado) {
                    $inscripcion->robots_participantes = $robotsParticipantes;
                    $inscripcion->save();
                    
                    // Si estaba en estado homologada, volver a PAGADA o CONFIRMADA
                    if ($inscripcion->estado === \App\Models\InscripcionEvento::ESTADO_HOMOLOGADA) {
                        if ($inscripcion->monto_pagado > 0) {
                            $inscripcion->estado = \App\Models\InscripcionEvento::ESTADO_PAGADA;
                        } else {
                            $inscripcion->estado = \App\Models\InscripcionEvento::ESTADO_CONFIRMADA;
                        }
                        $inscripcion->save();
                    }
                }
            }
        }
        
        Notification::make()
            ->title('Homologación rechazada')
            ->danger()
            ->send();
    }
    
    public function aprobarTodosPendientes()
    {
        $homologaciones = Homologacion::where('categoria_evento_id', $this->categoriaId)
            ->where('estado', Homologacion::ESTADO_PENDIENTE)
            ->get();
            
        if ($homologaciones->isEmpty()) {
            Notification::make()
                ->title('No hay homologaciones pendientes para aprobar')
                ->warning()
                ->send();
                
            return;
        }
        
        foreach ($homologaciones as $homologacion) {
            // Actualizar la homologación
            $homologacion->update([
                'estado' => Homologacion::ESTADO_APROBADO,
                'juez_id' => Auth::id(),
            ]);
            
            // Actualizar las inscripciones con este robot
            $inscripciones = \App\Models\InscripcionEvento::where('categoria_evento_id', $this->categoriaId)
                ->whereIn('estado', [
                    \App\Models\InscripcionEvento::ESTADO_CONFIRMADA,
                    \App\Models\InscripcionEvento::ESTADO_PAGADA,
                    \App\Models\InscripcionEvento::ESTADO_HOMOLOGADA
                ])
                ->get();
                
            foreach ($inscripciones as $inscripcion) {
                // Si el robot está directamente asociado a la inscripción
                if ($inscripcion->robot_id == $homologacion->robot_id) {
                    $inscripcion->marcarHomologada();
                }
                
                // Si el robot está en el array de robots_participantes
                if (!empty($inscripcion->robots_participantes)) {
                    $robotsParticipantes = $inscripcion->robots_participantes;
                    $actualizado = false;
                    
                    foreach ($robotsParticipantes as $key => $robotData) {
                        if ($robotData['id'] == $homologacion->robot_id && $robotData['participante']) {
                            $robotsParticipantes[$key]['homologado'] = true;
                            $actualizado = true;
                        }
                    }
                    
                    if ($actualizado) {
                        $inscripcion->robots_participantes = $robotsParticipantes;
                        $inscripcion->save();
                        
                        // Si todos los robots están homologados, marcar la inscripción como homologada
                        if ($inscripcion->todosRobotsHomologados()) {
                            $inscripcion->marcarHomologada();
                        }
                    }
                }
            }
        }
        
        $count = $homologaciones->count();
        
        Notification::make()
            ->title($count . ' ' . ($count == 1 ? 'homologación aprobada' : 'homologaciones aprobadas') . ' con éxito')
            ->success()
            ->send();
    }
    
    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->icon(static::getNavigationIcon())
                ->group(static::getNavigationGroup())
                ->sort(static::getNavigationSort())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.judge.pages.gestion-homologaciones'))
                ->url(fn (): string => static::getUrl()),
        ];
    }
    
    public static function getSlug(): string
    {
        return 'homologaciones-por-competencia';
    }
} 