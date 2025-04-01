<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LlaveResource\Pages;
use App\Models\Llave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class LlaveResource extends Resource
{
    protected static ?string $model = Llave::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationGroup = 'Torneos';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categoria_evento_id')
                    ->relationship('categoriaEvento', 'id')
                    ->label('Categoría Evento')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('tipo_fixture')
                    ->options([
                        'eliminacion_directa' => 'Eliminación Directa (Single Elimination)',
                        'eliminacion_doble' => 'Eliminación Doble (Double Elimination)',
                        'todos_contra_todos' => 'Todos contra Todos (Round Robin)',
                        'suizo' => 'Sistema Suizo (Swiss)',
                        'grupos' => 'Fase de Grupos',
                        'fase_grupos_eliminacion' => 'Fase de Grupos + Eliminación',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('usar_cabezas_serie')
                    ->label('Usar cabezas de serie (seeding)'),
                Forms\Components\Select::make('estado_torneo')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'en_curso' => 'En curso',
                        'pausado' => 'Pausado',
                        'finalizado' => 'Finalizado',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('finalizado')
                    ->label('Finalizado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoriaEvento.categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_fixture')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'eliminacion_directa' => 'Eliminación Directa',
                        'eliminacion_doble' => 'Eliminación Doble',
                        'todos_contra_todos' => 'Todos contra Todos',
                        'suizo' => 'Sistema Suizo',
                        'grupos' => 'Fase de Grupos',
                        'fase_grupos_eliminacion' => 'Fase de Grupos + Eliminación',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_torneo')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'en_curso' => 'success',
                        'pausado' => 'warning',
                        'finalizado' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('finalizado')
                    ->label('Finalizado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Llave $record): string => "/admin/brackets/{$record->id}")
                    ->openUrlInNewTab(),
                Action::make('administrar')
                    ->label('Administrar')
                    ->icon('heroicon-o-cog')
                    ->url(fn (Llave $record): string => "/admin/bracket-admin/{$record->id}")
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListLlaves::route('/'),
            'create' => Pages\CreateLlave::route('/create'),
            'edit' => Pages\EditLlave::route('/{record}/edit'),
        ];
    }
} 