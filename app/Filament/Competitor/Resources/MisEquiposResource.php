<?php

namespace App\Filament\Competitor\Resources;

use App\Models\Equipo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MisEquiposResource extends Resource
{
    protected static ?string $model = Equipo::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Mis Equipos';

    protected static ?string $navigationGroup = 'Mis Equipos';

    protected static ?string $modelLabel = 'Equipo';

    protected static ?string $pluralModelLabel = 'Equipos';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Equipo')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Equipo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('institucion')
                            ->label('Institución')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ciudad')
                            ->label('Ciudad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pais')
                            ->label('País')
                            ->default('Argentina')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del Equipo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('institucion')
                    ->label('Institución')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ciudad')
                    ->label('Ciudad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('robots_count')
                    ->label('Robots')
                    ->counts('robots')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Competitor\Resources\MisEquiposResource\Pages\ListMisEquipos::route('/'),
            'create' => \App\Filament\Competitor\Resources\MisEquiposResource\Pages\CreateMisEquipos::route('/create'),
            'view' => \App\Filament\Competitor\Resources\MisEquiposResource\Pages\ViewMisEquipos::route('/{record}'),
            'edit' => \App\Filament\Competitor\Resources\MisEquiposResource\Pages\EditMisEquipos::route('/{record}/edit'),
        ];
    }

    // Solo mostrar equipos del usuario autenticado
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
} 