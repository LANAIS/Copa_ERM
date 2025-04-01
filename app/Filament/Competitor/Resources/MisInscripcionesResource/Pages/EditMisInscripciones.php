<?php

namespace App\Filament\Competitor\Resources\MisInscripcionesResource\Pages;

use App\Filament\Competitor\Resources\MisInscripcionesResource;
use App\Models\InscripcionEvento;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditMisInscripciones extends EditRecord
{
    protected static string $resource = MisInscripcionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    /**
     * Modificar los datos del formulario antes de guardar
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Verificar si robot_id es un array
        if (isset($data['robot_id']) && is_array($data['robot_id']) && !empty($data['robot_id'])) {
            // Guardar todos los robots en robot_ids
            $data['robot_ids'] = $data['robot_id'];
            // Tomar solo el primer robot para robot_id (como entero)
            $data['robot_id'] = (int)$data['robot_id'][0];
        } elseif (isset($data['robot_id']) && is_string($data['robot_id']) && json_decode($data['robot_id'], true)) {
            // Si es un string JSON, decodificarlo
            $robotIds = json_decode($data['robot_id'], true);
            $data['robot_ids'] = $robotIds;
            $data['robot_id'] = isset($robotIds[0]) ? (int)$robotIds[0] : null;
        } elseif (isset($data['robot_id'])) {
            // Asegurar que cualquier otro valor sea entero
            $data['robot_id'] = (int)$data['robot_id'];
        }
        
        return $data;
    }
    
    /**
     * Manejar la actualización del registro
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // Asegurarnos que tenemos todos los datos necesarios
            if (!isset($data['robot_id'])) {
                Notification::make()
                    ->title('Faltan datos')
                    ->body('Debe seleccionar al menos un robot para la inscripción')
                    ->danger()
                    ->send();
                    
                return $record;
            }
            
            // Obtener los IDs de los robots seleccionados
            $robotIds = isset($data['robot_id']) ? $data['robot_id'] : [];
            if (!is_array($robotIds)) {
                $robotIds = [$robotIds];
            } else if (is_string($data['robot_id']) && json_decode($data['robot_id'], true)) {
                $robotIds = json_decode($data['robot_id'], true);
            }
            
            // Convertir todos los IDs a enteros
            $robotIds = array_map('intval', $robotIds);
            
            \Illuminate\Support\Facades\Log::info('Edición: Robots seleccionados DEPURADOS: ' . implode(', ', $robotIds));
            
            // Actualizar la inscripción evento si existe
            if ($record->inscripcion_evento_id) {
                $inscripcionEvento = InscripcionEvento::find($record->inscripcion_evento_id);
                
                if ($inscripcionEvento) {
                    // Actualizar robots participantes
                    $robotsParticipantes = [];
                    foreach ($robotIds as $robotId) {
                        $robotsParticipantes[] = [
                            'id' => (int)$robotId,
                            'participante' => true,
                            'homologado' => false
                        ];
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Edición: Robots participantes a guardar: ' . json_encode($robotsParticipantes));
                    
                    $inscripcionEvento->robots_participantes = $robotsParticipantes;
                    $inscripcionEvento->robot_id = (int)$robotIds[0]; // Actualizar robot principal como entero
                    
                    // Actualizar fecha_evento_id si está presente en los datos
                    if (isset($data['fecha_evento_id'])) {
                        $inscripcionEvento->fecha_evento_id = $data['fecha_evento_id'];
                    }
                    
                    $saved = $inscripcionEvento->save();
                    \Illuminate\Support\Facades\Log::info('Edición: Inscripción evento actualizada: ' . ($saved ? 'SÍ' : 'NO'));
                    \Illuminate\Support\Facades\Log::info('Edición: Estado después de guardar: ' . json_encode($inscripcionEvento->robots_participantes));
                }
            }
            
            // Actualizar el registro con el robot principal
            $record->robot_id = (int)$robotIds[0];
            
            // Actualizar la fecha de registro siempre
            $record->registration_date = now();
            
            // Actualizar fecha_evento_id si está presente en los datos
            if (isset($data['fecha_evento_id'])) {
                $record->fecha_evento_id = $data['fecha_evento_id'];
            }
            
            $saved = $record->save();
            \Illuminate\Support\Facades\Log::info('Edición: Registro actualizado: ' . ($saved ? 'SÍ' : 'NO'));
            
            Notification::make()
                ->title('Inscripción actualizada')
                ->body('Se ha actualizado la inscripción con los robots seleccionados')
                ->success()
                ->send();
                
            return $record;
        });
    }
}
