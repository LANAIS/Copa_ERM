<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventDateResource\Pages;
use App\Models\EventDate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventDateResource extends Resource
{
    protected static ?string $model = EventDate::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Fechas de Eventos';

    protected static ?string $modelLabel = 'Fecha de Evento';

    protected static ?string $pluralModelLabel = 'Fechas de Eventos';

    protected static ?int $navigationSort = 91;

    protected static ?string $navigationGroup = 'Configuraciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->placeholder('Ej: Fecha 1')
                            ->maxLength(255)
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('location')
                            ->label('Localidad')
                            ->required()
                            ->placeholder('Ej: Posadas')
                            ->maxLength(255)
                            ->columnSpan(1),
                            
                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                            
                        Forms\Components\Toggle::make('is_final')
                            ->label('¿Es la fecha final?')
                            ->default(false)
                            ->helperText('Si es la fecha final, se mostrará como "Fecha Final" en lugar del nombre')
                            ->columnSpan(1),
                    ]),
                    
                Forms\Components\TagsInput::make('categories')
                    ->label('Categorías')
                    ->placeholder('Agrega una categoría y presiona Enter')
                    ->helperText('Ingresa cada categoría y presiona Enter para añadirla')
                    ->suggestions([
                        'Sumo',
                        'Seguidor de Línea',
                        'Desafío Creativo',
                        'Programación',
                        'Innovación',
                        'Rescate',
                        'Laberinto',
                        'Todas las categorías',
                    ]),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('order')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Las fechas se ordenarán por este número y luego por fecha')
                            ->columnSpan(1),
                            
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Solo se mostrarán las fechas activas en la página principal')
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->formatStateUsing(fn (EventDate $record) => $record->display_name)
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('location')
                    ->label('Localidad')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('formatted_date')
                    ->label('Fecha')
                    ->sortable(['date']),
                    
                Tables\Columns\TagsColumn::make('categories')
                    ->label('Categorías')
                    ->separator(' ')
                    ->limitList(2),
                    
                Tables\Columns\IconColumn::make('is_final')
                    ->label('Fecha Final')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_final')
                    ->label('Tipo')
                    ->options([
                        '0' => 'Fechas Regulares',
                        '1' => 'Fechas Finales',
                    ]),
                    
                Tables\Filters\Filter::make('active')
                    ->label('Solo Activas')
                    ->query(fn (Builder $query) => $query->where('active', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activateBulk')
                        ->label('Activar seleccionadas')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (EventDate $record) => $record->update(['active' => true])),
                    Tables\Actions\BulkAction::make('deactivateBulk')
                        ->label('Desactivar seleccionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (EventDate $record) => $record->update(['active' => false])),
                ]),
            ])
            ->reorderable('order')
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEventDates::route('/'),
        ];
    }
} 