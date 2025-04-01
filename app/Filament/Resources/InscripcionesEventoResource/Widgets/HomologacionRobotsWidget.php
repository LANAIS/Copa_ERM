<?php

namespace App\Filament\Resources\InscripcionesEventoResource\Widgets;

use App\Models\InscripcionEvento;
use App\Models\Robot;
use App\Models\Homologacion;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class HomologacionRobotsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    // Esta propiedad se llenará automáticamente por Filament
    public ?InscripcionEvento $record = null;
    
    protected function getTableHeading(): string
    {
        return 'Robots Inscritos';
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if (!$this->record || !$this->record->equipo) {
                    return Robot::whereNull('id'); // Query vacía
                }
                
                // Traer todos los robots del equipo directamente
                return Robot::where('equipo_id', $this->record->equipo_id)
                    ->with(['homologaciones' => function($query) {
                        $query->where('categoria_evento_id', $this->record->categoria_evento_id);
                    }]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del robot')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoría')
                    ->badge(),
                    
                Tables\Columns\IconColumn::make('homologado')
                    ->label('Homologado')
                    ->boolean()
                    ->state(function (Robot $record): bool {
                        // Verificar si tiene homologación aprobada para esta categoría
                        return $record->estaHomologado($this->record->categoria_evento_id);
                    }),
                    
                Tables\Columns\TextColumn::make('estado_homologacion')
                    ->label('Estado Homologación')
                    ->state(function (Robot $record): string {
                        $homologacion = $record->obtenerHomologacion($this->record->categoria_evento_id);
                        
                        if (!$homologacion) {
                            return 'Sin homologación';
                        }
                        
                        return match($homologacion->estado) {
                            Homologacion::ESTADO_PENDIENTE => 'Pendiente',
                            Homologacion::ESTADO_APROBADO => 'Aprobada',
                            Homologacion::ESTADO_RECHAZADO => 'Rechazada',
                            default => 'Desconocido'
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'Pendiente' => 'warning',
                        'Aprobada' => 'success',
                        'Rechazada' => 'danger',
                        default => 'gray'
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('homologar')
                    ->label('Homologar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Homologar robot')
                    ->modalDescription(fn (Robot $robot) => "¿Estás seguro de homologar el robot {$robot->nombre}?")
                    ->modalSubmitActionLabel('Sí, homologar')
                    ->visible(function (Robot $robot): bool {
                        $homologacion = $robot->obtenerHomologacion($this->record->categoria_evento_id);
                        
                        // Mostrar botón si no hay homologación o está pendiente/rechazada
                        return !$homologacion || !$homologacion->estaAprobada();
                    })
                    ->action(function (Robot $robot) {
                        // Buscar la homologación existente o crear una nueva
                        $homologacion = Homologacion::firstOrNew([
                            'robot_id' => $robot->id,
                            'categoria_evento_id' => $this->record->categoria_evento_id,
                        ]);
                        
                        // Actualizamos o creamos la homologación
                        $homologacion->fill([
                            'estado' => Homologacion::ESTADO_APROBADO,
                            'observaciones' => 'Homologado automáticamente desde panel administrativo',
                            'juez_id' => Auth::id(),
                        ]);
                        
                        $homologacion->save();
                        
                        // También actualizamos el registro en robots_participantes
                        $robotsParticipantes = $this->record->robots_participantes ?: [];
                        $actualizado = false;
                        
                        foreach ($robotsParticipantes as $key => $robotData) {
                            if ($robotData['id'] == $robot->id) {
                                $robotsParticipantes[$key]['homologado'] = true;
                                $actualizado = true;
                                break;
                            }
                        }
                        
                        // Si no existía en el array, lo agregamos
                        if (!$actualizado) {
                            $robotsParticipantes[] = [
                                'id' => $robot->id,
                                'participante' => true,
                                'homologado' => true
                            ];
                        }
                        
                        $this->record->update([
                            'robots_participantes' => $robotsParticipantes
                        ]);
                        
                        // Si todos los robots están homologados y la inscripción está confirmada,
                        // actualizar el estado a homologada
                        if ($this->record->todosRobotsHomologados() && $this->record->estado === 'confirmada') {
                            $this->record->marcarHomologada();
                        }
                        
                        Notification::make()
                            ->title("Robot {$robot->nombre} homologado correctamente")
                            ->success()
                            ->send();
                            
                        $this->dispatch('refreshPage');
                    }),
                
                Tables\Actions\Action::make('ver_detalles')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(function (Robot $robot): bool {
                        $homologacion = $robot->obtenerHomologacion($this->record->categoria_evento_id);
                        return $homologacion !== null;
                    })
                    ->action(function (Robot $robot) {
                        $homologacion = $robot->obtenerHomologacion($this->record->categoria_evento_id);
                        
                        if ($homologacion) {
                            $estado = match($homologacion->estado) {
                                Homologacion::ESTADO_PENDIENTE => 'Pendiente',
                                Homologacion::ESTADO_APROBADO => 'Aprobada',
                                Homologacion::ESTADO_RECHAZADO => 'Rechazada',
                                default => 'Desconocido'
                            };
                            
                            Notification::make()
                                ->title("Detalles de homologación")
                                ->body("
                                    Robot: {$robot->nombre}
                                    Estado: {$estado}
                                    Observaciones: {$homologacion->observaciones}
                                    Fecha: {$homologacion->created_at->format('d/m/Y H:i')}
                                ")
                                ->persistent()
                                ->info()
                                ->send();
                        }
                    }),
            ])
            ->emptyStateHeading('No hay robots para mostrar')
            ->emptyStateDescription('No hay robots asociados a esta inscripción o el equipo no tiene robots.')
            ->emptyStateIcon('heroicon-o-cpu-chip');
    }
} 