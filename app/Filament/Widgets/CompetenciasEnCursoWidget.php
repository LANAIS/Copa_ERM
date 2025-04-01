<?php

namespace App\Filament\Widgets;

use App\Models\CategoriaEvento;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CompetenciasEnCursoWidget extends BaseWidget
{
    protected static ?string $heading = 'Competencias en Curso';
    
    protected static ?string $pollingInterval = '15s';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CategoriaEvento::query()
                    ->where('estado_competencia', 'en_curso')
                    ->with(['categoria', 'evento', 'llave'])
                    ->latest()
            )
            ->recordClasses(fn () => 'hover:bg-success-50')
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
                Tables\Columns\TextColumn::make('inscritos')
                    ->label('Robots Participantes')
                    ->sortable()
                    ->badge()
                    ->color('success'),
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
                    } : 'danger'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actions([
                Tables\Actions\Action::make('ver_bracket')
                    ->label('Ver Bracket')
                    ->url(fn (CategoriaEvento $record): ?string => $record->llave ? route('brackets.show', $record->llave->id) : null)
                    ->icon('heroicon-m-chart-bar')
                    ->color('primary')
                    ->openUrlInNewTab()
                    ->visible(fn (CategoriaEvento $record): bool => $record->llave !== null),
                Tables\Actions\Action::make('administrar')
                    ->label('Administrar')
                    ->url(fn (CategoriaEvento $record): ?string => $record->llave ? route('admin.brackets.admin', $record->llave->id) : null)
                    ->icon('heroicon-m-cog')
                    ->color('warning')
                    ->openUrlInNewTab()
                    ->visible(fn (CategoriaEvento $record): bool => $record->llave !== null),
                Tables\Actions\Action::make('finalizar')
                    ->label('Finalizar')
                    ->icon('heroicon-m-flag')
                    ->color('purple')
                    ->action(function (CategoriaEvento $record): void {
                        $record->update(['estado_competencia' => 'finalizada']);
                        
                        if ($record->llave) {
                            $record->llave->update([
                                'estado_torneo' => 'finalizado',
                                'finalizado' => true
                            ]);
                        }
                        
                        $this->refresh();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('¿Finalizar competencia?')
                    ->modalDescription('Esto marcará la competencia como finalizada y establecerá los resultados actuales como definitivos.')
                    ->modalIcon('heroicon-o-exclamation-circle')
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading('No hay competencias en curso')
            ->emptyStateDescription('Las competencias en curso aparecerán aquí')
            ->emptyStateIcon('heroicon-o-play')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }
} 