<?php

namespace App\Filament\Pages;

use App\Models\Llave;
use App\Models\Equipo;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;

class BracketPublicView extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Torneos';
    
    protected static ?string $navigationLabel = 'Visualizar Bracket';
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.bracket-public-view';
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $slug = 'brackets/{id}';

    public ?array $data = [];
    
    public $llaveId = null;
    
    public function mount($id = null): void
    {
        $this->llaveId = $id;
    }
    
    public function getBracketData()
    {
        $llave = Llave::with(['enfrentamientos.equipo1', 'enfrentamientos.equipo2', 'enfrentamientos.ganador', 'categoriaEvento.categoria'])
            ->findOrFail($this->llaveId);
            
        return [
            'llave' => $llave,
            'datos' => $this->prepararDatosBracket($llave),
        ];
    }
    
    /**
     * Preparar datos en formato adecuado para el visor de brackets
     */
    private function prepararDatosBracket(Llave $llave)
    {
        $datos = [];
        
        // Información básica del torneo
        $datos['torneo'] = [
            'id' => $llave->id,
            'nombre' => $llave->categoriaEvento->categoria->nombre,
            'tipo' => $llave->tipo_fixture,
            'estado' => $llave->estado_torneo,
            'finalizado' => $llave->finalizado,
            'estructura' => $llave->estructura,
        ];
        
        // Participantes (equipos)
        $participantes = [];
        $equipoIds = $llave->enfrentamientos()
            ->where('ronda', 1) // Solo de primera ronda
            ->whereNotNull('equipo1_id')
            ->orWhereNotNull('equipo2_id')
            ->get()
            ->pluck('equipo1_id')
            ->merge($llave->enfrentamientos()->pluck('equipo2_id'))
            ->filter()
            ->unique()
            ->values();
        
        $equipos = Equipo::whereIn('id', $equipoIds)->get();
        
        foreach ($equipos as $equipo) {
            $participantes[] = [
                'id' => $equipo->id,
                'nombre' => $equipo->nombre,
                'imagen' => $equipo->imagen ?? null,
                'seed' => null, // TODO: Implementar seed si es necesario
            ];
        }
        
        $datos['participantes'] = $participantes;
        
        // Matches (enfrentamientos)
        $matches = [];
        $enfrentamientos = $llave->enfrentamientos()->orderBy('numero_juego')->get();
        
        foreach ($enfrentamientos as $enfrentamiento) {
            $match = [
                'id' => $enfrentamiento->id,
                'numero_juego' => $enfrentamiento->numero_juego,
                'ronda' => $enfrentamiento->ronda,
                'posicion' => $enfrentamiento->posicion,
                'fase' => $enfrentamiento->fase,
                'grupo' => $enfrentamiento->grupo,
                'equipo1_id' => $enfrentamiento->equipo1_id,
                'equipo2_id' => $enfrentamiento->equipo2_id,
                'puntaje_equipo1' => $enfrentamiento->puntaje_equipo1,
                'puntaje_equipo2' => $enfrentamiento->puntaje_equipo2,
                'ganador_id' => $enfrentamiento->ganador_id,
                'estado' => $enfrentamiento->tieneResultado() ? 'completado' : 
                            ($enfrentamiento->enCurso() ? 'en_curso' : 'pendiente'),
            ];
            
            $matches[] = $match;
        }
        
        $datos['matches'] = $matches;
        
        return $datos;
    }
    
    public static function getNavigationBadge(): ?string
    {
        return Llave::count();
    }
} 