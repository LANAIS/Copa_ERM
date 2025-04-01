<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoriaEvento;
use App\Models\Llave;

class GestionCompetenciaController extends Controller
{
    /**
     * Mostrar la lista de categorías para gestionar competencias
     */
    public function index()
    {
        $categorias = CategoriaEvento::with(['categoria', 'evento', 'llave'])
            ->orderBy('estado_competencia')
            ->orderBy('id', 'desc')
            ->paginate(10);
            
        // Agrupar por estado
        $porEstado = [
            'homologacion' => CategoriaEvento::where('estado_competencia', 'homologacion')->with(['categoria', 'evento'])->get(),
            'armado_llaves' => CategoriaEvento::where('estado_competencia', 'armado_llaves')->with(['categoria', 'evento', 'llave'])->get(),
            'en_curso' => CategoriaEvento::where('estado_competencia', 'en_curso')->with(['categoria', 'evento', 'llave'])->get(),
            'finalizada' => CategoriaEvento::where('estado_competencia', 'finalizada')->with(['categoria', 'evento', 'llave'])->get(),
        ];
        
        // Estadísticas
        $stats = [
            'total_categorias' => CategoriaEvento::count(),
            'homologacion' => CategoriaEvento::where('estado_competencia', 'homologacion')->count(),
            'armado_llaves' => CategoriaEvento::where('estado_competencia', 'armado_llaves')->count(),
            'en_curso' => CategoriaEvento::where('estado_competencia', 'en_curso')->count(),
            'finalizada' => CategoriaEvento::where('estado_competencia', 'finalizada')->count(),
        ];
            
        return view('admin.gestion-competencia.index', compact('categorias', 'porEstado', 'stats'));
    }
    
    /**
     * Cambiar el estado de una categoría
     */
    public function cambiarEstado(Request $request, CategoriaEvento $categoriaEvento)
    {
        $nuevoEstado = $request->input('estado');
        
        if (!in_array($nuevoEstado, ['homologacion', 'armado_llaves', 'en_curso', 'finalizada'])) {
            return redirect()->back()->with('error', 'Estado no válido');
        }
        
        // Validar transiciones permitidas
        $estadoActual = $categoriaEvento->estado_competencia;
        $transicionValida = false;
        
        switch ($estadoActual) {
            case 'inscripciones':
                $transicionValida = $nuevoEstado === 'homologacion';
                break;
            case 'homologacion':
                $transicionValida = $nuevoEstado === 'armado_llaves';
                break;
            case 'armado_llaves':
                $transicionValida = $nuevoEstado === 'en_curso';
                break;
            case 'en_curso':
                $transicionValida = $nuevoEstado === 'finalizada';
                break;
        }
        
        if (!$transicionValida) {
            return redirect()->back()->with('error', 'No se puede cambiar directamente de ' . $estadoActual . ' a ' . $nuevoEstado);
        }
        
        // Acciones específicas según el estado
        if ($nuevoEstado === 'homologacion' && $estadoActual === 'inscripciones') {
            $categoriaEvento->iniciarHomologacion();
        } else {
            $categoriaEvento->estado_competencia = $nuevoEstado;
            $categoriaEvento->save();
        }
        
        return redirect()->back()->with('success', 'Estado cambiado correctamente');
    }
    
    /**
     * Crear una llave para una categoría
     */
    public function crearLlave(CategoriaEvento $categoriaEvento)
    {
        // Verificar si ya existe una llave
        if ($categoriaEvento->llave) {
            return redirect()->route('admin.brackets.admin', $categoriaEvento->llave->id);
        }
        
        // Crear nueva llave
        $llave = new Llave([
            'categoria_evento_id' => $categoriaEvento->id,
            'tipo_fixture' => Llave::TIPO_ELIMINACION_DIRECTA,
            'estructura' => [
                'total_equipos' => 0,
                'total_rondas' => 0,
                'tamano_llave' => 0,
                'total_enfrentamientos' => 0,
            ],
            'finalizado' => false,
            'estado_torneo' => Llave::ESTADO_PENDIENTE,
        ]);
        
        $llave->save();
        
        return redirect()->route('admin.brackets.admin', $llave->id)
            ->with('success', 'Llave creada correctamente');
    }
}
