<?php

namespace App\Filament\Pages;

use App\Models\CategoriaEvento;
use App\Models\Homologacion;
use App\Models\InscripcionEvento;
use App\Models\Robot;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class GestionHomologaciones extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static string $view = 'filament.pages.gestion-homologaciones';
    protected static ?string $navigationGroup = 'Competencia';
    protected static ?string $navigationLabel = 'Gestión de Homologaciones';
    protected static ?string $title = 'Gestión de Homologaciones';
    protected static ?int $navigationSort = 6;
    
    public $categoriaEventoId = null;
    public $homologacionId = null;
    public $robotEnEdicion = null;
    public $mostrarFormulario = false;
    
    // Campos de formulario
    public $peso;
    public $ancho;
    public $largo;
    public $alto;
    public $resultado;
    public $observaciones;
    
    public function mount($id = null): void
    {
        if ($id) {
            $this->categoriaEventoId = $id;
        } else {
            $this->categoriaEventoId = request()->query('id');
        }
        
        // Si se proporciona el ID pero no se encuentra la categoría, mostrar un mensaje de error
        if ($this->categoriaEventoId && !$this->categoriaEvento) {
            $this->dispatch('notify', [
                'status' => 'danger',
                'message' => 'La categoría seleccionada no existe'
            ]);
        }
    }
    
    #[Computed]
    public function categoriaEvento()
    {
        if (!$this->categoriaEventoId) {
            return null;
        }
        
        return CategoriaEvento::with(['categoria', 'evento'])->find($this->categoriaEventoId);
    }
    
    #[Computed]
    public function inscripciones()
    {
        if (!$this->categoriaEventoId) {
            return collect();
        }
        
        return InscripcionEvento::where('categoria_evento_id', $this->categoriaEventoId)
            ->where('estado', 'aprobada')
            ->with(['equipo', 'robots', 'robots.homologaciones' => function($query) {
                $query->where('categoria_evento_id', $this->categoriaEventoId);
            }])
            ->get();
    }
    
    public function mostrarFormularioHomologacion($robotId): void
    {
        $this->resetFormulario();
        $this->robotEnEdicion = Robot::find($robotId);
        
        // Verificar si ya existe una homologación
        $homologacion = Homologacion::where('robot_id', $robotId)
            ->where('categoria_evento_id', $this->categoriaEventoId)
            ->first();
        
        if ($homologacion) {
            $this->homologacionId = $homologacion->id;
            $this->peso = $homologacion->peso;
            $this->ancho = $homologacion->ancho;
            $this->largo = $homologacion->largo;
            $this->alto = $homologacion->alto;
            $this->resultado = $homologacion->resultado;
            $this->observaciones = $homologacion->observaciones;
        }
        
        $this->mostrarFormulario = true;
    }
    
    public function resetFormulario(): void
    {
        $this->homologacionId = null;
        $this->robotEnEdicion = null;
        $this->peso = null;
        $this->ancho = null;
        $this->largo = null;
        $this->alto = null;
        $this->resultado = null;
        $this->observaciones = null;
        $this->mostrarFormulario = false;
    }
    
    public function guardarHomologacion(): void
    {
        // Validar datos
        $datos = $this->validate([
            'peso' => 'required|numeric|min:0',
            'ancho' => 'required|numeric|min:0',
            'largo' => 'required|numeric|min:0',
            'alto' => 'required|numeric|min:0',
            'resultado' => 'required|in:aprobado,rechazado',
            'observaciones' => 'nullable|string|max:500',
        ]);
        
        if (!$this->robotEnEdicion) {
            Notification::make()
                ->title('Error: Robot no encontrado')
                ->danger()
                ->send();
            return;
        }
        
        $datos['robot_id'] = $this->robotEnEdicion->id;
        $datos['categoria_evento_id'] = $this->categoriaEventoId;
        
        if ($this->homologacionId) {
            // Actualizar homologación existente
            $homologacion = Homologacion::find($this->homologacionId);
            $homologacion->update($datos);
            $mensaje = 'Homologación actualizada correctamente';
        } else {
            // Crear nueva homologación
            Homologacion::create($datos);
            $mensaje = 'Homologación registrada correctamente';
        }
        
        Notification::make()
            ->title($mensaje)
            ->success()
            ->send();
        
        $this->resetFormulario();
    }
    
    public function finalizarHomologaciones(): void
    {
        $categoriaEvento = CategoriaEvento::findOrFail($this->categoriaEventoId);
        
        // Verificar que al menos dos robots estén homologados
        $robotsHomologados = Homologacion::where('categoria_evento_id', $this->categoriaEventoId)
            ->where('resultado', 'aprobado')
            ->count();
        
        if ($robotsHomologados < 2) {
            Notification::make()
                ->title('Error: Se requieren al menos 2 robots homologados')
                ->danger()
                ->send();
            return;
        }
        
        // Actualizar estado de la competencia
        $categoriaEvento->update(['estado_competencia' => 'armado_llaves']);
        
        Notification::make()
            ->title('Proceso de homologación finalizado')
            ->body('La competencia ha pasado a la fase de armado de llaves')
            ->success()
            ->send();
        
        // Redireccionar al dashboard
        $this->redirect('/admin');
    }
    
    public function cancelarFormulario(): void
    {
        $this->resetFormulario();
    }
} 