<?php

namespace App\Filament\Competitor\Resources;

use App\Filament\Competitor\Resources\MisInscripcionesResource\Pages;
use App\Filament\Competitor\Resources\MisInscripcionesResource\RelationManagers;
use App\Models\Registration;
use App\Models\Competition;
use App\Models\Robot;
use App\Models\Equipo;
use App\Models\Evento;
use App\Models\CategoriaEvento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Closure;

class MisInscripcionesResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Mis Inscripciones';

    protected static ?string $navigationGroup = 'Mis Inscripciones';

    protected static ?string $modelLabel = 'Inscripción';

    protected static ?string $pluralModelLabel = 'Inscripciones';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        
        return $form
            ->schema([
                Forms\Components\Section::make('Inscripción a Competencia')
                    ->schema([
                        Forms\Components\Select::make('evento_id')
                            ->label('Evento')
                            ->options(
                                Evento::where('publicado', true)
                                    ->where('estado', 'abierto')
                                    ->where('inicio_inscripciones', '<=', now())
                                    ->where('fin_inscripciones', '>=', now())
                                    ->pluck('nombre', 'id')
                            )
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->afterStateUpdated(function (callable $set) {
                                $set('categoria_evento_id', null);
                                $set('fecha_evento_id', null);
                                $set('equipo_id', null);
                                $set('robot_id', null);
                            }),
                            
                        Forms\Components\Select::make('fecha_evento_id')
                            ->label('Fecha del Evento')
                            ->options(function (callable $get) {
                                $eventoId = $get('evento_id');
                                
                                if (!$eventoId) {
                                    return [];
                                }
                                
                                return \App\Models\FechaEvento::where('evento_id', $eventoId)
                                    ->where('activo', true)
                                    ->get()
                                    ->mapWithKeys(function ($fecha) {
                                        $inicio = \Carbon\Carbon::parse($fecha->fecha_inicio)->format('d/m/Y H:i');
                                        $fin = \Carbon\Carbon::parse($fecha->fecha_fin)->format('d/m/Y H:i');
                                        return [
                                            $fecha->id => "{$fecha->nombre} - {$inicio} a {$fin}"
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('categoria_evento_id', null);
                                $set('equipo_id', null);
                                $set('robot_id', null);
                            })
                            ->disabled(fn (callable $get) => !$get('evento_id')),
                            
                        Forms\Components\Select::make('categoria_evento_id')
                            ->label('Categoría')
                            ->options(function (callable $get) {
                                $eventoId = $get('evento_id');
                                $fechaEventoId = $get('fecha_evento_id');
                                
                                if (!$eventoId || !$fechaEventoId) {
                                    return [];
                                }
                                
                                // Crear la consulta base para categorías del evento
                                $query = CategoriaEvento::where('evento_id', $eventoId)
                                    ->where('activo', true)
                                    ->where('inscripciones_abiertas', true);
                                
                                // Si hay una fecha seleccionada, filtrar por esa fecha
                                if ($fechaEventoId) {
                                    $query->where('fecha_evento_id', $fechaEventoId);
                                }
                                
                                // Obtener las categorías filtradas
                                return $query->with(['categoria'])
                                    ->get()
                                    ->mapWithKeys(function ($categoriaEvento) {
                                        return [
                                            $categoriaEvento->id => "{$categoriaEvento->categoria->nombre}"
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('equipo_id', null);
                                $set('robot_id', null);
                            })
                            ->disabled(fn (callable $get) => !$get('evento_id') || !$get('fecha_evento_id')),

                        Forms\Components\Select::make('equipo_id')
                            ->label('Equipo')
                            ->relationship(
                                name: 'equipo',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->where('user_id', Auth::id())
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('robot_id', null))
                            ->disabled(fn (callable $get) => !$get('categoria_evento_id'))
                            // Validar que no exista una inscripción duplicada con exactamente los mismos valores
                            ->rules([
                                function (callable $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $eventoId = $get('evento_id');
                                        $fechaEventoId = $get('fecha_evento_id');
                                        $categoriaEventoId = $get('categoria_evento_id');
                                        $equipoId = $value;
                                        
                                        // Si falta alguno de estos valores, no validar
                                        if (!$eventoId || !$fechaEventoId || !$categoriaEventoId || !$equipoId) {
                                            return;
                                        }
                                        
                                        // Verificar si ya existe una inscripción con exactamente la misma combinación de valores
                                        $existeInscripcion = Registration::where('equipo_id', $equipoId)
                                            ->where('evento_id', $eventoId)
                                            ->where('fecha_evento_id', $fechaEventoId)
                                            ->where('categoria_evento_id', $categoriaEventoId)
                                            ->exists();
                                        
                                        if ($existeInscripcion) {
                                            $fail("Ya existe una inscripción para este equipo en esta categoría y fecha específica.");
                                        }
                                    };
                                }
                            ]),
                            
                        Forms\Components\Select::make('robot_id')
                            ->label('Robots')
                            ->multiple()
                            ->options(function (callable $get) use ($user) {
                                $equipoId = $get('equipo_id');
                                $categoriaEventoId = $get('categoria_evento_id');
                                
                                if (!$equipoId || !$categoriaEventoId) {
                                    return [];
                                }
                                
                                // Obtener la categoría del evento
                                $categoriaEvento = CategoriaEvento::with('categoria')->find($categoriaEventoId);
                                if (!$categoriaEvento || !$categoriaEvento->categoria) {
                                    return [];
                                }
                                
                                $nombreCategoria = $categoriaEvento->categoria->nombre;
                                
                                // Filtrar robots por categoría
                                $robots = Robot::where('equipo_id', $equipoId)
                                    ->where(function ($query) use ($nombreCategoria) {
                                        $query->where('categoria', $nombreCategoria)
                                            ->orWhere('categoria', 'LIKE', '%' . $nombreCategoria . '%')
                                            ->orWhereHas('categoria', function ($q) use ($nombreCategoria) {
                                                $q->where('nombre', $nombreCategoria);
                                            });
                                    })
                                    ->get();
                                
                                // Si no se encuentran robots, intentar buscar por categoría_id
                                if ($robots->isEmpty() && $categoriaEvento->categoria_id) {
                                    $robots = Robot::where('equipo_id', $equipoId)
                                        ->where('categoria_id', $categoriaEvento->categoria_id)
                                        ->get();
                                }
                                
                                return $robots->mapWithKeys(function ($robot) {
                                    return [
                                        $robot->id => "{$robot->nombre}"
                                    ];
                                })->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->visible(true)
                            ->disabled(fn (callable $get) => !$get('equipo_id') || !$get('categoria_evento_id'))
                            ->afterStateHydrated(function ($component, $state) {
                                // Si el estado es un JSON string, convertirlo a array
                                if (is_string($state) && is_array(json_decode($state, true))) {
                                    $component->state(json_decode($state, true));
                                }
                            }),
                        
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'approved' => 'Aprobada',
                                'rejected' => 'Rechazada',
                            ])
                            ->default('pending')
                            ->disabled()
                            ->dehydrated()
                            ->hidden(),
                        
                        Forms\Components\DatePicker::make('registration_date')
                            ->label('Fecha de registro')
                            ->default(now())
                            ->required()
                            ->hidden(),
                            
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->hidden(),
                            
                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id())
                            ->required()
                            ->dehydrated(true),

                        Forms\Components\Checkbox::make('terms')
                            ->label('Acepto los términos y condiciones')
                            ->required()
                            ->rules(['accepted'])
                            ->helperText('Al inscribirme, confirmo que mi robot cumple con los requisitos de la competencia y me comprometo a seguir las reglas del evento.'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('evento.nombre')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fechaEvento.nombre')
                    ->label('Fecha')
                    ->description(fn($record) => $record->fechaEvento ? \Carbon\Carbon::parse($record->fechaEvento->fecha_inicio)->format('d/m/Y') : '')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoriaEvento.categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipo.nombre')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('robot_id')
                    ->label('Robots')
                    ->formatStateUsing(function ($state, $record) {
                        // Intentar obtener los robots participantes de la inscripción de evento
                        if ($record->inscripcion_evento_id) {
                            $inscripcionEvento = \App\Models\InscripcionEvento::find($record->inscripcion_evento_id);
                            
                            if ($inscripcionEvento && $inscripcionEvento->robots_participantes) {
                                // Obtener los IDs de los robots participantes
                                $robotIds = collect($inscripcionEvento->robots_participantes)
                                    ->pluck('id')
                                    ->toArray();
                                
                                if (!empty($robotIds)) {
                                    // Obtener los nombres de los robots
                                    $robotNames = Robot::whereIn('id', $robotIds)
                                        ->pluck('nombre')
                                        ->toArray();
                                    
                                    if (!empty($robotNames)) {
                                        return implode(', ', $robotNames);
                                    }
                                }
                            }
                        }
                        
                        // Si no hay datos en robots_participantes, mostrar el robot asociado al registro
                        if (is_numeric($state)) {
                            $robot = Robot::find($state);
                            return $robot ? $robot->nombre : 'Robot #' . $state;
                        }
                        
                        return 'Sin robot asignado';
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('registration_date')
                    ->label('Fecha de registro')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc') // Ordenar por fecha de creación descendente
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ]),
                Tables\Filters\SelectFilter::make('evento_id')
                    ->label('Evento')
                    ->relationship('evento', 'nombre'),
                Tables\Filters\SelectFilter::make('equipo_id')
                    ->label('Equipo')
                    ->relationship('equipo', 'nombre'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Registration $record): bool => 
                        in_array($record->status, ['pending', 'rejected']) ||
                        ($record->inscripcionEvento && in_array($record->inscripcionEvento->estado, ['pendiente', 'rechazada']))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Registration $record): bool => 
                        in_array($record->status, ['pending', 'rejected']) ||
                        ($record->inscripcionEvento && in_array($record->inscripcionEvento->estado, ['pendiente', 'rechazada']))
                    ),
                Tables\Actions\Action::make('ver_detalles')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->url(fn (Registration $record): string => route('filament.competitor.resources.mis-inscripciones.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->groupedBulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function (?\Illuminate\Database\Eloquent\Collection $records = null): bool {
                            if (!$records) {
                                return false;
                            }
                            return $records->every(fn (Registration $record): bool => 
                                in_array($record->status, ['pending', 'rejected']) ||
                                ($record->inscripcionEvento && in_array($record->inscripcionEvento->estado, ['pendiente', 'rechazada']))
                            );
                        }),
                ]),
            ])
            ->defaultGroup('equipo.nombre');
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
            'index' => Pages\ListMisInscripciones::route('/'),
            'create' => Pages\CreateMisInscripciones::route('/create'),
            'edit' => Pages\EditMisInscripciones::route('/{record}/edit'),
        ];
    }
    
    // Solo mostrar inscripciones del usuario autenticado
    public static function getEloquentQuery(): Builder
    {
        $userId = Auth::id();
        
        // Log para depuración
        \Illuminate\Support\Facades\Log::info('Filtrando inscripciones para usuario: ' . $userId);
        
        $baseQuery = parent::getEloquentQuery();
        
        // Verificar si hay registros sin filtrar
        $totalRegistros = $baseQuery->count();
        \Illuminate\Support\Facades\Log::info('Total de registros sin filtrar: ' . $totalRegistros);
        
        // Ver IDs de algunos registros para depuración
        $algunosIds = $baseQuery->limit(5)->pluck('id')->toArray();
        \Illuminate\Support\Facades\Log::info('Algunos IDs de registros: ' . implode(', ', $algunosIds ?: ['ninguno']));
        
        $query = $baseQuery->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('equipo', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->with(['evento', 'categoriaEvento.categoria', 'fechaEvento', 'equipo', 'inscripcionEvento']);
        
        // Log el conteo de resultados para depuración
        $count = $query->count();
        \Illuminate\Support\Facades\Log::info('Total de inscripciones encontradas para el usuario: ' . $count);
        
        if ($count === 0) {
            // Si no se encuentran registros, verificar si hay registros en la base de datos para este usuario
            $totalUserRegistrations = parent::getEloquentQuery()
                ->where('user_id', $userId)
                ->count();
            
            \Illuminate\Support\Facades\Log::info('Total de inscripciones con user_id=' . $userId . ': ' . $totalUserRegistrations);
            
            // Verificar si hay inscripciones a través de equipos
            $equipoIds = Equipo::where('user_id', $userId)->pluck('id')->toArray();
            $totalEquipoRegistrations = parent::getEloquentQuery()
                ->whereIn('equipo_id', $equipoIds)
                ->count();
            
            \Illuminate\Support\Facades\Log::info('Total de inscripciones a través de equipos del usuario: ' . $totalEquipoRegistrations);
            \Illuminate\Support\Facades\Log::info('IDs de equipos: ' . implode(', ', $equipoIds ?: ['ninguno']));
            
            // Buscar las últimas inscripciones creadas en general
            $ultimasInscripciones = parent::getEloquentQuery()
                ->latest()
                ->limit(5)
                ->get(['id', 'user_id', 'equipo_id', 'evento_id', 'created_at']);
                
            \Illuminate\Support\Facades\Log::info('Últimas inscripciones creadas: ' . $ultimasInscripciones->toJson());
        }
        
        return $query;
    }
    
    /**
     * Procesar los datos del formulario antes de crear el registro
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // La lógica se ha trasladado al controlador de la página CreateMisInscripciones
        // para evitar conflictos y duplicación

        // Aseguramos que el user_id esté establecido
        $data['user_id'] = Auth::id();
        
        return $data;
    }

    /**
     * Boot es un método que se llama cuando se está inicializando el recurso
     */
    public static function boot()
    {
        parent::boot();
        
        // Verificar si hay registros en la tabla
        try {
            $count = Registration::count();
            \Illuminate\Support\Facades\Log::info('Total de registros en la tabla Registration: ' . $count);
            
            if ($count > 0) {
                // Obtener algunos registros para depuración
                $ultimosRegistros = Registration::latest()->limit(5)->get();
                \Illuminate\Support\Facades\Log::info('Últimos registros: ' . $ultimosRegistros->toJson());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al verificar registros: ' . $e->getMessage());
        }
    }
}
