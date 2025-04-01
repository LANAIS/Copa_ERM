<?php

namespace App\Filament\Resources\FechaEventoResource\RelationManagers;

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

    protected static ?string $recordTitleAttribute = 'categoria.nombre';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                Forms\Components\Select::make('estado_competencia')
                    ->label('Estado de la Competencia')
                    ->options([
                        'creada' => 'Creada',
                        'inscripciones' => 'Inscripciones',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                    ])
                    ->default('creada')
                    ->required(),
                
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                
                Forms\Components\Toggle::make('inscripciones_abiertas')
                    ->label('Inscripciones Abiertas')
                    ->default(false),
                
                Forms\Components\Textarea::make('reglas_especificas')
                    ->label('Reglas Específicas')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('categoria.nombre')
            ->columns([
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('categoria.modalidad')
                    ->label('Modalidad')
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
                    }),
                
                Tables\Columns\IconColumn::make('inscripciones_abiertas')
                    ->label('Inscripciones')
                    ->boolean()
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('inscritos')
                    ->label('Inscritos')
                    ->numeric()
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_competencia')
                    ->label('Estado')
                    ->options([
                        'creada' => 'Creada',
                        'inscripciones' => 'Inscripciones',
                        'homologacion' => 'Homologación',
                        'armado_llaves' => 'Armado de Llaves',
                        'en_curso' => 'En Curso',
                        'finalizada' => 'Finalizada',
                    ]),
                
                Tables\Filters\Filter::make('inscripciones_abiertas')
                    ->label('Con inscripciones abiertas')
                    ->query(fn (Builder $query) => $query->where('inscripciones_abiertas', true)),
                
                Tables\Filters\Filter::make('activo')
                    ->label('Solo activas')
                    ->query(fn (Builder $query) => $query->where('activo', true)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['evento_id'] = $this->ownerRecord->evento_id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activar')
                        ->label('Activar seleccionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['activo' => true]);
                            }
                        }),
                        
                    Tables\Actions\BulkAction::make('desactivar')
                        ->label('Desactivar seleccionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['activo' => false]);
                            }
                        }),
                        
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
