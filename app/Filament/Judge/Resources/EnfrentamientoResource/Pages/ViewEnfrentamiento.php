<?php

namespace App\Filament\Judge\Resources\EnfrentamientoResource\Pages;

use App\Filament\Judge\Resources\EnfrentamientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use App\Models\Enfrentamiento;

class ViewEnfrentamiento extends ViewRecord
{
    protected static string $resource = EnfrentamientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('registrarResultado')
                ->label('Registrar Resultado')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->visible(fn () => 
                    !$this->record->tieneResultado() && 
                    !is_null($this->record->equipo1_id) && 
                    !is_null($this->record->equipo2_id)
                )
                ->form([
                    \Filament\Forms\Components\Grid::make()
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('equipo1_nombre')
                                ->label('Equipo 1')
                                ->content(fn () => 
                                    $this->record->equipo1->nombre ?? 'No asignado')
                                ->columnSpan(1),
                                
                            \Filament\Forms\Components\TextInput::make('puntaje_equipo1')
                                ->label('Puntos')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->columnSpan(1),
                                
                            \Filament\Forms\Components\Placeholder::make('separator')
                                ->label('')
                                ->content('VS')
                                ->columnSpan(1),
                                
                            \Filament\Forms\Components\TextInput::make('puntaje_equipo2')
                                ->label('Puntos')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->columnSpan(1),
                                
                            \Filament\Forms\Components\Placeholder::make('equipo2_nombre')
                                ->label('Equipo 2')
                                ->content(fn () => 
                                    $this->record->equipo2->nombre ?? 'No asignado')
                                ->columnSpan(1),
                        ])
                        ->columns(5),
                        
                    \Filament\Forms\Components\Select::make('ganador_id')
                        ->label('Seleccionar Ganador')
                        ->options(function () {
                            $equipos = [];
                            
                            if ($this->record->equipo1_id) {
                                $equipos[$this->record->equipo1_id] = $this->record->equipo1->nombre ?? 'Equipo 1';
                            }
                            
                            if ($this->record->equipo2_id) {
                                $equipos[$this->record->equipo2_id] = $this->record->equipo2->nombre ?? 'Equipo 2';
                            }
                            
                            return $equipos;
                        })
                        ->required()
                        ->helperText('Si hay empate, seleccione manualmente el ganador'),
                        
                    \Filament\Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->registrarResultado(
                        $data['puntaje_equipo1'], 
                        $data['puntaje_equipo2'], 
                        $data['ganador_id']
                    );
                    
                    if (isset($data['observaciones'])) {
                        $this->record->update(['observaciones' => $data['observaciones']]);
                    }
                    
                    Notification::make()
                        ->title('Resultado registrado correctamente')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
} 