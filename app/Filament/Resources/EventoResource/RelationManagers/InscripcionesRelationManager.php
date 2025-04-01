<?php

namespace App\Filament\Resources\EventoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InscripcionesRelationManager extends RelationManager
{
    protected static string $relationship = 'inscripciones';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('equipo_id')
                    ->relationship('equipo', 'nombre')
                    ->required(),
                Forms\Components\Select::make('categoria_evento_id')
                    ->relationship('categoriaEvento', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => optional($record->categoria)->nombre ?? 'Categoría #' . $record->id)
                    ->required(),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'pagada' => 'Pagada',
                        'homologada' => 'Homologada',
                        'participando' => 'Participando',
                        'finalizada' => 'Finalizada',
                        'rechazada' => 'Rechazada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('monto_pagado')
                    ->numeric(),
                Forms\Components\Textarea::make('notas_admin')
                    ->maxLength(1000),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipo.nombre'),
                Tables\Columns\TextColumn::make('categoriaEvento.categoria.nombre')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Participante'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => ['confirmada', 'pagada', 'homologada', 'participando'],
                        'info' => 'finalizada',
                        'danger' => ['rechazada', 'cancelada'],
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'pagada' => 'Pagada',
                        'homologada' => 'Homologada',
                        'participando' => 'Participando',
                        'finalizada' => 'Finalizada',
                        'rechazada' => 'Rechazada',
                        'cancelada' => 'Cancelada',
                    ]),
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