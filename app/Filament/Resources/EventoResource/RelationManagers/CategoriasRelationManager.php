<?php

namespace App\Filament\Resources\EventoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriasRelationManager extends RelationManager
{
    protected static string $relationship = 'categorias';

    protected static ?string $recordTitleAttribute = 'nombre';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nombre')
                    ->required(),
                Forms\Components\TextInput::make('precio_inscripcion')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('fecha_evento_id')
                    ->relationship('fechaEvento', 'nombre')
                    ->required(),
                Forms\Components\Toggle::make('activo')
                    ->default(true),
                Forms\Components\Toggle::make('inscripciones_abiertas')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoria.nombre'),
                Tables\Columns\TextColumn::make('fechaEvento.nombre'),
                Tables\Columns\TextColumn::make('precio_inscripcion')
                    ->money('MXN'),
                Tables\Columns\ToggleColumn::make('activo'),
                Tables\Columns\ToggleColumn::make('inscripciones_abiertas'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
} 