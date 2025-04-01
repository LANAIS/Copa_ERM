<?php

namespace App\Filament\Pages;

use App\Models\Evento;
use App\Models\FechaEvento;
use App\Models\CategoriaEvento;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Livewire\Attributes\Computed;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class JerarquiaEventos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static string $view = 'filament.pages.jerarquia-eventos';
    protected static ?string $navigationLabel = 'Estructura Eventos';
    protected static ?string $title = 'Estructura Jerárquica de Eventos';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationGroup = 'Competencia';
    
    protected static ?string $slug = 'jerarquia-eventos';
    
    public ?string $selectedEvento = null;
    public ?Collection $fechasEvento = null;
    public ?Collection $categoriasEvento = null;
    
    public function mount(): void
    {
        $this->fechasEvento = collect();
        $this->categoriasEvento = collect();
        $this->selectedEvento = Evento::where('estado', 'abierto')->first()?->id;
        
        if ($this->selectedEvento) {
            $this->cargarEstructuraEvento();
        }
    }
    
    #[Computed]
    public function eventos()
    {
        return Evento::orderBy('fecha_inicio', 'desc')->pluck('nombre', 'id');
    }
    
    public function updatedSelectedEvento()
    {
        $this->cargarEstructuraEvento();
    }
    
    public function cargarEstructuraEvento()
    {
        if (!$this->selectedEvento) {
            $this->fechasEvento = collect();
            $this->categoriasEvento = collect();
            return;
        }
        
        // Cargar las fechas del evento con sus categorías
        $this->fechasEvento = FechaEvento::with(['categorias.categoria'])
            ->where('evento_id', $this->selectedEvento)
            ->orderBy('orden', 'asc')
            ->orderBy('fecha_inicio', 'asc')
            ->get();
        
        // Cargar categorías sin fecha asignada
        $this->categoriasEvento = CategoriaEvento::with('categoria')
            ->where('evento_id', $this->selectedEvento)
            ->whereNull('fecha_evento_id')
            ->get();
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('ir_a_gestion_eventos')
                ->label('Gestionar Eventos')
                ->url(url('/admin/eventos'))
                ->icon('heroicon-o-calendar')
                ->color('primary'),
            
            Action::make('ir_a_fechas')
                ->label('Gestionar Fechas')
                ->url(url('/admin/fecha-eventos'))
                ->icon('heroicon-o-calendar-days')
                ->color('success'),
                
            Action::make('ir_a_categorias')
                ->label('Gestionar Categorías')
                ->url(url('/admin/categoria-eventos'))
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning'),
        ];
    }
    
    protected function getMaxWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
} 