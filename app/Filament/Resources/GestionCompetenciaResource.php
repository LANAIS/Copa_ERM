<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GestionCompetenciaResource\Pages;
use App\Models\CategoriaEvento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Widgets\CompetenciaStatsWidget;
use App\Filament\Widgets\HomologacionesPendientesWidget;
use App\Filament\Widgets\CompetenciasEnCursoWidget;

class GestionCompetenciaResource extends Resource
{
    protected static ?string $model = CategoriaEvento::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Gestión de Competencias';

    protected static ?string $navigationGroup = 'Competencia';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'Gestión de Competencia';

    protected static ?string $pluralModelLabel = 'Gestión de Competencias';

    /**
     * Sobrescribe la URL de navegación para este recurso
     */
    public static function getNavigationUrl(): string
    {
        return '/admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('evento_id')
                    ->relationship('evento', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('estado_competencia')
                    ->options([
                        'creada' => 'Creada',
                        'inscripciones' => 'Inscripciones',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('reglas_especificas')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Gestión de Competencias')
            ->description('Administre el flujo completo de competencias desde inscripciones hasta resultados')
            ->headerActions([
                Tables\Actions\Action::make('dashboard')
                    ->label('Dashboard')
                    ->icon('heroicon-o-home')
                    ->url(route('admin.dashboard'))
            ])
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
                    ->color('gray')
                    ->size('md'),
                Tables\Columns\TextColumn::make('modalidad')
                    ->searchable()
                    ->sortable()
                    ->color('gray')
                    ->size('md'),
                Tables\Columns\TextColumn::make('estado_competencia')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inscripciones' => 'gray',
                        'homologacion' => 'warning',
                        'armado_llaves' => 'info',
                        'en_curso' => 'success',
                        'finalizada' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'inscripciones' => 'Inscripciones',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                        default => ucfirst($state),
                    })
                    ->sortable()
                    ->size('md'),
                Tables\Columns\TextColumn::make('llave.estado_torneo')
                    ->label('Estado Bracket')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? match ($state) {
                        'pendiente' => 'Pendiente',
                        'en_curso' => 'En Curso',
                        'finalizado' => 'Finalizado',
                        default => $state,
                    } : 'Sin Bracket')
                    ->color(fn ($state) => $state ? match ($state) {
                        'pendiente' => 'gray',
                        'en_curso' => 'success',
                        'finalizado' => 'purple',
                        default => 'gray',
                    } : 'danger')
                    ->sortable()
                    ->size('md'),
                Tables\Columns\TextColumn::make('inscritos')
                    ->label('Robots Inscritos')
                    ->sortable()
                    ->size('md')
                    ->alignCenter(),
            ])
            ->defaultSort('estado_competencia')
            ->groups([
                'estado_competencia',
                'evento.nombre',
            ])
            ->defaultGroup('estado_competencia')
            ->filters([
                SelectFilter::make('estado_competencia')
                    ->label('Estado')
                    ->options([
                        'inscripciones' => 'Inscripciones',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                    ])
                    ->multiple(),
                SelectFilter::make('evento')
                    ->relationship('evento', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('categoria')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('llave.estado_torneo')
                    ->label('Estado Bracket')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_curso' => 'En Curso',
                        'finalizado' => 'Finalizado',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                // Acciones agrupadas para homologación
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('gestionar_homologaciones')
                        ->label('Gestionar Homologaciones')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('warning')
                        ->url(fn (CategoriaEvento $record) => url('/admin/gestion-homologaciones?id=' . $record->id))
                        ->openUrlInNewTab()
                        ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'homologacion')
                        ->tooltip('Gestionar homologaciones de robots'),
                    Tables\Actions\Action::make('finalizar_homologaciones')
                        ->label('Finalizar Homologaciones')
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
                ])
                ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'homologacion')
                ->label('Homologación')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('warning'),
                
                // Acciones agrupadas para brackets
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('ver_bracket')
                        ->label('Ver Bracket')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (CategoriaEvento $record) => $record->llave ? route('brackets.show', $record->llave->id) : null)
                        ->openUrlInNewTab()
                        ->visible(fn (CategoriaEvento $record) => $record->llave !== null)
                        ->tooltip('Ver bracket del torneo'),
                    Tables\Actions\Action::make('administrar_bracket')
                        ->label('Administrar Bracket')
                        ->icon('heroicon-o-cog')
                        ->color('warning')
                        ->url(fn (CategoriaEvento $record) => $record->llave ? route('admin.brackets.admin', $record->llave->id) : null)
                        ->openUrlInNewTab()
                        ->visible(fn (CategoriaEvento $record) => $record->llave !== null && in_array($record->estado_competencia, ['armado_llaves', 'en_curso']))
                        ->tooltip('Administrar bracket del torneo'),
                    Tables\Actions\Action::make('crear_bracket')
                        ->label('Crear Bracket')
                        ->icon('heroicon-o-plus-circle')
                        ->color('primary')
                        ->action(function (CategoriaEvento $record) {
                            if ($record->llave) {
                                Notification::make()
                                    ->title('Ya existe un bracket')
                                    ->body('Esta categoría ya tiene un bracket creado')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            if ($record->estado_competencia !== 'armado_llaves') {
                                Notification::make()
                                    ->title('No se puede crear bracket')
                                    ->body('La categoría no está en etapa de armado de llaves')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            try {
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
                                    'estado_torneo' => \App\Models\Llave::ESTADO_PENDIENTE
                                ]);
                                $llave->save();
                                
                                Notification::make()
                                    ->title('Bracket creado')
                                    ->body('Se ha creado un bracket para esta categoría')
                                    ->success()
                                    ->send();
                                    
                                // Redirigir a la página de administración del bracket
                                redirect()->route('admin.brackets.admin', $llave->id);
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se pudo crear el bracket: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (CategoriaEvento $record) => $record->llave === null && $record->estado_competencia === 'armado_llaves')
                        ->tooltip('Crear bracket del torneo'),
                    
                    Tables\Actions\Action::make('iniciar_competencia')
                        ->label('Iniciar Competencia')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function (CategoriaEvento $record) {
                            if ($record->estado_competencia !== 'armado_llaves') {
                                Notification::make()
                                    ->title('No se puede iniciar la competencia')
                                    ->body('La categoría no está en etapa de armado de llaves')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            if (!$record->llave) {
                                Notification::make()
                                    ->title('No se puede iniciar la competencia')
                                    ->body('Debe crear un bracket primero')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            try {
                                $record->estado_competencia = 'en_curso';
                                $record->save();
                                
                                // Iniciar el bracket también
                                $record->llave->estado_torneo = 'en_curso';
                                $record->llave->save();
                                
                                Notification::make()
                                    ->title('Competencia iniciada')
                                    ->body('La competencia ahora está en curso')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('No se pudo iniciar la competencia: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'armado_llaves' && $record->llave !== null)
                        ->tooltip('Iniciar la competencia'),
                ])
                ->visible(fn (CategoriaEvento $record) => in_array($record->estado_competencia, ['armado_llaves', 'en_curso', 'finalizada']))
                ->label('Bracket')
                ->icon('heroicon-m-trophy')
                ->color('info'),
                
                // Acción para finalizar competencia
                Tables\Actions\Action::make('finalizar_competencia')
                    ->label('Finalizar Competencia')
                    ->icon('heroicon-o-flag')
                    ->color('purple')
                    ->action(function (CategoriaEvento $record) {
                        if ($record->estado_competencia !== 'en_curso') {
                            Notification::make()
                                ->title('No se puede finalizar la competencia')
                                ->body('La categoría no está en curso')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        try {
                            $record->estado_competencia = 'finalizada';
                            $record->save();
                            
                            // Finalizar el bracket también
                            if ($record->llave) {
                                $record->llave->estado_torneo = 'finalizado';
                                $record->llave->finalizado = true;
                                $record->llave->save();
                            }
                            
                            Notification::make()
                                ->title('Competencia finalizada')
                                ->body('La competencia ha sido marcada como finalizada')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('No se pudo finalizar la competencia: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('¿Finalizar competencia?')
                    ->modalDescription('Esta acción no se puede deshacer. Se marcarán como finales todos los resultados actuales.')
                    ->modalIcon('heroicon-o-exclamation-circle')
                    ->visible(fn (CategoriaEvento $record) => $record->estado_competencia === 'en_curso')
                    ->tooltip('Finalizar competencia y establecer resultados definitivos'),
            ])
            ->bulkActions([
                //
            ])
            ->emptyStateActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CompetenciaStatsWidget::class,
            HomologacionesPendientesWidget::class,
            CompetenciasEnCursoWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGestionCompetencias::route('/'),
        ];
    }
}
