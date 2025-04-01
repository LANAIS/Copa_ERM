<?php

namespace App\Filament\Widgets;

use App\Models\Homologacion;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class HomologacionesPendientesWidget extends BaseWidget
{
    protected static ?string $heading = 'Homologaciones Pendientes';
    
    protected static ?string $pollingInterval = '15s'; 
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Homologacion::query()
                    ->where('estado', 'pendiente')
                    ->with(['robot.equipo', 'categoriaEvento.categoria', 'categoriaEvento.evento'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('robot.nombre')
                    ->label('Robot')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('robot.equipo.nombre')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoriaEvento.categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('categoriaEvento.evento.nombre')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Pendiente desde')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('gestionar')
                    ->label('Homologar')
                    ->icon('heroicon-s-clipboard-check')
                    ->color('warning')
                    ->url(fn ($record): string => url('/admin/gestion-homologaciones?id=' . $record->categoria_evento_id))
                    ->openUrlInNewTab(),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading('No hay homologaciones pendientes')
            ->emptyStateDescription('Las homologaciones pendientes aparecerán aquí')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }
} 