<?php

namespace App\Filament\Pages;

use App\Models\Llave;
use App\Models\Equipo;
use App\Models\InscripcionEvento;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class BracketAdminView extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Torneos';
    
    protected static ?string $navigationLabel = 'Administrar Bracket';
    
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.bracket-admin-view';
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $slug = 'bracket-admin/{id}';
    
    public $llaveId;
    public $tipo_fixture;
    public $usar_cabezas_serie;
    public $equipos = [];
    public $equipos_seleccionados = [];
    public $rondas = 3;
    public $num_grupos = 2;
    public $confirmingRegenerate = false;
    public $regenerarBracket = false;
    
    public function mount($id = null): void
    {
        $this->llaveId = $id;
        
        if ($this->llaveId) {
            $llave = Llave::findOrFail($this->llaveId);
            $this->tipo_fixture = $llave->tipo_fixture;
            $this->usar_cabezas_serie = $llave->usar_cabezas_serie;
            
            // Precargar equipos seleccionados si es posible
            $equiposSeleccionados = $llave->enfrentamientos()
                ->whereNotNull('equipo1_id')
                ->orWhereNotNull('equipo2_id')
                ->get()
                ->pluck('equipo1_id')
                ->merge($llave->enfrentamientos()->pluck('equipo2_id'))
                ->filter()
                ->unique()
                ->values()
                ->toArray();
                
            $this->equipos_seleccionados = $equiposSeleccionados;
        }
    }
    
    public function getBracketData()
    {
        $llave = Llave::with(['enfrentamientos.equipo1', 'enfrentamientos.equipo2', 'categoriaEvento.categoria'])
            ->findOrFail($this->llaveId);
        
        // Obtener equipos disponibles para el torneo (filtrando por los inscritos en esta categoría)
        $equiposInscritos = InscripcionEvento::where('categoria_evento_id', $llave->categoria_evento_id)
            ->where('estado', 'aprobada')
            ->with(['equipo', 'robots'])
            ->get()
            ->pluck('equipo')
            ->filter()
            ->unique('id');
        
        // Si no hay equipos inscritos, mostrar todos los equipos (fallback)
        if ($equiposInscritos->isEmpty()) {
            $equiposDisponibles = Equipo::all();
        } else {
            $equiposDisponibles = $equiposInscritos;
        }
        
        return [
            'llave' => $llave,
            'equipos' => $equiposDisponibles,
            'tiposTorneo' => [
                Llave::TIPO_ELIMINACION_DIRECTA => 'Eliminación Directa (Single Elimination)',
                Llave::TIPO_ELIMINACION_DOBLE => 'Eliminación Doble (Double Elimination)',
                Llave::TIPO_TODOS_CONTRA_TODOS => 'Todos contra Todos (Round Robin)',
                Llave::TIPO_SUIZO => 'Sistema Suizo (Swiss)',
                Llave::TIPO_GRUPOS => 'Fase de Grupos',
                Llave::TIPO_FASE_GRUPOS_ELIMINACION => 'Fase de Grupos + Eliminación',
            ]
        ];
    }
    
    public function configurarTipo()
    {
        $llave = Llave::findOrFail($this->llaveId);
        
        $llave->update([
            'tipo_fixture' => $this->tipo_fixture,
            'usar_cabezas_serie' => $this->usar_cabezas_serie,
        ]);
        
        Notification::make()
            ->title('Tipo de torneo configurado correctamente')
            ->success()
            ->send();
    }
    
    public function generarBracket()
    {
        $llave = Llave::findOrFail($this->llaveId);
        
        if (count($this->equipos_seleccionados) < 2) {
            Notification::make()
                ->title('Debe seleccionar al menos 2 equipos')
                ->danger()
                ->send();
                
            return;
        }
        
        // Verificar si el bracket ya tiene juegos
        if ($llave->enfrentamientos()->count() > 0) {
            // Preguntar si desea reiniciar
            if (!$this->regenerarBracket) {
                $this->confirmingRegenerate = true;
                return;
            }
            
            // Limpiar bracket anterior
            $llave->enfrentamientos()->delete();
            $llave->update([
                'estado_torneo' => 'pendiente',
                'finalizado' => false
            ]);
        }
        
        // Generar el bracket según el tipo de fixture
        switch ($llave->tipo_fixture) {
            case Llave::TIPO_ELIMINACION_DIRECTA:
                $llave->generarEliminacionDirecta($this->equipos_seleccionados);
                break;
            case Llave::TIPO_ELIMINACION_DOBLE:
                $llave->generarEliminacionDoble($this->equipos_seleccionados);
                break;
            case Llave::TIPO_TODOS_CONTRA_TODOS:
                $llave->generarTodosContraTodos($this->equipos_seleccionados);
                break;
            case Llave::TIPO_SUIZO:
                $llave->generarSuizo($this->equipos_seleccionados, $this->rondas);
                break;
            case Llave::TIPO_GRUPOS:
            case Llave::TIPO_FASE_GRUPOS_ELIMINACION:
                $conEliminacion = $llave->tipo_fixture === Llave::TIPO_FASE_GRUPOS_ELIMINACION;
                $llave->generarGrupos($this->equipos_seleccionados, $this->num_grupos, $conEliminacion);
                break;
        }
        
        // Actualizar estado
        $llave->iniciar();
        
        // Actualizar estado de la competencia
        $llave->categoriaEvento->update(['estado_competencia' => 'en_curso']);
        
        Notification::make()
            ->title('Bracket generado correctamente')
            ->success()
            ->send();
    }
    
    public function iniciarTorneo()
    {
        $llave = Llave::findOrFail($this->llaveId);
        $llave->iniciar();
        
        Notification::make()
            ->title('Torneo iniciado correctamente')
            ->success()
            ->send();
    }
    
    public function finalizarTorneo()
    {
        $llave = Llave::findOrFail($this->llaveId);
        $llave->finalizar();
        
        // Actualizar estado de la competencia
        $llave->categoriaEvento->update(['estado_competencia' => 'finalizada']);
        
        Notification::make()
            ->title('Torneo finalizado correctamente')
            ->success()
            ->send();
    }
    
    public function reiniciarTorneo()
    {
        $llave = Llave::findOrFail($this->llaveId);
        $llave->reiniciar();
        
        Notification::make()
            ->title('Torneo reiniciado correctamente')
            ->success()
            ->send();
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('ver_bracket')
                ->label('Ver Bracket')
                ->url(fn () => "/admin/brackets/{$this->llaveId}")
                ->icon('heroicon-o-eye')
                ->color('info'),
        ];
    }
    
    // Métodos para la interfaz de usuario
    public function seleccionarTodos()
    {
        $bracketData = $this->getBracketData();
        $this->equipos_seleccionados = $bracketData['equipos']->pluck('id')->toArray();
    }
    
    public function deseleccionarTodos()
    {
        $this->equipos_seleccionados = [];
    }
    
    public function confirmarRegenerarBracket()
    {
        $this->regenerarBracket = true;
        $this->confirmingRegenerate = false;
        $this->generarBracket();
    }
    
    // Gestión de resultados
    public $editingEnfrentamiento = null;
    public $enfrentamientoId = null;
    public $puntaje_equipo1 = null;
    public $puntaje_equipo2 = null;
    public $mostrarModalResultado = false;
    
    public function editarResultado($enfrentamientoId)
    {
        $this->enfrentamientoId = $enfrentamientoId;
        $enfrentamiento = \App\Models\Enfrentamiento::find($enfrentamientoId);
        
        if ($enfrentamiento) {
            $this->editingEnfrentamiento = $enfrentamiento;
            $this->puntaje_equipo1 = $enfrentamiento->puntaje_equipo1;
            $this->puntaje_equipo2 = $enfrentamiento->puntaje_equipo2;
            $this->mostrarModalResultado = true;
        }
    }
    
    public function guardarResultado()
    {
        $enfrentamiento = \App\Models\Enfrentamiento::find($this->enfrentamientoId);
        
        if (!$enfrentamiento) {
            $this->cancelarEdicionResultado();
            return;
        }
        
        // Validar
        $this->validate([
            'puntaje_equipo1' => 'required|integer|min:0',
            'puntaje_equipo2' => 'required|integer|min:0',
        ]);
        
        // Determinar ganador automáticamente
        $ganadorId = null;
        
        if ($this->puntaje_equipo1 > $this->puntaje_equipo2) {
            $ganadorId = $enfrentamiento->equipo1_id;
        } elseif ($this->puntaje_equipo2 > $this->puntaje_equipo1) {
            $ganadorId = $enfrentamiento->equipo2_id;
        }
        
        // Actualizar
        $enfrentamiento->update([
            'puntaje_equipo1' => $this->puntaje_equipo1,
            'puntaje_equipo2' => $this->puntaje_equipo2,
            'ganador_id' => $ganadorId,
        ]);
        
        // Avanzar ganador a la siguiente ronda
        $llave = $enfrentamiento->llave;
        
        // Método específico según el tipo de torneo
        if ($ganadorId) {
            switch ($llave->tipo_fixture) {
                case \App\Models\Llave::TIPO_ELIMINACION_DOBLE:
                    $llave->avanzarGanadorDobleEliminacion($enfrentamiento);
                    break;
                case \App\Models\Llave::TIPO_FASE_GRUPOS_ELIMINACION:
                    // Para fase de grupos, actualizar estadísticas del grupo
                    $llave->acumularPuntosGrupo($enfrentamiento);
                    break;
                default:
                    // Para otros tipos, usar el método estándar
                    $llave->avanzarGanador($enfrentamiento);
                    break;
            }
        }
        
        $this->cancelarEdicionResultado();
        
        Notification::make()
            ->title('Resultado guardado correctamente')
            ->success()
            ->send();
    }
    
    public function cancelarEdicionResultado()
    {
        $this->editingEnfrentamiento = null;
        $this->enfrentamientoId = null;
        $this->puntaje_equipo1 = null;
        $this->puntaje_equipo2 = null;
        $this->mostrarModalResultado = false;
    }
} 