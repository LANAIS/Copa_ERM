<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Robot;
use App\Models\Equipo;
use App\Models\Competition;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompetitorController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $equipos = Equipo::where('user_id', $user->id)->get();
        $robots = Robot::where('user_id', $user->id)
                ->orWhereIn('equipo_id', $equipos->pluck('id'))
                ->get();
        $inscripciones = Registration::where('user_id', $user->id)->get();
        $competencias = Competition::where('active', true)->get();

        return view('competitor.index', compact('equipos', 'robots', 'inscripciones', 'competencias'));
    }

    // === GESTIÓN DE EQUIPOS ===
    
    public function indexEquipos()
    {
        $equipos = Equipo::where('user_id', Auth::id())->get();
        return view('competitor.equipos.index', compact('equipos'));
    }

    public function createEquipo()
    {
        return view('competitor.equipos.create');
    }
    
    public function storeEquipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'sitio_web' => 'nullable|url',
            'email' => 'nullable|email',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'youtube' => 'nullable|url',
            'linkedin' => 'nullable|url'
        ]);

        $equipo = new Equipo();
        $equipo->nombre = $request->nombre;
        $equipo->descripcion = $request->descripcion;
        $equipo->user_id = Auth::id();
        $equipo->activo = true;
        $equipo->sitio_web = $request->sitio_web;
        $equipo->email = $request->email;
        $equipo->instagram = $request->instagram;
        $equipo->facebook = $request->facebook;
        $equipo->youtube = $request->youtube;
        $equipo->linkedin = $request->linkedin;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('equipos', 'public');
            $equipo->logo = $path;
        }

        $equipo->save();

        return redirect()->route('competitor.equipos.index')->with('success', 'Equipo creado exitosamente');
    }
    
    public function showEquipo($id)
    {
        $equipo = Equipo::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        
        $robots = Robot::where('equipo_id', $equipo->id)->get();
        
        return view('competitor.equipos.show', compact('equipo', 'robots'));
    }
    
    public function editEquipo($id)
    {
        $equipo = Equipo::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        
        return view('competitor.equipos.edit', compact('equipo'));
    }
    
    public function updateEquipo(Request $request, $id)
    {
        $equipo = Equipo::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'sitio_web' => 'nullable|url',
            'email' => 'nullable|email',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'youtube' => 'nullable|url',
            'linkedin' => 'nullable|url'
        ]);

        $equipo->nombre = $request->nombre;
        $equipo->descripcion = $request->descripcion;
        $equipo->sitio_web = $request->sitio_web;
        $equipo->email = $request->email;
        $equipo->instagram = $request->instagram;
        $equipo->facebook = $request->facebook;
        $equipo->youtube = $request->youtube;
        $equipo->linkedin = $request->linkedin;

        if ($request->hasFile('logo')) {
            // Eliminar el logo anterior si existe
            if ($equipo->logo) {
                Storage::disk('public')->delete($equipo->logo);
            }
            
            $path = $request->file('logo')->store('equipos', 'public');
            $equipo->logo = $path;
        }

        $equipo->save();

        return redirect()->route('competitor.equipos.index')->with('success', 'Equipo actualizado exitosamente');
    }
    
    public function destroyEquipo($id)
    {
        $equipo = Equipo::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        
        // Verificar si tiene robots asociados
        if ($equipo->robots()->count() > 0) {
            return redirect()->route('competitor.equipos.index')
                    ->with('error', 'No se puede eliminar el equipo porque tiene robots asociados');
        }
        
        // Eliminar el logo si existe
        if ($equipo->logo) {
            Storage::disk('public')->delete($equipo->logo);
        }
        
        $equipo->delete();
        
        return redirect()->route('competitor.equipos.index')->with('success', 'Equipo eliminado exitosamente');
    }

    // === GESTIÓN DE ROBOTS ===
    
    public function indexRobots(Request $request)
    {
        $user = Auth::user();
        $equipos = Equipo::where('user_id', $user->id)->get();
        
        $robotsQuery = Robot::where(function($query) use ($user, $equipos) {
            $query->where('user_id', $user->id)
                  ->orWhereIn('equipo_id', $equipos->pluck('id'));
        });
        
        // Aplicar filtros
        if ($request->filled('search')) {
            $robotsQuery->where('nombre', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('categoria')) {
            $robotsQuery->where('categoria', $request->categoria);
        }
        
        if ($request->filled('equipo_id')) {
            $robotsQuery->where('equipo_id', $request->equipo_id);
        }
        
        $robots = $robotsQuery->get();
        
        return view('competitor.robots.index', compact('robots', 'equipos'));
    }

    public function createRobot()
    {
        $equipos = Equipo::where('user_id', Auth::id())->get();
        return view('competitor.robots.create', compact('equipos'));
    }
    
    public function storeRobot(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'categoria' => 'required|string',
            'imagen' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'equipo_id' => 'nullable|exists:equipos,id',
            'especificaciones' => 'nullable|string'
        ]);

        $robot = new Robot();
        $robot->nombre = $request->nombre;
        $robot->descripcion = $request->descripcion;
        $robot->categoria = $request->categoria;
        $robot->user_id = Auth::id();
        $robot->equipo_id = $request->equipo_id;
        $robot->especificaciones = $request->especificaciones;

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('robots', 'public');
            $robot->imagen = $path;
        }

        $robot->save();

        return redirect()->route('competitor.robots.index')->with('success', 'Robot creado exitosamente');
    }
    
    public function showRobot($id)
    {
        $user = Auth::user();
        $equipos = Equipo::where('user_id', $user->id)->pluck('id');
        
        $robot = Robot::where('id', $id)
                ->where(function($query) use ($user, $equipos) {
                    $query->where('user_id', $user->id)
                          ->orWhereIn('equipo_id', $equipos);
                })
                ->firstOrFail();
        
        $inscripciones = Registration::where('robot_id', $robot->id)->get();
        
        return view('competitor.robots.show', compact('robot', 'inscripciones'));
    }
    
    public function editRobot($id)
    {
        $user = Auth::user();
        $equipos = Equipo::where('user_id', $user->id)->get();
        $equiposIds = $equipos->pluck('id');
        
        $robot = Robot::where('id', $id)
                ->where(function($query) use ($user, $equiposIds) {
                    $query->where('user_id', $user->id)
                          ->orWhereIn('equipo_id', $equiposIds);
                })
                ->firstOrFail();
        
        return view('competitor.robots.edit', compact('robot', 'equipos'));
    }
    
    public function updateRobot(Request $request, $id)
    {
        $user = Auth::user();
        $equiposIds = Equipo::where('user_id', $user->id)->pluck('id');
        
        $robot = Robot::where('id', $id)
                ->where(function($query) use ($user, $equiposIds) {
                    $query->where('user_id', $user->id)
                          ->orWhereIn('equipo_id', $equiposIds);
                })
                ->firstOrFail();
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'categoria' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'equipo_id' => 'nullable|exists:equipos,id',
            'especificaciones' => 'nullable|string'
        ]);

        $robot->nombre = $request->nombre;
        $robot->descripcion = $request->descripcion;
        $robot->categoria = $request->categoria;
        $robot->equipo_id = $request->equipo_id;
        $robot->especificaciones = $request->especificaciones;

        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            if ($robot->imagen) {
                Storage::disk('public')->delete($robot->imagen);
            }
            
            $path = $request->file('imagen')->store('robots', 'public');
            $robot->imagen = $path;
        }

        $robot->save();

        return redirect()->route('competitor.robots.index')->with('success', 'Robot actualizado exitosamente');
    }
    
    public function destroyRobot($id)
    {
        $user = Auth::user();
        $equiposIds = Equipo::where('user_id', $user->id)->pluck('id');
        
        $robot = Robot::where('id', $id)
                ->where(function($query) use ($user, $equiposIds) {
                    $query->where('user_id', $user->id)
                          ->orWhereIn('equipo_id', $equiposIds);
                })
                ->firstOrFail();
        
        // Verificar si tiene inscripciones
        if (Registration::where('robot_id', $robot->id)->exists()) {
            return redirect()->route('competitor.robots.index')
                    ->with('error', 'No se puede eliminar el robot porque tiene inscripciones asociadas');
        }
        
        // Eliminar la imagen si existe
        if ($robot->imagen) {
            Storage::disk('public')->delete($robot->imagen);
        }
        
        $robot->delete();
        
        return redirect()->route('competitor.robots.index')->with('success', 'Robot eliminado exitosamente');
    }

    // === GESTIÓN DE INSCRIPCIONES ===
    
    public function indexInscripciones()
    {
        $inscripciones = Registration::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        
        return view('competitor.inscripciones.index', compact('inscripciones'));
    }
    
    public function createInscripcion(Request $request)
    {
        $user = Auth::user();
        $equipos = Equipo::where('user_id', $user->id)->get();
        $equiposIds = $equipos->pluck('id');
        
        $robots = Robot::where('user_id', $user->id)
                ->orWhereIn('equipo_id', $equiposIds)
                ->get();
        
        $competencias = Competition::where('active', true)->get();
        
        return view('competitor.inscripciones.create', compact('robots', 'equipos', 'competencias'));
    }
    
    public function inscribirCompetencia(Request $request)
    {
        $request->validate([
            'competition_id' => 'required|exists:competitions,id',
            'robot_id' => 'required|exists:robots,id',
            'equipo_id' => 'required|exists:equipos,id',
            'notes' => 'nullable|string',
            'terms' => 'required'
        ]);

        // Verificar que el robot pertenezca al usuario o a uno de sus equipos
        $user = Auth::user();
        $equiposIds = Equipo::where('user_id', $user->id)->pluck('id');
        
        $robot = Robot::where('id', $request->robot_id)
                ->where(function($query) use ($user, $equiposIds) {
                    $query->where('user_id', $user->id)
                          ->orWhereIn('equipo_id', $equiposIds);
                })
                ->firstOrFail();
        
        // Verificar que el equipo pertenezca al usuario
        $equipo = Equipo::where('id', $request->equipo_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        
        // Verificar si ya existe una inscripción para este robot en esta competencia
        $existingInscripcion = Registration::where('competition_id', $request->competition_id)
                ->where('robot_id', $request->robot_id)
                ->first();
        
        if ($existingInscripcion) {
            return redirect()->back()
                    ->withErrors(['robot_id' => 'Este robot ya está inscrito en esta competencia'])
                    ->withInput();
        }

        $inscripcion = new Registration();
        $inscripcion->competition_id = $request->competition_id;
        $inscripcion->robot_id = $request->robot_id;
        $inscripcion->equipo_id = $request->equipo_id;
        $inscripcion->user_id = Auth::id();
        $inscripcion->status = 'pending';
        $inscripcion->notes = $request->notes;
        $inscripcion->registration_date = now();

        $inscripcion->save();

        return redirect()->route('competitor.inscripciones.index')->with('success', 'Inscripción realizada exitosamente');
    }
    
    public function showInscripcion($id)
    {
        $inscripcion = Registration::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
        
        return view('competitor.inscripciones.show', compact('inscripcion'));
    }
    
    public function destroyInscripcion($id)
    {
        $inscripcion = Registration::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending') // Solo permitir cancelar inscripciones pendientes
                ->firstOrFail();
        
        $inscripcion->delete();
        
        return redirect()->route('competitor.inscripciones.index')->with('success', 'Inscripción cancelada exitosamente');
    }
} 