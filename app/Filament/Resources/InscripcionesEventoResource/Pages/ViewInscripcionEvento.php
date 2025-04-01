<?php

namespace App\Filament\Resources\InscripcionesEventoResource\Pages;

use App\Filament\Resources\InscripcionesEventoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewInscripcionEvento extends ViewRecord
{
    protected static string $resource = InscripcionesEventoResource::class;
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Aseguramos que el estado sea cargado correctamente
        $data['estado'] = $this->record->estado;
        
        // Si hay notas, las cargamos
        $data['notas_admin'] = $this->record->notas_admin;
        
        return $data;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('homologar_todos_robots')
                ->label('Homologar todos los robots')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Homologar todos los robots')
                ->modalDescription('¿Estás seguro de que deseas marcar todos los robots como homologados?')
                ->modalSubmitActionLabel('Sí, homologar todos')
                ->visible(function () {
                    // Solo mostrar si hay robots no homologados
                    if (empty($this->record->robots_participantes)) {
                        return false;
                    }
                    
                    // Verificar si hay robots no homologados
                    $robotsNoHomologados = collect($this->record->robots_participantes)
                        ->where('participante', true)
                        ->filter(function ($robot) {
                            $robotObj = \App\Models\Robot::find($robot['id']);
                            if (!$robotObj) return false;
                            return !$robotObj->estaHomologado($this->record->categoria_evento_id);
                        })
                        ->isNotEmpty();
                    
                    return $robotsNoHomologados;
                })
                ->action(function () {
                    $robotsParticipantes = $this->record->robots_participantes ?: [];
                    $contador = 0;
                    
                    foreach ($robotsParticipantes as $key => $robot) {
                        if (!isset($robot['participante']) || !$robot['participante']) {
                            continue;
                        }
                        
                        $robotObj = \App\Models\Robot::find($robot['id']);
                        if (!$robotObj) {
                            continue;
                        }
                        
                        // Verificar si ya está homologado
                        if ($robotObj->estaHomologado($this->record->categoria_evento_id)) {
                            continue;
                        }
                        
                        // Buscar la homologación existente o crear una nueva
                        $homologacion = \App\Models\Homologacion::firstOrNew([
                            'robot_id' => $robot['id'],
                            'categoria_evento_id' => $this->record->categoria_evento_id,
                        ]);
                        
                        // Actualizamos o creamos la homologación
                        $homologacion->fill([
                            'estado' => \App\Models\Homologacion::ESTADO_APROBADO,
                            'observaciones' => 'Homologado automáticamente desde panel administrativo',
                            'juez_id' => Auth::id(),
                        ]);
                        
                        $homologacion->save();
                        
                        // También actualizamos el array de robots_participantes
                        $robotsParticipantes[$key]['homologado'] = true;
                        $contador++;
                    }
                    
                    // Actualizar el array de robots_participantes
                    $this->record->update([
                        'robots_participantes' => $robotsParticipantes
                    ]);
                    
                    // Si todos los robots están homologados y la inscripción está confirmada,
                    // actualizar el estado a homologada
                    if ($this->record->todosRobotsHomologados() && $this->record->estado === 'confirmada') {
                        $this->record->marcarHomologada();
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title("Se han homologado {$contador} robots")
                        ->success()
                        ->send();
                        
                    $this->redirect(route('filament.admin.resources.inscripciones.view', $this->record));
                }),
            
            Actions\Action::make('exportar_pdf')
                ->label('Exportar a PDF')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    return InscripcionesEventoResource::generatePDF(collect([$this->record]));
                }),
                
            Actions\Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    return InscripcionesEventoResource::generateExcel(collect([$this->record]));
                }),
        ];
    }

    // Este método es llamado automáticamente por Filament y configura el widget
    protected function getWidgets(): array
    {
        return [
            \App\Filament\Resources\InscripcionesEventoResource\Widgets\HomologacionRobotsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\InscripcionesEventoResource\Widgets\HomologacionRobotsWidget::class,
        ];
    }
} 