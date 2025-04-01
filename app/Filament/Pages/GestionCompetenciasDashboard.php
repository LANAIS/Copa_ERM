<?php

namespace App\Filament\Pages;

use App\Models\CategoriaEvento;
use App\Models\Evento;
use App\Models\Homologacion;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Livewire\Attributes\Computed;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class GestionCompetenciasDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static string $view = 'filament.pages.gestion-competencias-dashboard';
    protected static ?string $navigationLabel = 'Dashboard de Competencias';
    protected static ?string $title = 'Dashboard de Gestión de Competencias';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Competencia';
    
    protected static ?string $slug = 'gestion-competencias-dashboard';
    
    public ?string $selectedEvento = null;
    public ?string $selectedFecha = null;
    public ?Collection $competencias = null;
    public ?Collection $fechasEvento = null;
    
    public function mount(): void
    {
        $this->competencias = collect();
        $this->fechasEvento = collect();
        $this->selectedEvento = Evento::where('estado', 'abierto')->first()?->id;
        
        if ($this->selectedEvento) {
            $this->cargarFechasEvento();
            $this->cargarCompetencias();
        }
    }
    
    #[Computed]
    public function eventos()
    {
        return Evento::orderBy('fecha_inicio', 'desc')->pluck('nombre', 'id');
    }
    
    public function cargarFechasEvento()
    {
        if (!$this->selectedEvento) {
            $this->fechasEvento = collect();
            $this->selectedFecha = null;
            return;
        }
        
        $this->fechasEvento = \App\Models\FechaEvento::where('evento_id', $this->selectedEvento)
            ->where('activo', true)
            ->orderBy('fecha_inicio', 'asc')
            ->get();
            
        // Reseteamos el valor seleccionado
        $this->selectedFecha = null;
    }
    
    public function updatedSelectedEvento()
    {
        $this->cargarFechasEvento();
        $this->cargarCompetencias();
    }
    
    public function updatedSelectedFecha()
    {
        $this->cargarCompetencias();
    }
    
    public function cargarCompetencias()
    {
        if (!$this->selectedEvento) {
            $this->competencias = collect();
            return;
        }
        
        $query = CategoriaEvento::with(['categoria', 'llave', 'evento'])
            ->where('evento_id', $this->selectedEvento);
        
        // Si hay una fecha seleccionada, filtrar por esa fecha
        if ($this->selectedFecha) {
            $query->whereHas('fecha_evento', function($q) {
                $q->where('id', $this->selectedFecha);
            });
        }
        
        $this->competencias = $query->get();
    }
    
    public function cambiarEstado($categoriaId, $nuevoEstado)
    {
        $categoria = CategoriaEvento::findOrFail($categoriaId);
        
        try {
            // Verificar transiciones válidas
            $estadoActual = $categoria->estado_competencia;
            $transicionValida = $this->esTransicionValida($estadoActual, $nuevoEstado);
            
            if (!$transicionValida) {
                Notification::make()
                    ->title('Transición no permitida')
                    ->body("No se puede cambiar de {$estadoActual} a {$nuevoEstado}")
                    ->danger()
                    ->send();
                return;
            }
            
            // Realizar acciones específicas según el nuevo estado
            switch ($nuevoEstado) {
                case 'inscripciones':
                    $categoria->update([
                        'estado_competencia' => 'inscripciones',
                        'inscripciones_abiertas' => true
                    ]);
                    break;
                    
                case 'homologacion':
                    $categoria->update([
                        'estado_competencia' => 'homologacion',
                        'inscripciones_abiertas' => false
                    ]);
                    $categoria->crearHomologaciones();
                    break;
                    
                case 'armado_llaves':
                    $categoria->update(['estado_competencia' => 'armado_llaves']);
                    break;
                    
                case 'en_curso':
                    if (!$categoria->llave) {
                        Notification::make()
                            ->title('Error')
                            ->body('La categoría debe tener un bracket para iniciar la competencia')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $categoria->update(['estado_competencia' => 'en_curso']);
                    $categoria->llave->update(['estado_torneo' => 'en_curso']);
                    break;
                    
                case 'finalizada':
                    $categoria->update(['estado_competencia' => 'finalizada']);
                    
                    if ($categoria->llave) {
                        $categoria->llave->update([
                            'estado_torneo' => 'finalizado',
                            'finalizado' => true
                        ]);
                    }
                    break;
                    
                default:
                    $categoria->update(['estado_competencia' => $nuevoEstado]);
                    break;
            }
            
            Notification::make()
                ->title('Estado actualizado')
                ->body("La categoría ha pasado a estado: {$nuevoEstado}")
                ->success()
                ->send();
                
            // Recargar las competencias para reflejar los cambios
            $this->cargarCompetencias();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function crearBracket($categoriaId)
    {
        $categoria = CategoriaEvento::findOrFail($categoriaId);
        
        try {
            if ($categoria->llave) {
                Notification::make()
                    ->title('Ya existe un bracket')
                    ->body('Esta categoría ya tiene un bracket creado')
                    ->warning()
                    ->send();
                return;
            }
            
            if ($categoria->estado_competencia !== 'armado_llaves') {
                Notification::make()
                    ->title('No se puede crear bracket')
                    ->body('La categoría debe estar en etapa de armado de llaves')
                    ->danger()
                    ->send();
                return;
            }
            
            $llave = new \App\Models\Llave([
                'categoria_evento_id' => $categoria->id,
                'tipo_fixture' => \App\Models\Llave::TIPO_ELIMINACION_DIRECTA,
                'estructura' => [
                    'total_equipos' => 0,
                    'total_rondas' => 0,
                    'tamano_llave' => 0,
                    'total_enfrentamientos' => 0,
                ],
                'finalizado' => false,
                'estado_torneo' => \App\Models\Llave::ESTADO_PENDIENTE
            ]);
            $llave->save();
            
            Notification::make()
                ->title('Bracket creado')
                ->body('Se ha creado un bracket para esta categoría')
                ->success()
                ->send();
                
            // Recargar las competencias para reflejar los cambios
            $this->cargarCompetencias();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    private function esTransicionValida($estadoActual, $nuevoEstado): bool
    {
        $transicionesPermitidas = [
            'creada' => ['inscripciones'],
            'inscripciones' => ['homologacion'],
            'homologacion' => ['armado_llaves'],
            'armado_llaves' => ['en_curso'],
            'en_curso' => ['finalizada'],
            'finalizada' => []
        ];
        
        return in_array($nuevoEstado, $transicionesPermitidas[$estadoActual] ?? []);
    }
    
    #[Computed]
    public function resumenEstados()
    {
        $estados = [
            'creada' => 0,
            'inscripciones' => 0,
            'homologacion' => 0,
            'armado_llaves' => 0,
            'en_curso' => 0,
            'finalizada' => 0,
        ];
        
        foreach ($this->competencias as $competencia) {
            $estados[$competencia->estado_competencia]++;
        }
        
        return $estados;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('ir_a_gestion')
                ->label('Ir a Gestión de Competencias')
                ->url(url('/admin/categoria-eventos'))
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),
            
            Action::make('ir_a_fechas')
                ->label('Gestionar Fechas de Eventos')
                ->url(url('/admin/fecha-eventos'))
                ->icon('heroicon-o-calendar')
                ->color('gray'),
        ];
    }
    
    protected function getMaxWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
} 