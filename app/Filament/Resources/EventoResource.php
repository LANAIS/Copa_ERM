<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventoResource\Pages;
use App\Filament\Resources\EventoResource\RelationManagers;
use App\Models\Evento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static ?string $navigationLabel = 'Eventos';

    protected static ?string $navigationGroup = 'Torneos';

    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Información del Evento')
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Evento::class, 'slug', ignoreRecord: true),
                                
                                Forms\Components\Textarea::make('descripcion')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                
                                Forms\Components\TextInput::make('lugar')
                                    ->maxLength(255),
                                
                                Forms\Components\FileUpload::make('banner')
                                    ->label('Banner')
                                    ->image()
                                    ->directory('eventos/banners')
                                    ->maxSize(5120)
                                    ->columnSpanFull(),
                            ]),
                        
                        Forms\Components\Section::make('Fechas y Estado')
                            ->schema([
                                Forms\Components\DateTimePicker::make('fecha_inicio')
                                    ->label('Fecha de inicio')
                                    ->required(),
                                
                                Forms\Components\DateTimePicker::make('fecha_fin')
                                    ->label('Fecha de fin')
                                    ->required()
                                    ->after('fecha_inicio'),
                                
                                Forms\Components\DateTimePicker::make('inicio_inscripciones')
                                    ->label('Inicio de inscripciones')
                                    ->required()
                                    ->before('fin_inscripciones'),
                                
                                Forms\Components\DateTimePicker::make('fin_inscripciones')
                                    ->label('Fin de inscripciones')
                                    ->required()
                                    ->before('fecha_inicio')
                                    ->after('inicio_inscripciones'),
                                
                                Forms\Components\Select::make('estado')
                                    ->options([
                                        'proximamente' => 'Próximamente',
                                        'abierto' => 'Abierto',
                                        'cerrado' => 'Cerrado',
                                        'finalizado' => 'Finalizado',
                                    ])
                                    ->default('proximamente')
                                    ->required(),
                                
                                Forms\Components\Toggle::make('publicado')
                                    ->label('Publicado')
                                    ->helperText('Al activar esta opción, el evento será visible para todos los usuarios')
                                    ->default(false),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Información Adicional')
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Creado')
                                    ->content(fn (Evento $record): ?string => $record->created_at?->diffForHumans()),
                                
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Última actualización')
                                    ->content(fn (Evento $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                            ->hidden(fn (?Evento $record) => $record === null),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner')
                    ->square()
                    ->label('Banner'),
                
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fin_inscripciones')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Cierre de Inscripciones'),
                
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'danger' => 'finalizado',
                        'warning' => 'cerrado',
                        'success' => 'abierto',
                        'secondary' => 'proximamente',
                    ]),
                
                Tables\Columns\IconColumn::make('publicado')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'proximamente' => 'Próximamente',
                        'abierto' => 'Abierto',
                        'cerrado' => 'Cerrado',
                        'finalizado' => 'Finalizado',
                    ]),
                
                Tables\Filters\Filter::make('activos')
                    ->label('Solo eventos publicados')
                    ->query(fn (Builder $query) => $query->where('publicado', true)),
                
                Tables\Filters\Filter::make('inscripciones_abiertas')
                    ->label('Inscripciones abiertas')
                    ->query(function (Builder $query) {
                        return $query
                            ->where('estado', 'abierto')
                            ->where('inicio_inscripciones', '<=', now())
                            ->where('fin_inscripciones', '>=', now());
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FechasRelationManager::class,
            RelationManagers\CategoriasRelationManager::class,
            RelationManagers\InscripcionesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
        ];
    }
}
