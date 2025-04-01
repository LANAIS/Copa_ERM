<?php

namespace App\Livewire;

use App\Models\InscripcionEvento;
use Filament\Notifications\Notification;
use Livewire\Component;

class GestionRobots extends Component
{
    public function toggleRobotParticipante($datos)
    {
        try {
            $inscripcionId = $datos['inscripcionId'];
            $robotId = $datos['robotId'];
            $participante = $datos['participante'];
            
            $inscripcion = InscripcionEvento::findOrFail($inscripcionId);
            $inscripcion->actualizarRobotParticipante($robotId, $participante);
            
            Notification::make()
                ->title($participante ? 'Robot incluido como participante' : 'Robot excluido de participantes')
                ->success()
                ->send();
                
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al actualizar el robot')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function homologarRobot($datos)
    {
        try {
            $inscripcionId = $datos['inscripcionId'];
            $robotId = $datos['robotId'];
            
            $inscripcion = InscripcionEvento::findOrFail($inscripcionId);
            $inscripcion->homologarRobot($robotId);
            
            Notification::make()
                ->title('Robot homologado correctamente')
                ->success()
                ->send();
                
            $this->dispatch('refreshPage');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al homologar el robot')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
} 