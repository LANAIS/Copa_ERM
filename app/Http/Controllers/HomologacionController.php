<?php

namespace App\Http\Controllers;

use App\Models\CategoriaEvento;
use App\Models\InscripcionEvento;
use App\Models\Homologacion;
use App\Models\Robot;
use Illuminate\Http\Request;

class HomologacionController extends Controller
{
    /**
     * Mostrar la página de homologaciones para una categoría de evento
     */
    public function index($categoriaEventoId)
    {
        $categoriaEvento = CategoriaEvento::with('categoria', 'evento')->findOrFail($categoriaEventoId);
        
        // Obtener inscripciones aprobadas para esta categoría
        $inscripciones = InscripcionEvento::where('categoria_evento_id', $categoriaEventoId)
            ->where('estado', 'aprobada')
            ->with(['equipo', 'robots', 'robots.homologaciones' => function($query) use ($categoriaEventoId) {
                $query->where('categoria_evento_id', $categoriaEventoId);
            }])
            ->get();
        
        return view('homologaciones.index', [
            'categoriaEvento' => $categoriaEvento,
            'inscripciones' => $inscripciones,
        ]);
    }
    
    /**
     * Mostrar formulario para registrar una homologación
     */
    public function create($robotId, $categoriaEventoId)
    {
        $robot = Robot::findOrFail($robotId);
        $categoriaEvento = CategoriaEvento::with('categoria')->findOrFail($categoriaEventoId);
        
        // Verificar si ya existe una homologación para este robot en esta categoría
        $homologacionExistente = Homologacion::where('robot_id', $robotId)
            ->where('categoria_evento_id', $categoriaEventoId)
            ->first();
        
        return view('homologaciones.create', [
            'robot' => $robot,
            'categoriaEvento' => $categoriaEvento,
            'homologacion' => $homologacionExistente,
        ]);
    }
    
    /**
     * Guardar una homologación
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'robot_id' => 'required|exists:robots,id',
            'categoria_evento_id' => 'required|exists:categoria_eventos,id',
            'peso' => 'required|numeric|min:0',
            'ancho' => 'required|numeric|min:0',
            'largo' => 'required|numeric|min:0',
            'alto' => 'required|numeric|min:0',
            'resultado' => 'required|in:aprobado,rechazado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Verificar si ya existe una homologación para este robot en esta categoría
        $homologacion = Homologacion::where('robot_id', $validatedData['robot_id'])
            ->where('categoria_evento_id', $validatedData['categoria_evento_id'])
            ->first();

        if ($homologacion) {
            // Actualizar la homologación existente
            $homologacion->update($validatedData);
            return redirect()->route('homologaciones.index', $validatedData['categoria_evento_id'])
                ->with('success', 'Homologación actualizada con éxito.');
        } else {
            // Crear una nueva homologación
            Homologacion::create($validatedData);
            return redirect()->route('homologaciones.index', $validatedData['categoria_evento_id'])
                ->with('success', 'Homologación registrada con éxito.');
        }
    }
    
    /**
     * Finalizar proceso de homologación y pasar a armado de llaves
     */
    public function finalizar($categoriaEventoId)
    {
        $categoriaEvento = CategoriaEvento::findOrFail($categoriaEventoId);
        
        // Verificar que al menos un robot esté homologado
        $robotsHomologados = Homologacion::where('categoria_evento_id', $categoriaEventoId)
            ->where('resultado', 'aprobado')
            ->count();
        
        if ($robotsHomologados < 2) {
            return back()->with('error', 'No hay suficientes robots homologados para continuar. Mínimo 2 robots aprobados.');
        }
        
        // Actualizar estado de la competencia
        $categoriaEvento->update(['estado_competencia' => 'armado_llaves']);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Proceso de homologación finalizado. La competencia ha pasado a la fase de armado de llaves.');
    }

    public function edit(Robot $robot, CategoriaEvento $categoriaEvento)
    {
        // Buscar la homologación existente
        $homologacion = Homologacion::where('robot_id', $robot->id)
            ->where('categoria_evento_id', $categoriaEvento->id)
            ->first();

        if (!$homologacion) {
            return redirect()->route('homologaciones.index', $categoriaEvento->id)
                ->with('error', 'La homologación solicitada no existe.');
        }

        return view('homologaciones.create', [
            'robot' => $robot,
            'categoriaEvento' => $categoriaEvento,
            'homologacion' => $homologacion,
            'editando' => true
        ]);
    }

    public function update(Request $request, Homologacion $homologacion)
    {
        $validatedData = $request->validate([
            'peso' => 'required|numeric|min:0',
            'ancho' => 'required|numeric|min:0',
            'largo' => 'required|numeric|min:0',
            'alto' => 'required|numeric|min:0',
            'resultado' => 'required|in:aprobado,rechazado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $homologacion->update($validatedData);

        return redirect()->route('homologaciones.index', $homologacion->categoria_evento_id)
            ->with('success', 'Homologación actualizada con éxito.');
    }
} 