<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FechaEventoResource\Pages;
use App\Models\FechaEvento;
use App\Models\Evento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class FechaEventoResource extends Resource
{
    protected static ?string $model = FechaEvento::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Fechas de Eventos';

    protected static ?string $modelLabel = 'Fecha de Evento';

    protected static ?string $pluralModelLabel = 'Fechas de Eventos';

    protected static ?string $navigationGroup = 'Competencia';

    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('evento_id')
                    ->label('Evento')
                    ->options(Evento::pluck('nombre', 'id'))
                    ->required()
                    ->searchable()
                    ->columnSpan(2),
                
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                
                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                
                Forms\Components\DateTimePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->required()
                    ->columnSpan(1),
                
                Forms\Components\DateTimePicker::make('fecha_fin')
                    ->label('Fecha de Fin')
                    ->required()
                    ->columnSpan(1),
                
                Forms\Components\TextInput::make('lugar')
                    ->label('Lugar')
                    ->maxLength(255)
                    ->columnSpan(2),
                
                Forms\Components\TextInput::make('orden')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(1),
                
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true)
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('evento.nombre')
                    ->label('Evento')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('lugar')
                    ->label('Lugar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('evento')
                    ->relationship('evento', 'nombre'),
                
                Tables\Filters\Filter::make('activo')
                    ->label('Solo activas')
                    ->query(fn (Builder $query) => $query->where('activo', true)),
                
                Tables\Filters\Filter::make('futuras')
                    ->label('Solo fechas futuras')
                    ->query(fn (Builder $query) => $query->where('fecha_inicio', '>', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('cambiar_estado')
                    ->label(fn (FechaEvento $record): string => $record->activo ? 'Desactivar' : 'Activar')
                    ->icon(fn (FechaEvento $record): string => $record->activo ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (FechaEvento $record): string => $record->activo ? 'danger' : 'success')
                    ->action(function (FechaEvento $record) {
                        $record->activo = !$record->activo;
                        $record->save();
                        
                        $estado = $record->activo ? 'activada' : 'desactivada';
                        Notification::make()
                            ->title("Fecha {$estado}")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activar')
                        ->label('Activar seleccionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (!$record->activo) {
                                    $record->activo = true;
                                    $record->save();
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title("{$count} fechas activadas")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar seleccionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->activo) {
                                    $record->activo = false;
                                    $record->save();
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title("{$count} fechas desactivadas")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FechaEventoResource\RelationManagers\CategoriasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFechaEventos::route('/'),
            'create' => Pages\CreateFechaEvento::route('/create'),
            'edit' => Pages\EditFechaEvento::route('/{record}/edit'),
        ];
    }
} 