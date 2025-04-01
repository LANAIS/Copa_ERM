<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaEventoResource\Pages;
use App\Models\CategoriaEvento;
use App\Models\Categoria;
use App\Models\Evento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class CategoriaEventoResource extends Resource
{
    protected static ?string $model = CategoriaEvento::class;

    protected static ?string $navigationGroup = 'Competencia';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('evento_id')
                    ->label('Evento')
                    ->options(Evento::pluck('nombre', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('fecha_evento_id', null)),
                
                Forms\Components\Select::make('fecha_evento_id')
                    ->label('Fecha del Evento')
                    ->options(function (callable $get) {
                        $eventoId = $get('evento_id');
                        if (!$eventoId) {
                            return [];
                        }
                        
                        return \App\Models\FechaEvento::where('evento_id', $eventoId)
                            ->where('activo', true)
                            ->orderBy('fecha_inicio', 'asc')
                            ->pluck('nombre', 'id');
                    })
                    ->searchable()
                    ->nullable(),
                
                Forms\Components\Select::make('categoria_id')
                    ->label('Categoría')
                    ->options(Categoria::where('activo', true)->pluck('nombre', 'id'))
                    ->required()
                    ->searchable(),
                
                Forms\Components\Textarea::make('reglas_especificas')
                    ->label('Reglas Específicas')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('requisitos')
                    ->label('Requisitos')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('participantes_min')
                    ->label('Mínimo de Participantes')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
                
                Forms\Components\TextInput::make('participantes_max')
                    ->label('Máximo de Participantes')
                    ->numeric()
                    ->default(10)
                    ->minValue(1)
                    ->required(),
                
                Forms\Components\TextInput::make('cupo_limite')
                    ->label('Límite de Cupo')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Dejar vacío para cupo ilimitado'),
                
                Forms\Components\TextInput::make('inscritos')
                    ->label('Inscritos')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                
                Forms\Components\TextInput::make('precio_inscripcion')
                    ->label('Precio de Inscripción')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->default(0)
                    ->minValue(0),
                
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                
                Forms\Components\Toggle::make('inscripciones_abiertas')
                    ->label('Inscripciones Abiertas')
                    ->default(false),
                
                Forms\Components\Select::make('estado_competencia')
                    ->label('Estado de la Competencia')
                    ->options([
                        'creada' => 'Creada',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                    ])
                    ->default('creada')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->size('lg')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('evento.nombre')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('fecha_evento.nombre')
                    ->label('Fecha')
                    ->description(fn ($record) => $record->fecha_evento ? $record->fecha_evento->fecha_inicio->format('d/m/Y') : null)
                    ->searchable()
                    ->sortable()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('categoria.modalidad')
                    ->label('Modalidad')
                    ->formatStateUsing(function ($record) {
                        $modalidad = $record->categoria->modalidad ?? $record->categoria->tipo ?? '';
                        return empty($modalidad) ? 'No especificada' : $modalidad;
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('estado_competencia')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'creada' => 'gray',
                        'inscripciones' => 'blue',
                        'homologacion' => 'warning',
                        'armado_llaves' => 'info',
                        'en_curso' => 'success',
                        'finalizada' => 'purple',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'creada' => 'Creada',
                        'inscripciones' => 'Inscripciones',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                        default => ucfirst($state),
                    }),
                
                Tables\Columns\TextColumn::make('llave.estado_torneo')
                    ->label('Estado Bracket')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->llave ? match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_curso' => 'En Curso',
                        'finalizado' => 'Finalizado',
                        default => ucfirst($state),
                    } : 'Sin Bracket')
                    ->color(fn ($state, $record) => $record->llave ? match ($state) {
                        'pendiente' => 'gray',
                        'en_curso' => 'success',
                        'finalizado' => 'purple',
                        default => 'gray',
                    } : 'danger'),
                
                Tables\Columns\TextColumn::make('inscritos')
                    ->label('Robots Inscritos')
                    ->numeric()
                    ->badge()
                    ->alignCenter()
                    ->color('info')
                    ->size('md'),
                
                Tables\Columns\IconColumn::make('inscripciones_abiertas')
                    ->label('Inscripciones')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('evento')
                    ->relationship('evento', 'nombre'),
                
                Tables\Filters\SelectFilter::make('fecha_evento')
                    ->relationship('fecha_evento', 'nombre'),
                
                Tables\Filters\SelectFilter::make('estado_competencia')
                    ->label('Estado de la Competencia')
                    ->options([
                        'creada' => 'Creada',
                        'inscripciones' => 'Inscripciones',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                    ]),
                
                Tables\Filters\Filter::make('inscripciones_abiertas')
                    ->label('Solo con inscripciones abiertas')
                    ->query(fn (Builder $query) => $query->where('inscripciones_abiertas', true)),
                
                Tables\Filters\Filter::make('con_llave')
                    ->label('Con bracket creado')
                    ->query(function (Builder $query) {
                        return $query->whereHas('llave');
                    }),
                
                Tables\Filters\Filter::make('sin_llave')
                    ->label('Sin bracket creado')
                    ->query(function (Builder $query) {
                        return $query->whereDoesntHave('llave');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                // Acciones agrupadas para gestión de estados
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('abrir_inscripciones')
                        ->label('Abrir Inscripciones')
                        ->icon('heroicon-o-pencil-square')
                        ->color('gray')
                        ->action(function (CategoriaEvento $record) {
                            try {
                                $record->update([
                                    'estado_competencia' => 'inscripciones',
                                    'inscripciones_abiertas' => true
                                ]);
                                
                                Notification::make()
                                    ->title('Inscripciones abiertas')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'creada'),
                        
                    Tables\Actions\Action::make('cerrar_inscripciones')
                        ->label('Cerrar Inscripciones')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->action(function (CategoriaEvento $record) {
                            try {
                                $record->update([
                                    'inscripciones_abiertas' => false
                                ]);
                                
                                Notification::make()
                                    ->title('Inscripciones cerradas')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (CategoriaEvento $record) => $record->inscripciones_abiertas),
                        
                    Tables\Actions\Action::make('iniciar_homologacion')
                        ->label('Iniciar Homologación')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('warning')
                        ->action(function (CategoriaEvento $record) {
                            try {
                                // Cerrar inscripciones
                                $record->update([
                                    'estado_competencia' => 'homologacion',
                                    'inscripciones_abiertas' => false
                                ]);
                                
                                // Crear entradas de homologación para cada robot inscrito
                                $record->crearHomologaciones();
                                
                                Notification::make()
                                    ->title('Homologación iniciada')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'inscripciones'),
                ])
                ->label('Cambiar Estado')
                ->icon('heroicon-m-arrow-path')
                ->color('primary')
                ->visible(fn (CategoriaEvento $record) => in_array($record->estado_competencia, ['creada', 'inscripciones', 'homologacion'])),
                
                // Acciones para homologación
                Tables\Actions\Action::make('Gestionar Homologaciones')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->url(fn (CategoriaEvento $record) => url('/admin/gestion-homologaciones?id=' . $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'homologacion')
                    ->tooltip('Gestionar homologaciones de robots'),
                Tables\Actions\Action::make('Finalizar Homologaciones')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (CategoriaEvento $record) {
                        if ($record->estado_competencia !== 'homologacion') {
                            Notification::make()
                                ->title('No se puede finalizar la homologación')
                                ->body('La categoría no está en etapa de homologación')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        try {
                            $record->estado_competencia = 'armado_llaves';
                            $record->save();
                            
                            Notification::make()
                                ->title('Homologaciones finalizadas')
                                ->body('La categoría ha pasado a la etapa de armado de llaves')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('No se pudo finalizar la homologación: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'homologacion')
                    ->tooltip('Finalizar etapa de homologación y pasar a armado de llaves'),
                
                // Acciones para bracket
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Ver Bracket')
                        ->icon('heroicon-o-chart-bar')
                        ->color('success')
                        ->url(fn (CategoriaEvento $record) => $record->llave ? "/admin/brackets/{$record->llave->id}" : null)
                        ->openUrlInNewTab()
                        ->visible(fn (CategoriaEvento $record) => $record->llave !== null)
                        ->tooltip('Ver bracket del torneo'),
                    
                    Tables\Actions\Action::make('Administrar Bracket')
                        ->icon('heroicon-o-cog')
                        ->color('warning')
                        ->url(fn (CategoriaEvento $record) => $record->llave ? "/admin/bracket-admin/{$record->llave->id}" : null)
                        ->openUrlInNewTab()
                        ->visible(fn (CategoriaEvento $record) => $record->llave !== null && in_array($record->estado_competencia, ['armado_llaves', 'en_curso']))
                        ->tooltip('Administrar bracket del torneo'),
                    
                    Tables\Actions\Action::make('Crear Bracket')
                        ->icon('heroicon-o-plus-circle')
                        ->color('primary')
                        ->action(function (CategoriaEvento $record) {
                            // Crear una llave para esta categoría de evento si no existe
                            if (!$record->llave) {
                                $llave = new \App\Models\Llave([
                                    'categoria_evento_id' => $record->id,
                                    'tipo_fixture' => \App\Models\Llave::TIPO_ELIMINACION_DIRECTA,
                                    'estructura' => [
                                        'total_equipos' => 0,
                                        'total_rondas' => 0,
                                        'tamano_llave' => 0,
                                        'total_enfrentamientos' => 0,
                                    ],
                                    'finalizado' => false,
                                    'estado_torneo' => \App\Models\Llave::ESTADO_PENDIENTE,
                                ]);
                                $llave->save();
                                
                                // Actualizar estado de la competencia
                                $record->update(['estado_competencia' => 'armado_llaves']);
                                
                                Notification::make()
                                    ->title('Bracket creado')
                                    ->success()
                                    ->send();
                                
                                // Redirigir al usuario a la página de administración del bracket
                                redirect("/admin/bracket-admin/{$llave->id}");
                            } else {
                                Notification::make()
                                    ->title('Ya existe un bracket')
                                    ->warning()
                                    ->send();
                                
                                // Redirigir al bracket existente
                                redirect("/admin/bracket-admin/{$record->llave->id}");
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'armado_llaves' && !$record->llave),
                        
                    Tables\Actions\Action::make('iniciar_competencia')
                        ->label('Iniciar Competencia')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function (CategoriaEvento $record) {
                            if (!$record->llave) {
                                Notification::make()
                                    ->title('No hay bracket')
                                    ->body('Debe crear un bracket antes de iniciar la competencia')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            try {
                                $record->update(['estado_competencia' => 'en_curso']);
                                
                                // Iniciar el bracket también
                                $record->llave->update(['estado_torneo' => 'en_curso']);
                                
                                Notification::make()
                                    ->title('Competencia iniciada')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'armado_llaves' && $record->llave),
                ])
                ->label('Bracket')
                ->icon('heroicon-m-trophy')
                ->color('info')
                ->visible(fn (CategoriaEvento $record) => in_array($record->estado_competencia, ['armado_llaves', 'en_curso', 'finalizada'])),
                
                Tables\Actions\Action::make('finalizar_competencia')
                    ->label('Finalizar Competencia')
                    ->icon('heroicon-o-flag')
                    ->color('purple')
                    ->action(function (CategoriaEvento $record) {
                        try {
                            $record->update(['estado_competencia' => 'finalizada']);
                            
                            // Finalizar el bracket también
                            if ($record->llave) {
                                $record->llave->update([
                                    'estado_torneo' => 'finalizado',
                                    'finalizado' => true
                                ]);
                            }
                            
                            Notification::make()
                                ->title('Competencia finalizada')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'en_curso'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaEventos::route('/'),
            'create' => Pages\CreateCategoriaEvento::route('/create'),
            'edit' => Pages\EditCategoriaEvento::route('/{record}/edit'),
        ];
    }
}
 