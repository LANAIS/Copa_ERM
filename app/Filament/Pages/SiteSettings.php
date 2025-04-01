<?php

namespace App\Filament\Pages;

use App\Models\SiteConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;

class SiteSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Configuración del Sitio';

    protected static ?string $title = 'Configuración del Sitio';

    protected static ?int $navigationSort = 91;

    protected static ?string $navigationGroup = 'Configuraciones';

    protected static string $view = 'filament.pages.site-settings';

    // Variables necesarias para la vista
    public $logoPath;

    public function mount(): void
    {
        $this->logoPath = SiteConfig::getLogo();
    }

    // Acción para actualizar el logo
    public function submitForm($logoPath)
    {
        // Si hay un logo anterior, eliminarlo
        $oldLogo = SiteConfig::getValue('site_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }
        
        // Guardar la ruta del nuevo logo (ya está en storage/public/temp)
        // Necesitamos moverlo a storage/public/logos
        $newPath = 'logos/' . basename($logoPath);
        if (Storage::disk('public')->exists($logoPath)) {
            // Si existe, copiamos el archivo a la nueva ubicación
            Storage::disk('public')->copy($logoPath, $newPath);
            // Y eliminamos el temporal
            Storage::disk('public')->delete($logoPath);
        }
        
        SiteConfig::setLogo($newPath);

        Notification::make()
            ->title('Logo actualizado correctamente')
            ->success()
            ->send();

        $this->logoPath = SiteConfig::getLogo();
        
        // Emitir evento para recargar la página
        $this->dispatch('logoUpdated');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateLogo')
                ->label('Actualizar Logo')
                ->modalHeading('Cambiar Logo del Sitio')
                ->modalDescription('Sube un nuevo logo para el sitio web. Se recomienda un tamaño de 200x50 píxeles.')
                ->form([
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo')
                        ->required()
                        ->image()
                        ->maxSize(2048)
                        ->disk('public')
                        ->directory('temp'),
                ])
                ->action(function (array $data): void {
                    $this->submitForm($data['logo']);
                }),
        ];
    }
} 