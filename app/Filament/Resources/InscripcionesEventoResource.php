<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InscripcionesEventoResource\Pages;
use App\Models\InscripcionEvento;
use App\Models\Evento;
use App\Models\FechaEvento;
use App\Models\CategoriaEvento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;

class InscripcionesEventoResource extends Resource
{
    protected static ?string $model = InscripcionEvento::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Competencia';

    protected static ?string $slug = 'inscripciones';

    protected static ?string $navigationLabel = 'Inscripciones';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'evento', 
                'categoriaEvento.categoria', 
                'categoriaEvento.fecha_evento', 
                'equipo'
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Evento')
                    ->schema([
                        Forms\Components\TextInput::make('evento_nombre')
                            ->label('Evento')
                            ->disabled()
                            ->formatStateUsing(fn (InscripcionEvento $record): string => 
                                $record->evento->nombre ?? 'Sin evento'),
                            
                        Forms\Components\TextInput::make('fecha_evento_nombre')
                            ->label('Fecha del evento')
                            ->disabled()
                            ->formatStateUsing(fn (InscripcionEvento $record): string => 
                                $record->categoriaEvento->fecha_evento->nombre ?? 'Sin fecha'),
                            
                        Forms\Components\TextInput::make('categoria_evento_nombre')
                            ->label('Categoría')
                            ->disabled()
                            ->formatStateUsing(fn (InscripcionEvento $record): string => 
                                $record->categoriaEvento->categoria->nombre ?? 'Sin categoría'),
                    ])
                    ->columns(3)
                    ->collapsible(),
                    
                Forms\Components\Section::make('Información del Equipo')
                    ->schema([
                        Forms\Components\TextInput::make('equipo_nombre')
                            ->label('Nombre del equipo')
                            ->disabled()
                            ->formatStateUsing(fn (InscripcionEvento $record): string => 
                                $record->equipo->nombre ?? 'Sin equipo'),
                            
                        Forms\Components\TextInput::make('equipo_email')
                            ->label('Email del equipo')
                            ->disabled()
                            ->formatStateUsing(fn (InscripcionEvento $record): string => 
                                $record->equipo->email ?? 'Sin email'),
                            
                        Forms\Components\Select::make('estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'confirmada' => 'Confirmada',
                                'rechazada' => 'Rechazada',
                                'cancelada' => 'Cancelada',
                                'homologada' => 'Homologada',
                                'participando' => 'Participando',
                                'finalizada' => 'Finalizada',
                            ])
                            ->required(),
                            
                        Forms\Components\Textarea::make('notas_admin')
                            ->label('Notas del administrador')
                            ->columnSpan(3)
                            ->rows(3),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('evento.nombre')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->description(fn (InscripcionEvento $record): string => 
                        $record->evento->fecha_inicio?->format('d/m/Y') ?? ''),
                    
                Tables\Columns\TextColumn::make('categoriaEvento.fecha_evento.nombre')
                    ->label('Fecha')
                    ->searchable()
                    ->sortable()
                    ->description(fn (InscripcionEvento $record): string => 
                        $record->categoriaEvento->fecha_evento->fecha_inicio?->format('d/m/Y') ?? ''),
                    
                Tables\Columns\TextColumn::make('categoriaEvento.categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('equipo.nombre')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-group'),
                    
                Tables\Columns\TextColumn::make('robots')
                    ->label('Robots')
                    ->state(function (InscripcionEvento $record): string {
                        if (!$record->equipo) {
                            return 'Sin equipo asociado';
                        }
                        
                        // Obtener robots
                        $robotsText = 'Sin robots';
                        
                        if ($record->equipo) {
                            // Obtener todos los robots del equipo
                            $robots = \App\Models\Robot::where('equipo_id', $record->equipo_id)->get();
                            if ($robots->isNotEmpty()) {
                                $robotsText = $robots->pluck('nombre')->implode(', ');
                            }
                        } else {
                            $robotsText = 'Sin equipo asociado';
                        }
                        
                        return $robotsText;
                    })
                    ->icon('heroicon-o-cpu-chip')
                    ->color('success')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'confirmada' => 'info',
                        'rechazada' => 'danger',
                        'cancelada' => 'warning',
                        'homologada' => 'success',
                        'participando' => 'success',
                        'finalizada' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pendiente' => 'heroicon-o-clock',
                        'confirmada' => 'heroicon-o-check-circle',
                        'rechazada' => 'heroicon-o-x-circle',
                        'cancelada' => 'heroicon-o-x-mark',
                        'homologada' => 'heroicon-o-check-badge',
                        'participando' => 'heroicon-o-rocket-launch',
                        'finalizada' => 'heroicon-o-trophy',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de inscripción')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('evento_id')
                    ->label('Evento')
                    ->options(fn () => Evento::pluck('nombre', 'id')->toArray())
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->indicator('Evento')
                    ->columnSpan(2),
                    
                Filter::make('fecha_evento')
                    ->form([
                        Forms\Components\Select::make('fecha_evento_id')
                            ->label('Fecha del evento')
                            ->options(function () {
                                $eventos = request()->get('tableFilters.evento_id.values', []);
                                
                                $query = FechaEvento::query();
                                
                                if (!empty($eventos)) {
                                    $query->whereIn('evento_id', $eventos);
                                }
                                
                                return $query->pluck('nombre', 'id')->toArray();
                            })
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_evento_id'],
                                fn (Builder $query, $fechaEventoIds): Builder => $query->whereHas(
                                    'categoriaEvento',
                                    fn (Builder $query) => $query->whereIn('fecha_evento_id', $fechaEventoIds)
                                )
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['fecha_evento_id']) {
                            return null;
                        }
                        
                        $count = count($data['fecha_evento_id']);
                        return 'Fecha: ' . $count . ' ' . ($count === 1 ? 'seleccionada' : 'seleccionadas');
                    })
                    ->columnSpan(2),
                    
                SelectFilter::make('categoria_evento_id')
                    ->label('Categoría')
                    ->options(function () {
                        $eventos = request()->get('tableFilters.evento_id.values', []);
                        $fechas = request()->get('tableFilters.fecha_evento.fecha_evento_id', []);
                        
                        $query = CategoriaEvento::query()->with('categoria');
                        
                        if (!empty($eventos)) {
                            $query->whereIn('evento_id', $eventos);
                        }
                        
                        if (!empty($fechas)) {
                            $query->whereIn('fecha_evento_id', $fechas);
                        }
                        
                        return $query->get()->mapWithKeys(function ($categoriaEvento) {
                            $nombreCategoria = $categoriaEvento->categoria 
                                ? $categoriaEvento->categoria->nombre 
                                : 'Sin categoría';
                            return [$categoriaEvento->id => $nombreCategoria];
                        })->toArray();
                    })
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->indicator('Categoría')
                    ->columnSpan(2),
                    
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'rechazada' => 'Rechazada',
                        'cancelada' => 'Cancelada',
                        'homologada' => 'Homologada',
                        'participando' => 'Participando',
                        'finalizada' => 'Finalizada',
                    ])
                    ->multiple()
                    ->searchable()
                    ->indicator('Estado')
                    ->columnSpan(1),
            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->filtersApplyAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Aplicar filtros')
                    ->color('primary')
            )
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filtros')
                    ->color('gray')
                    ->icon('heroicon-m-funnel')
            )
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('primary')
                    ->icon('heroicon-o-eye'),
                    
                Tables\Actions\Action::make('confirmar_inscripcion')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar inscripción')
                    ->modalDescription('¿Estás seguro de que deseas confirmar esta inscripción?')
                    ->modalSubmitActionLabel('Sí, confirmar')
                    ->visible(fn (InscripcionEvento $record) => $record->estado === 'pendiente')
                    ->action(function (InscripcionEvento $record) {
                        $record->update([
                            'estado' => 'confirmada',
                        ]);
                        
                        // Opcional: Notificar al equipo por email
                        
                        Notification::make()
                            ->title('Inscripción confirmada correctamente')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\Action::make('exportar_pdf_individual')
                    ->label('PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Forms\Components\Toggle::make('incluir_titulo')
                            ->label('Incluir título')
                            ->default(true),
                            
                        Forms\Components\TextInput::make('titulo')
                            ->label('Título del reporte')
                            ->default('Detalle de Inscripción'),
                    ])
                    ->action(function (InscripcionEvento $record, array $data) {
                        return InscripcionesEventoResource::generatePDF(
                            $record,
                            $data['incluir_titulo'] ?? true,
                            $data['titulo'] ?? 'Detalle de Inscripción',
                            'inscripcion_' . $record->id
                        );
                    }),
                    
                Tables\Actions\Action::make('exportar_excel_individual')
                    ->label('Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Toggle::make('incluir_encabezados')
                            ->label('Incluir encabezados')
                            ->default(true),
                    ])
                    ->action(function (InscripcionEvento $record, array $data) {
                        return InscripcionesEventoResource::generateExcel(
                            $record,
                            $data['incluir_encabezados'] ?? true,
                            'inscripcion_' . $record->id
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('confirmar_inscripciones')
                        ->label('Confirmar inscripciones')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $pendientes = $records->where('estado', 'pendiente');
                            $count = $pendientes->count();
                            
                            if ($count > 0) {
                                foreach ($pendientes as $record) {
                                    $record->update(['estado' => 'confirmada']);
                                }
                                
                                Notification::make()
                                    ->title("{$count} inscripciones confirmadas correctamente")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('No hay inscripciones pendientes para confirmar')
                                    ->warning()
                                    ->send();
                            }
                        }),
                        
                    BulkAction::make('exportar_excel')
                        ->label('Exportar a Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->form([
                            Forms\Components\Toggle::make('incluir_encabezados')
                                ->label('Incluir encabezados')
                                ->default(true),
                                
                            Forms\Components\TextInput::make('nombre_archivo')
                                ->label('Nombre del archivo')
                                ->default('inscripciones_seleccionadas_' . now()->format('YmdHis')),
                        ])
                        ->action(function (Collection $records, array $data) {
                            return self::generateExcel(
                                $records,
                                $data['incluir_encabezados'] ?? true,
                                $data['nombre_archivo'] ?? null
                            );
                        }),
                    BulkAction::make('exportar_pdf')
                        ->label('Exportar a PDF')
                        ->icon('heroicon-o-document-text')
                        ->color('danger')
                        ->form([
                            Forms\Components\Toggle::make('incluir_titulo')
                                ->label('Incluir título')
                                ->default(true),
                                
                            Forms\Components\TextInput::make('titulo')
                                ->label('Título del reporte')
                                ->default('Listado de Inscripciones Seleccionadas'),
                                
                            Forms\Components\TextInput::make('nombre_archivo')
                                ->label('Nombre del archivo')
                                ->default('inscripciones_seleccionadas_' . now()->format('YmdHis')),
                        ])
                        ->action(function (Collection $records, array $data) {
                            return self::generatePDF(
                                $records,
                                $data['incluir_titulo'] ?? true,
                                $data['titulo'] ?? 'Listado de Inscripciones Seleccionadas',
                                $data['nombre_archivo'] ?? null
                            );
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
            ->striped();
    }

    public static function generateExcel($records, bool $incluirEncabezados = true, ?string $nombreArchivo = null)
    {
        // Aseguramos que $records sea una colección, aunque sea de otro tipo
        if (!$records instanceof Collection) {
            $records = collect($records);
        }
        
        $fileName = $nombreArchivo ? $nombreArchivo . '.csv' : 'inscripciones_' . now()->format('YmdHis') . '.csv';
        
        return response()->streamDownload(function () use ($records, $incluirEncabezados) {
            $headers = ['Evento', 'Fecha', 'Categoría', 'Equipo', 'Robots', 'Estado', 'Fecha de inscripción'];
            
            $handle = fopen('php://output', 'w');
            
            // Insertar la fila de encabezados si es necesario
            if ($incluirEncabezados) {
                fputcsv($handle, $headers);
            }
            
            foreach ($records as $record) {
                // Obtener robots
                $robotsText = 'Sin robots';
                
                if ($record->equipo) {
                    // Obtener todos los robots del equipo
                    $robots = \App\Models\Robot::where('equipo_id', $record->equipo_id)->get();
                    if ($robots->isNotEmpty()) {
                        $robotsText = $robots->pluck('nombre')->implode(', ');
                    }
                } else {
                    $robotsText = 'Sin equipo asociado';
                }
                
                $row = [
                    $record->evento->nombre ?? 'Sin evento',
                    $record->categoriaEvento->fecha_evento->nombre ?? 'Sin fecha',
                    $record->categoriaEvento->categoria->nombre ?? 'Sin categoría',
                    $record->equipo->nombre ?? 'Sin equipo',
                    $robotsText,
                    $record->estado,
                    $record->created_at->format('d/m/Y H:i'),
                ];
                
                fputcsv($handle, $row);
            }
            
            fclose($handle);
            
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public static function generatePDF($records, bool $incluirTitulo = true, string $titulo = 'Listado de Inscripciones', ?string $nombreArchivo = null)
    {
        // Aseguramos que $records sea una colección, aunque sea de otro tipo
        if (!$records instanceof Collection) {
            $records = collect($records);
        }
        
        $data = [];
        $estadisticas = [];
        $totalEquipos = 0;
        $totalRobots = 0;
        
        // Obtener detalles del filtro para mostrar en el reporte
        $filtros = [];
        if ($records->isNotEmpty()) {
            $primerRegistro = $records->first();
            $filtros['evento'] = $primerRegistro->evento->nombre ?? 'Todos los eventos';
            
            if ($primerRegistro->categoriaEvento && $primerRegistro->categoriaEvento->fecha_evento) {
                $filtros['fecha'] = $primerRegistro->categoriaEvento->fecha_evento->nombre ?? 'Todas las fechas';
            } else {
                $filtros['fecha'] = 'Todas las fechas';
            }
            
            if ($primerRegistro->categoriaEvento && $primerRegistro->categoriaEvento->categoria) {
                $filtros['categoria'] = $primerRegistro->categoriaEvento->categoria->nombre ?? 'Todas las categorías';
            } else {
                $filtros['categoria'] = 'Todas las categorías';
            }
        }
        
        // Array para almacenar equipos y robots por categoría
        $categorias = [];
        $equiposPorCategoria = [];
        
        foreach ($records as $record) {
            // Obtener la categoría
            $categoriaId = $record->categoria_evento_id;
            $categoriaNombre = $record->categoriaEvento && $record->categoriaEvento->categoria 
                ? $record->categoriaEvento->categoria->nombre 
                : 'Sin categoría';
            
            // Inicializar el contador de la categoría si no existe
            if (!isset($categorias[$categoriaId])) {
                $categorias[$categoriaId] = [
                    'nombre' => $categoriaNombre,
                    'equipos' => 0,
                    'robots' => 0,
                    'robots_homologados' => 0,
                    'robots_pendientes' => 0,
                    'estados' => [
                        'pendiente' => 0,
                        'confirmada' => 0,
                        'rechazada' => 0,
                        'cancelada' => 0,
                        'homologada' => 0,
                        'participando' => 0,
                        'finalizada' => 0,
                    ]
                ];
            }
            
            // Registrar el estado de la inscripción
            $categorias[$categoriaId]['estados'][$record->estado] += 1;
            
            // Contar el equipo solo si no se ha contado antes para esta categoría
            $equipoClave = $categoriaId . '_' . $record->equipo_id;
            if (!isset($equiposPorCategoria[$equipoClave]) && $record->equipo_id) {
                $equiposPorCategoria[$equipoClave] = true;
                $categorias[$categoriaId]['equipos'] += 1;
                $totalEquipos += 1;
            }
            
            // Obtener robots
            $robots = \App\Models\Robot::where('equipo_id', $record->equipo_id)->get();
            
            if (!empty($robots)) {
                // Contar robots homologados y pendientes
                $robotsCount = count($robots);
                $homologadosCount = 0;
                
                foreach ($robots as $robot) {
                    // Verificar si el robot está homologado para esta categoría
                    if ($robot->estaHomologado($record->categoria_evento_id)) {
                        $homologadosCount++;
                    }
                }
                
                // Actualizar contadores
                $categorias[$categoriaId]['robots'] += $robotsCount;
                $categorias[$categoriaId]['robots_homologados'] += $homologadosCount;
                $categorias[$categoriaId]['robots_pendientes'] += ($robotsCount - $homologadosCount);
                
                // Actualizar totales
                $totalRobots += $robotsCount;
                
                $robotsText = $robots->pluck('nombre')->implode(', ');
            } else {
                $robotsText = 'Sin robots';
            }
            
            $data[] = [
                'evento' => $record->evento->nombre ?? 'Sin evento',
                'fecha' => $record->categoriaEvento->fecha_evento->nombre ?? 'Sin fecha',
                'categoria' => $categoriaNombre,
                'equipo' => $record->equipo->nombre ?? 'Sin equipo',
                'robots' => $robotsText,
                'estado' => $record->estado,
                'fecha_inscripcion' => $record->created_at->format('d/m/Y H:i'),
            ];
        }
        
        // Preparar estadísticas para mostrar en el reporte
        foreach ($categorias as $categoria) {
            $estadisticas[] = $categoria;
        }
        
        $fileName = $nombreArchivo ? $nombreArchivo . '.pdf' : 'inscripciones_' . now()->format('YmdHis') . '.pdf';
        
        $pdf = PDF::loadView('exports.inscripciones', [
            'inscripciones' => $data,
            'incluirTitulo' => $incluirTitulo,
            'titulo' => $titulo,
            'filtros' => $filtros,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'estadisticas' => $estadisticas,
            'totalEquipos' => $totalEquipos,
            'totalRobots' => $totalRobots,
        ]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInscripcionesEventos::route('/'),
            'view' => Pages\ViewInscripcionEvento::route('/{record}'),
        ];
    }
} 