<?php

namespace App\Filament\Resources\InscripcionesEventoResource\Pages;

use App\Filament\Resources\InscripcionesEventoResource;
use App\Filament\Resources\InscripcionesEventoResource\Widgets\InscripcionesEstadisticasWidget;
use App\Models\Evento;
use App\Models\FechaEvento;
use App\Models\CategoriaEvento;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class ListInscripcionesEventos extends ListRecords
{
    protected static string $resource = InscripcionesEventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_excel_todos')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->size('md')
                ->button()
                ->form([
                    Forms\Components\Section::make('Filtros para exportación')
                        ->description('Selecciona los filtros para la exportación a Excel')
                        ->schema([
                            Forms\Components\Select::make('evento_id')
                                ->label('Evento')
                                ->options(fn () => Evento::pluck('nombre', 'id')->toArray())
                                ->searchable()
                                ->preload(),
                                
                            Forms\Components\Select::make('fecha_evento_id')
                                ->label('Fecha del evento')
                                ->options(function (callable $get) {
                                    if (!$get('evento_id')) {
                                        return FechaEvento::pluck('nombre', 'id')->toArray();
                                    }
                                    
                                    return FechaEvento::where('evento_id', $get('evento_id'))
                                        ->pluck('nombre', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                                
                            Forms\Components\Select::make('categoria_evento_id')
                                ->label('Categoría')
                                ->options(function (callable $get) {
                                    $query = CategoriaEvento::query()->with('categoria');
                                    
                                    if ($get('evento_id')) {
                                        $query->where('evento_id', $get('evento_id'));
                                    }
                                    
                                    if ($get('fecha_evento_id')) {
                                        $query->where('fecha_evento_id', $get('fecha_evento_id'));
                                    }
                                    
                                    return $query->get()->mapWithKeys(function ($categoriaEvento) {
                                        $nombreCategoria = $categoriaEvento->categoria 
                                            ? $categoriaEvento->categoria->nombre 
                                            : 'Sin categoría';
                                        return [$categoriaEvento->id => $nombreCategoria];
                                    })->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                                
                            Forms\Components\Select::make('estado')
                                ->label('Estado')
                                ->options([
                                    'pendiente' => 'Pendiente',
                                    'confirmada' => 'Confirmada',
                                    'pagada' => 'Pagada',
                                    'rechazada' => 'Rechazada',
                                    'cancelada' => 'Cancelada',
                                    'homologada' => 'Homologada',
                                    'participando' => 'Participando',
                                    'finalizada' => 'Finalizada',
                                ])
                                ->multiple(),
                                
                            Forms\Components\Toggle::make('incluir_encabezados')
                                ->label('Incluir encabezados')
                                ->default(true),
                                
                            Forms\Components\TextInput::make('nombre_archivo')
                                ->label('Nombre del archivo')
                                ->default('inscripciones_' . now()->format('YmdHis')),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data) {
                    $query = $this->getTable()->getQuery();
                    
                    // Aplicar filtros seleccionados
                    if (!empty($data['evento_id'])) {
                        $query->where('evento_id', $data['evento_id']);
                    }
                    
                    if (!empty($data['categoria_evento_id'])) {
                        $query->where('categoria_evento_id', $data['categoria_evento_id']);
                    }
                    
                    if (!empty($data['estado'])) {
                        $query->whereIn('estado', $data['estado']);
                    }
                    
                    // Aplicar filtro de fecha_evento a través de la relación categoriaEvento
                    if (!empty($data['fecha_evento_id'])) {
                        $query->whereHas('categoriaEvento', function (Builder $query) use ($data) {
                            $query->where('fecha_evento_id', $data['fecha_evento_id']);
                        });
                    }
                    
                    $records = $query->get();
                    
                    return InscripcionesEventoResource::generateExcel(
                        $records,
                        $data['incluir_encabezados'] ?? true,
                        $data['nombre_archivo'] ?? null
                    );
                }),
                
            Action::make('exportar_pdf_todos')
                ->label('Exportar a PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->size('md')
                ->button()
                ->form([
                    Forms\Components\Section::make('Filtros para exportación')
                        ->description('Selecciona los filtros para la exportación a PDF')
                        ->schema([
                            Forms\Components\Select::make('evento_id')
                                ->label('Evento')
                                ->options(fn () => Evento::pluck('nombre', 'id')->toArray())
                                ->searchable()
                                ->preload(),
                                
                            Forms\Components\Select::make('fecha_evento_id')
                                ->label('Fecha del evento')
                                ->options(function (callable $get) {
                                    if (!$get('evento_id')) {
                                        return FechaEvento::pluck('nombre', 'id')->toArray();
                                    }
                                    
                                    return FechaEvento::where('evento_id', $get('evento_id'))
                                        ->pluck('nombre', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                                
                            Forms\Components\Select::make('categoria_evento_id')
                                ->label('Categoría')
                                ->options(function (callable $get) {
                                    $query = CategoriaEvento::query()->with('categoria');
                                    
                                    if ($get('evento_id')) {
                                        $query->where('evento_id', $get('evento_id'));
                                    }
                                    
                                    if ($get('fecha_evento_id')) {
                                        $query->where('fecha_evento_id', $get('fecha_evento_id'));
                                    }
                                    
                                    return $query->get()->mapWithKeys(function ($categoriaEvento) {
                                        $nombreCategoria = $categoriaEvento->categoria 
                                            ? $categoriaEvento->categoria->nombre 
                                            : 'Sin categoría';
                                        return [$categoriaEvento->id => $nombreCategoria];
                                    })->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                                
                            Forms\Components\Select::make('estado')
                                ->label('Estado')
                                ->options([
                                    'pendiente' => 'Pendiente',
                                    'confirmada' => 'Confirmada',
                                    'pagada' => 'Pagada',
                                    'rechazada' => 'Rechazada',
                                    'cancelada' => 'Cancelada',
                                    'homologada' => 'Homologada',
                                    'participando' => 'Participando',
                                    'finalizada' => 'Finalizada',
                                ])
                                ->multiple(),
                                
                            Forms\Components\Toggle::make('incluir_titulo')
                                ->label('Incluir título')
                                ->default(true),
                                
                            Forms\Components\TextInput::make('titulo')
                                ->label('Título del reporte')
                                ->default('Listado de Inscripciones'),
                                
                            Forms\Components\TextInput::make('nombre_archivo')
                                ->label('Nombre del archivo')
                                ->default('inscripciones_' . now()->format('YmdHis')),
                        ])
                        ->columns(2),
                ])
                ->action(function (array $data) {
                    $query = $this->getTable()->getQuery();
                    
                    // Aplicar filtros seleccionados
                    if (!empty($data['evento_id'])) {
                        $query->where('evento_id', $data['evento_id']);
                    }
                    
                    if (!empty($data['categoria_evento_id'])) {
                        $query->where('categoria_evento_id', $data['categoria_evento_id']);
                    }
                    
                    if (!empty($data['estado'])) {
                        $query->whereIn('estado', $data['estado']);
                    }
                    
                    // Aplicar filtro de fecha_evento a través de la relación categoriaEvento
                    if (!empty($data['fecha_evento_id'])) {
                        $query->whereHas('categoriaEvento', function (Builder $query) use ($data) {
                            $query->where('fecha_evento_id', $data['fecha_evento_id']);
                        });
                    }
                    
                    $records = $query->get();
                    
                    return InscripcionesEventoResource::generatePDF(
                        $records,
                        $data['incluir_titulo'] ?? true,
                        $data['titulo'] ?? 'Listado de Inscripciones',
                        $data['nombre_archivo'] ?? null
                    );
                }),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            InscripcionesEstadisticasWidget::class,
        ];
    }
    
    protected function getTableFiltersFormWidth(): string
    {
        return '3xl';
    }
    
    protected function getTableFiltersFormMaxHeight(): string
    {
        return '80vh';
    }
    
    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
} 