<?php

namespace App\Filament\Resources\GestionCompetenciaResource\Pages;

use App\Filament\Resources\GestionCompetenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\CompetenciaStatsWidget;
use App\Filament\Widgets\HomologacionesPendientesWidget;
use App\Filament\Widgets\CompetenciasEnCursoWidget;
use Filament\Support\Facades\FilamentIcon;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class ListGestionCompetencias extends ListRecords
{
    protected static string $resource = GestionCompetenciaResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CompetenciaStatsWidget::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            HomologacionesPendientesWidget::class,
            CompetenciasEnCursoWidget::class,
        ];
    }

    public function mount(): void
    {
        parent::mount();
        
        // Abrir automáticamente los filtros al cargar la página
        $this->dispatch('open-filters');
    }

    #[On('filterHomologacion')]
    public function filterHomologacion()
    {
        $this->applyFilterState([
            'estado_competencia' => ['homologacion']
        ]);
        
        Notification::make()
            ->title('Filtro aplicado')
            ->body('Mostrando categorías en estado de homologación')
            ->success()
            ->send();
    }
    
    #[On('filterArmadoLlaves')]
    public function filterArmadoLlaves()
    {
        $this->applyFilterState([
            'estado_competencia' => ['armado_llaves']
        ]);
        
        Notification::make()
            ->title('Filtro aplicado')
            ->body('Mostrando categorías en estado de armado de llaves')
            ->success()
            ->send();
    }
    
    #[On('filterEnCurso')]
    public function filterEnCurso()
    {
        $this->applyFilterState([
            'estado_competencia' => ['en_curso']
        ]);
        
        Notification::make()
            ->title('Filtro aplicado')
            ->body('Mostrando categorías en estado en curso')
            ->success()
            ->send();
    }
    
    #[On('filterFinalizadas')]
    public function filterFinalizadas()
    {
        $this->applyFilterState([
            'estado_competencia' => ['finalizada']
        ]);
        
        Notification::make()
            ->title('Filtro aplicado')
            ->body('Mostrando categorías finalizadas')
            ->success()
            ->send();
    }

    #[On('filterInscripciones')]
    public function filterInscripciones()
    {
        $this->applyFilterState([
            'estado_competencia' => ['inscripciones']
        ]);
        
        Notification::make()
            ->title('Filtro aplicado')
            ->body('Mostrando categorías en estado de inscripciones')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('volver')
                ->label('Volver al Dashboard')
                ->url(route('admin.dashboard'))
                ->icon('heroicon-m-arrow-left')
                ->color('gray'),
            Actions\Action::make('recargar')
                ->label('Recargar')
                ->action(fn () => $this->refreshData())
                ->icon('heroicon-m-arrow-path')
                ->color('info'),
        ];
    }
    
    public function getTitle(): string
    {
        return 'Gestión de Competencias';
    }
}
