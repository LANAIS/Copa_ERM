<?php

namespace App\Filament\Pages;

use App\Models\CountdownConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class CountdownSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Cuenta Atrás';

    protected static ?string $title = 'Configuración de la Cuenta Atrás';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationGroup = 'Configuraciones';

    protected static string $view = 'filament.pages.countdown-config';

    // Variables necesarias para la vista
    public $targetDate;

    public function mount(): void
    {
        $config = CountdownConfig::where('key', 'target_date')->first();
        $this->targetDate = $config ? $config->value : now()->addDays(75)->format('Y-m-d\TH:i');
    }

    // Acción para actualizar la fecha
    public function submitForm($date)
    {
        CountdownConfig::setTargetDate($date);

        Notification::make()
            ->title('Fecha de cuenta atrás actualizada correctamente')
            ->success()
            ->send();

        $this->targetDate = $date;
        
        // Emitir evento para recargar la página
        $this->dispatch('dateUpdated');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateDate')
                ->label('Actualizar Fecha')
                ->modalHeading('Configurar Fecha de Cuenta Atrás')
                ->modalDescription('Establece la fecha objetivo para la cuenta atrás que se mostrará en la página principal.')
                ->form([
                    Forms\Components\DateTimePicker::make('target_date')
                        ->label('Fecha Objetivo')
                        ->required()
                        ->seconds(false)
                        ->default(function () {
                            return $this->targetDate;
                        }),
                ])
                ->action(function (array $data): void {
                    $this->submitForm($data['target_date']);
                }),
        ];
    }
} 