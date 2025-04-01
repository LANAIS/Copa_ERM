<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoriaEvento;
use App\Models\Evento;
use App\Models\FechaEvento;
use App\Models\Categoria;
use App\Models\Llave;
use App\Models\Homologacion;
use App\Models\InscripcionEvento;

class GestionCompetenciasController extends Controller
{
    /**
     * Mostrar el panel principal de gestión de competencias
     */
    public function index()
    {
        // Obtener todos los eventos activos
        $eventos = Evento::where('estado', 'abierto')
                        ->orderBy('fecha_inicio', 'desc')
                        ->get();
        
        // Estadísticas generales
        $stats = [
            'total_eventos' => Evento::count(),
            'eventos_activos' => Evento::where('estado', 'abierto')->count(),
            'total_categorias' => CategoriaEvento::count(),
            'categorias_activas' => CategoriaEvento::where('activo', true)->count(),
            'inscripciones' => InscripcionEvento::count(),
            'homologaciones_pendientes' => Homologacion::where('estado', Homologacion::ESTADO_PENDIENTE)->count(),
        ];
        
        return view('admin.gestion-competencias.index', compact('eventos', 'stats'));
    }
    
    /**
     * Mostrar un evento específico con sus fechas y categorías
     */
    public function evento($id)
    {
        $evento = Evento::with(['fechas', 'categorias.categoria'])->findOrFail($id);
        
        return view('admin.gestion-competencias.evento', compact('evento'));
    }
    
    /**
     * Mostrar una fecha específica con sus categorías
     */
    public function fecha($id)
    {
        $fecha = FechaEvento::with(['evento', 'categorias.categoria'])->findOrFail($id);
        
        return view('admin.gestion-competencias.fecha', compact('fecha'));
    }
    
    /**
     * Mostrar una categoría específica con sus inscripciones
     */
    public function categoria($id)
    {
        $categoria = CategoriaEvento::with([
            'categoria', 
            'evento', 
            'inscripciones.equipo',
            'inscripciones.robotsParticipantes',
            'homologaciones.robot',
            'llave'
        ])->findOrFail($id);
        
        // Calcular estadísticas de homologación
        $statsHomologacion = [
            'total' => $categoria->homologaciones->count(),
            'pendientes' => $categoria->homologaciones->where('estado', Homologacion::ESTADO_PENDIENTE)->count(),
            'aprobadas' => $categoria->homologaciones->where('estado', Homologacion::ESTADO_APROBADO)->count(),
            'rechazadas' => $categoria->homologaciones->where('estado', Homologacion::ESTADO_RECHAZADO)->count(),
        ];
        
        return view('admin.gestion-competencias.categoria', compact('categoria', 'statsHomologacion'));
    }
    
    /**
     * Cambiar el estado de una categoría
     */
    public function cambiarEstado(Request $request, CategoriaEvento $categoria)
    {
        $nuevoEstado = $request->input('estado');
        
        if (!in_array($nuevoEstado, [
            CategoriaEvento::ESTADO_INSCRIPCIONES,
            CategoriaEvento::ESTADO_HOMOLOGACION, 
            CategoriaEvento::ESTADO_ARMADO_LLAVES, 
            CategoriaEvento::ESTADO_EN_CURSO, 
            CategoriaEvento::ESTADO_FINALIZADA
        ])) {
            return redirect()->back()->with('error', 'Estado no válido');
        }
        
        // Validar las transiciones permitidas
        $transicionValida = false;
        $estadoActual = $categoria->estado_competencia;
        
        switch ($estadoActual) {
            case CategoriaEvento::ESTADO_CREADA:
                $transicionValida = $nuevoEstado === CategoriaEvento::ESTADO_INSCRIPCIONES;
                break;
            case CategoriaEvento::ESTADO_INSCRIPCIONES:
                $transicionValida = $nuevoEstado === CategoriaEvento::ESTADO_HOMOLOGACION;
                break;
            case CategoriaEvento::ESTADO_HOMOLOGACION:
                $transicionValida = $nuevoEstado === CategoriaEvento::ESTADO_ARMADO_LLAVES;
                if ($transicionValida && !$categoria->homologacionesCompletas()) {
                    return redirect()->back()->with('error', 'No se puede avanzar porque hay homologaciones pendientes');
                }
                break;
            case CategoriaEvento::ESTADO_ARMADO_LLAVES:
                $transicionValida = $nuevoEstado === CategoriaEvento::ESTADO_EN_CURSO;
                if ($transicionValida && !$categoria->llave) {
                    return redirect()->back()->with('error', 'No se puede iniciar la competencia sin una llave');
                }
                break;
            case CategoriaEvento::ESTADO_EN_CURSO:
                $transicionValida = $nuevoEstado === CategoriaEvento::ESTADO_FINALIZADA;
                break;
        }
        
        if (!$transicionValida) {
            return redirect()->back()->with('error', 'No se puede cambiar directamente de ' . $estadoActual . ' a ' . $nuevoEstado);
        }
        
        // Ejecutar las acciones del cambio de estado
        switch ($nuevoEstado) {
            case CategoriaEvento::ESTADO_INSCRIPCIONES:
                $categoria->abrirInscripciones();
                break;
            case CategoriaEvento::ESTADO_HOMOLOGACION:
                $categoria->iniciarHomologacion();
                break;
            case CategoriaEvento::ESTADO_ARMADO_LLAVES:
                $categoria->iniciarArmadoLlaves();
                break;
            case CategoriaEvento::ESTADO_EN_CURSO:
                $categoria->iniciarCompetencia();
                break;
            case CategoriaEvento::ESTADO_FINALIZADA:
                $categoria->finalizarCompetencia();
                break;
        }
        
        return redirect()->back()->with('success', 'Estado cambiado correctamente');
    }
    
    /**
     * Crear una llave para una categoría
     */
    public function crearLlave(Request $request, CategoriaEvento $categoria)
    {
        // Verificar si ya existe una llave
        if ($categoria->llave) {
            return redirect()->route('admin.brackets.admin', $categoria->llave->id);
        }
        
        // Validar que estemos en la etapa correcta
        if (!$categoria->enArmadoLlaves()) {
            return redirect()->back()->with('error', 'La categoría no está en estado de armado de llaves');
        }
        
        // Obtener el tipo de fixture seleccionado
        $tipoFixture = $request->input('tipo_fixture', Llave::TIPO_ELIMINACION_DIRECTA);
        
        // Crear nueva llave
        $llave = new Llave([
            'categoria_evento_id' => $categoria->id,
            'tipo_fixture' => $tipoFixture,
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
    
    /**
     * Gestionar las homologaciones de una categoría
     */
    public function homologaciones($id)
    {
        $categoria = CategoriaEvento::with([
            'categoria', 
            'evento', 
            'homologaciones.robot.equipo'
        ])->findOrFail($id);
        
        // Verificar que estemos en la etapa de homologación
        if (!$categoria->enHomologacion()) {
            return redirect()->route('admin.gestion-competencias.categoria', $id)
                ->with('error', 'La categoría no está en etapa de homologación');
        }
        
        // Calcular estadísticas de homologación
        $stats = [
            'total' => $categoria->homologaciones->count(),
            'pendientes' => $categoria->homologaciones->where('estado', Homologacion::ESTADO_PENDIENTE)->count(),
            'aprobadas' => $categoria->homologaciones->where('estado', Homologacion::ESTADO_APROBADO)->count(),
            'rechazadas' => $categoria->homologaciones->where('estado', Homologacion::ESTADO_RECHAZADO)->count(),
        ];
        
        return view('admin.gestion-competencias.homologaciones', compact('categoria', 'stats'));
    }
} 