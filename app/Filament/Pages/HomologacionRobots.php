<?php

namespace App\Filament\Pages;

use App\Models\CategoriaEvento;
use App\Models\Equipo;
use App\Models\Evento;
use App\Models\InscripcionEvento;
use App\Models\Robot;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Illuminate\Contracts\View\View;

class HomologacionRobots extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static string $view = 'filament.pages.homologacion-robots';
    protected static ?string $navigationGroup = 'Competencia';
    protected static ?string $navigationLabel = 'Homologación de Robots';
    protected static ?int $navigationSort = 6;
    
    public $currentStep = 1;
    public $eventoId = null;
    public $categoriaEventoId = null;
    public $equipoId = null;
    public $robotId = null;
    public $comentarios = null;
    public $evidencias = [];
    
    #[Computed]
    public function evento()
    {
        if (!$this->eventoId) {
            return null;
        }
        
        return Evento::find($this->eventoId);
    }
    
    #[Computed]
    public function categoriaEvento()
    {
        if (!$this->categoriaEventoId) {
            return null;
        }
        
        return CategoriaEvento::with('categoria')->find($this->categoriaEventoId);
    }
    
    #[Computed]
    public function robot()
    {
        if (!$this->robotId) {
            return null;
        }
        
        return Robot::find($this->robotId);
    }
    
    #[Computed]
    public function eventosDisponibles()
    {
        return Evento::where('publicado', true)
            ->where('estado', 'abierto')
            ->orderBy('fecha_inicio')
            ->get();
    }
    
    #[Computed]
    public function categoriasDisponibles()
    {
        if (!$this->eventoId) {
            return collect();
        }
        
        return CategoriaEvento::where('evento_id', $this->eventoId)
            ->where('activo', true)
            ->whereHas('inscripciones')
            ->whereHas('categoria')
            ->with(['categoria', 'inscripciones'])
            ->get();
    }
    
    #[Computed]
    public function equiposUsuario()
    {
        return Equipo::where('user_id', Auth::id())->get();
    }
    
    #[Computed]
    public function robotsDisponibles()
    {
        if (!$this->equipoId || !$this->categoriaEventoId) {
            return collect();
        }
        
        $categoriaEvento = CategoriaEvento::find($this->categoriaEventoId);
        if (!$categoriaEvento || !$categoriaEvento->categoria) {
            return collect();
        }
        
        return Robot::where('equipo_id', $this->equipoId)
            ->where('modalidad', $categoriaEvento->categoria->nombre)
            ->get();
    }
    
    public function updateEventoId($id)
    {
        $this->eventoId = $id;
        $this->categoriaEventoId = null;
    }
    
    public function updateCategoriaEventoId($id)
    {
        $this->categoriaEventoId = $id;
    }
    
    public function updateEquipoId($id)
    {
        $this->equipoId = $id;
        $this->robotId = null;
    }
    
    public function updateRobotId($id)
    {
        $this->robotId = $id;
    }
    
    public function nextStep()
    {
        // Validaciones según el paso actual
        if ($this->currentStep == 1) {
            if (!$this->eventoId || !$this->categoriaEventoId) {
                Notification::make()
                    ->title('Por favor, selecciona un evento y una categoría')
                    ->warning()
                    ->send();
                    
                return;
            }
        } elseif ($this->currentStep == 2) {
            if (!$this->equipoId || !$this->robotId) {
                Notification::make()
                    ->title('Por favor, selecciona un equipo y un robot')
                    ->warning()
                    ->send();
                    
                return;
            }
        }
        
        // Avanzar al siguiente paso
        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }
    
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    public function enviarHomologacion()
    {
        // Validar datos
        if (!$this->eventoId || !$this->categoriaEventoId || !$this->equipoId || !$this->robotId) {
            Notification::make()
                ->title('Por favor, completa todos los campos requeridos')
                ->warning()
                ->send();
                
            return;
        }
        
        // Obtener el robot
        $robot = Robot::find($this->robotId);
        if (!$robot) {
            Notification::make()
                ->title('Robot no encontrado')
                ->danger()
                ->send();
                
            return;
        }
        
        // Buscar la inscripción asociada al evento
        $inscripcion = \App\Models\InscripcionEvento::where('user_id', Auth::id())
            ->where('evento_id', $this->eventoId)
            ->where('categoria_evento_id', $this->categoriaEventoId)
            ->where('equipo_id', $this->equipoId)
            ->first();
            
        if (!$inscripcion) {
            Notification::make()
                ->title('No tienes una inscripción para este evento y categoría')
                ->warning()
                ->send();
                
            return;
        }
        
        // Verificar si la categoría ya está en estado de homologación
        $categoriaEvento = CategoriaEvento::find($this->categoriaEventoId);
        if ($categoriaEvento && $categoriaEvento->estado_competencia !== CategoriaEvento::ESTADO_HOMOLOGACION) {
            // Podemos actualizar automáticamente el estado a homologación
            $categoriaEvento->update(['estado_competencia' => CategoriaEvento::ESTADO_HOMOLOGACION]);
            
            Notification::make()
                ->title('La categoría ha sido actualizada a estado de homologación')
                ->success()
                ->send();
        }
        
        // Actualizar el estado de homologación del robot
        $inscripcion->actualizarRobotParticipante($robot->id, true, true);
        
        // Notificar éxito
        Notification::make()
            ->title('¡Robot homologado correctamente!')
            ->success()
            ->send();
            
        // Reiniciar el formulario
        $this->currentStep = 1;
        $this->eventoId = null;
        $this->categoriaEventoId = null;
        $this->equipoId = null;
        $this->robotId = null;
        $this->comentarios = null;
        $this->evidencias = [];
    }
}
