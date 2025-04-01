<?php

namespace App\Filament\Competitor\Resources\MisInscripcionesResource\Pages;

use App\Filament\Competitor\Resources\MisInscripcionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Robot;
use App\Models\InscripcionEvento;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateMisInscripciones extends CreateRecord
{
    protected static string $resource = MisInscripcionesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        
        // Procesamos el campo robot_id para manejar selección múltiple
        if (isset($data['robot_id'])) {
            // Si es un array, convertirlo y mantener una copia en robot_ids
            if (is_array($data['robot_id']) && !empty($data['robot_id'])) {
                $data['robot_ids'] = $data['robot_id']; // Guardar el array completo en otra variable
                $data['robot_id'] = (int)$data['robot_id'][0]; // Usar solo el primer ID para robot_id, como entero
            } else if (is_string($data['robot_id']) && strpos($data['robot_id'], '[') === 0) {
                // Si ya es un string JSON, decodificarlo y extraer el primer ID
                $decoded = json_decode($data['robot_id'], true);
                if (is_array($decoded) && !empty($decoded)) {
                    $data['robot_ids'] = $decoded;
                    $data['robot_id'] = (int)$decoded[0]; // Convertir a entero
                }
            } else {
                // Si es un valor único, asegurar que sea entero
                $data['robot_id'] = (int)$data['robot_id'];
            }
        }
        
        return $data;
    }
    
    /**
     * Crear inscripciones para el evento y categoría seleccionados
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Asegurarnos que el user_id está establecido explícitamente
        $data['user_id'] = Auth::id();
        
        // Log para depuración
        \Illuminate\Support\Facades\Log::info('Creando inscripción con datos: ' . json_encode($data));
        
        // Comenzar una transacción para garantizar que todas las inscripciones se crean correctamente
        return DB::transaction(function () use ($data) {
            // Validamos que están todos los datos necesarios
            if (!isset($data['evento_id']) || !isset($data['categoria_evento_id']) || !isset($data['fecha_evento_id'])) {
                Notification::make()
                    ->title('Faltan datos')
                    ->body('Por favor, complete todos los campos del formulario')
                    ->danger()
                    ->send();
                
                \Illuminate\Support\Facades\Log::error('Faltan datos para la inscripción');
                return parent::handleRecordCreation($data);
            }
            
            // Obtener los IDs de los robots seleccionados
            $robotIds = isset($data['robot_id']) ? $data['robot_id'] : [];
            if (!is_array($robotIds)) {
                $robotIds = [$robotIds]; // Convertir a array si es un solo ID
            } else if (is_string($data['robot_id']) && json_decode($data['robot_id'], true)) {
                $robotIds = json_decode($data['robot_id'], true);
            }
            
            // Convertir todos los IDs a enteros
            $robotIds = array_map('intval', $robotIds);
            \Illuminate\Support\Facades\Log::info('Robots seleccionados DEPURADOS: ' . implode(', ', $robotIds));
            
            // Si no hay robots seleccionados, mostrar error
            if (empty($robotIds)) {
                Notification::make()
                    ->title('Sin robots')
                    ->body('Debe seleccionar al menos un robot para la inscripción')
                    ->danger()
                    ->send();
                    
                \Illuminate\Support\Facades\Log::error('No se seleccionaron robots');
                return parent::handleRecordCreation($data);
            }
            
            // Elegir el primer robot como principal
            $robotId = (int)$robotIds[0];
            
            try {
                // Primero verificamos si ya existe una inscripción de evento para este equipo, evento, fecha y categoría
                $inscripcionEvento = InscripcionEvento::where('evento_id', $data['evento_id'])
                    ->where('equipo_id', $data['equipo_id'])
                    ->where('categoria_evento_id', $data['categoria_evento_id'])
                    ->where('fecha_evento_id', $data['fecha_evento_id'])
                    ->first();
                
                \Illuminate\Support\Facades\Log::info('Verificando inscripción existente: ' . ($inscripcionEvento ? 'Encontrada ID: '.$inscripcionEvento->id : 'No encontrada'));
                
                if ($inscripcionEvento) {
                    // Si existe, actualizamos sus datos
                    $inscripcionEvento->categoria_evento_id = $data['categoria_evento_id'];
                    $inscripcionEvento->fecha_evento_id = $data['fecha_evento_id'];
                    $inscripcionEvento->robot_id = $robotId;
                    
                    // Guardar todos los robots como participantes
                    $robotsParticipantes = [];
                    foreach ($robotIds as $rId) {
                        $robotsParticipantes[] = [
                            'id' => (int)$rId,
                            'participante' => true,
                            'homologado' => false
                        ];
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Robots participantes a guardar: ' . json_encode($robotsParticipantes));
                    
                    $inscripcionEvento->robots_participantes = $robotsParticipantes;
                    $inscripcionEvento->estado = InscripcionEvento::ESTADO_PENDIENTE;
                    $inscripcionEvento->notas_participante = $data['notes'] ?? null;
                    
                    // Asegurarse de guardarlo
                    $saved = $inscripcionEvento->save();
                    \Illuminate\Support\Facades\Log::info('Inscripción evento actualizada guardada: ' . ($saved ? 'SÍ' : 'NO'));
                    \Illuminate\Support\Facades\Log::info('Estado después de guardar: ' . json_encode($inscripcionEvento->robots_participantes));
                    
                    \Illuminate\Support\Facades\Log::info('Inscripción evento existente actualizada. ID: ' . $inscripcionEvento->id);
                } else {
                    // Si no existe, creamos una nueva
                    $inscripcionEvento = new InscripcionEvento();
                    $inscripcionEvento->evento_id = $data['evento_id'];
                    $inscripcionEvento->categoria_evento_id = $data['categoria_evento_id'];
                    $inscripcionEvento->fecha_evento_id = $data['fecha_evento_id'];
                    $inscripcionEvento->equipo_id = $data['equipo_id'];
                    $inscripcionEvento->user_id = Auth::id();
                    $inscripcionEvento->robot_id = $robotId;
                    
                    // Guardar todos los robots como participantes
                    $robotsParticipantes = [];
                    foreach ($robotIds as $rId) {
                        $robotsParticipantes[] = [
                            'id' => (int)$rId,
                            'participante' => true,
                            'homologado' => false
                        ];
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Robots participantes a guardar (nueva inscripción): ' . json_encode($robotsParticipantes));
                    
                    $inscripcionEvento->robots_participantes = $robotsParticipantes;
                    $inscripcionEvento->estado = InscripcionEvento::ESTADO_PENDIENTE;
                    $inscripcionEvento->notas_participante = $data['notes'] ?? null;
                    $inscripcionEvento->codigo_confirmacion = Str::random(8);
                    
                    // Asegurarse de guardarlo
                    $saved = $inscripcionEvento->save();
                    \Illuminate\Support\Facades\Log::info('Nueva inscripción evento guardada: ' . ($saved ? 'SÍ' : 'NO'));
                    \Illuminate\Support\Facades\Log::info('Estado después de guardar nueva inscripción: ' . json_encode($inscripcionEvento->robots_participantes));
                    
                    \Illuminate\Support\Facades\Log::info('Nueva inscripción evento creada. ID: ' . $inscripcionEvento->id);
                }
                
                // Crear o actualizar el registro en Registration
                $registrationData = [
                    'user_id' => Auth::id(),
                    'equipo_id' => $data['equipo_id'],
                    'robot_id' => $robotId,
                    'status' => $inscripcionEvento->estado,
                    'notes' => $data['notes'] ?? null,
                    'registration_date' => now(), // Aseguramos la fecha de registro
                    'competition_id' => 1, // Valor por defecto
                    'competition_event_id' => 1, // Valor por defecto
                    'evento_id' => $data['evento_id'],
                    'categoria_evento_id' => $data['categoria_evento_id'], 
                    'fecha_evento_id' => $data['fecha_evento_id'],
                    'inscripcion_evento_id' => $inscripcionEvento->id,
                ];
                
                \Illuminate\Support\Facades\Log::info('Datos de registration a guardar: ' . json_encode($registrationData));
                
                // Verificar si ya existe un registro en Registration para esta inscripción
                $existingRegistration = static::getModel()::where('inscripcion_evento_id', $inscripcionEvento->id)
                    ->first();
                
                \Illuminate\Support\Facades\Log::info('Buscando registration por inscripcion_evento_id: ' . ($existingRegistration ? 'Encontrado ID: '.$existingRegistration->id : 'No encontrado'));
                
                if (!$existingRegistration) {
                    // Si no se encontró por inscripcion_evento_id, buscar por combinación de campos
                    $existingRegistration = static::getModel()::where('equipo_id', $data['equipo_id'])
                        ->where('evento_id', $data['evento_id'])
                        ->where('fecha_evento_id', $data['fecha_evento_id'])
                        ->where('categoria_evento_id', $data['categoria_evento_id'])
                        ->first();
                        
                    \Illuminate\Support\Facades\Log::info('Buscando registration por combinación de campos: ' . ($existingRegistration ? 'Encontrado ID: '.$existingRegistration->id : 'No encontrado'));
                }
                
                if ($existingRegistration) {
                    // Actualizar el registro existente
                    $existingRegistration->robot_id = $robotId;
                    $existingRegistration->evento_id = $data['evento_id'];
                    $existingRegistration->categoria_evento_id = $data['categoria_evento_id'];
                    $existingRegistration->fecha_evento_id = $data['fecha_evento_id'];
                    $existingRegistration->status = $inscripcionEvento->estado;
                    $existingRegistration->registration_date = now(); // Asegurar que la fecha de registro se actualice
                    $existingRegistration->inscripcion_evento_id = $inscripcionEvento->id;
                    
                    $saved = $existingRegistration->save();
                    \Illuminate\Support\Facades\Log::info('Registration existente actualizado: ' . ($saved ? 'SÍ' : 'NO'));
                    
                    // Asegurarse de que la inscripción evento tenga el ID del registro
                    $inscripcionEvento->registration_id = $existingRegistration->id;
                    $inscripcionEvento->save();
                    
                    \Illuminate\Support\Facades\Log::info('Registro Registration existente actualizado. ID: ' . $existingRegistration->id);
                    
                    $registration = $existingRegistration;
                } else {
                    // Crear nuevo registro
                    $registration = static::getModel()::create($registrationData);
                    \Illuminate\Support\Facades\Log::info('Nuevo Registration creado. ID: ' . $registration->id);
                    
                    // Actualizar la inscripción evento con el ID del registro de Registration
                    $inscripcionEvento->registration_id = $registration->id;
                    $inscripcionEvento->save();
                    
                    \Illuminate\Support\Facades\Log::info('Nuevo registro Registration creado. ID: ' . $registration->id);
                }
                
                \Illuminate\Support\Facades\Log::info('Inscripción completada. ID: ' . $registration->id . ', User ID: ' . Auth::id() . ', Equipo ID: ' . $data['equipo_id']);
                
                Notification::make()
                    ->title('Inscripción realizada')
                    ->body('Se ha registrado exitosamente en el evento con los robots seleccionados.')
                    ->success()
                    ->send();
                    
                return $registration;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al crear inscripción: ' . $e->getMessage());
                
                Notification::make()
                    ->title('Error al crear la inscripción')
                    ->body('Error: ' . $e->getMessage())
                    ->danger()
                    ->send();
                
                return parent::handleRecordCreation($data);
            }
        });
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
