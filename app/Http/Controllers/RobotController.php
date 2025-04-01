<?php

namespace App\Http\Controllers;

use App\Models\Robot;
use App\Models\Team;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RobotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Redirigir al panel de Filament donde se gestionarán los robots
        return redirect()->route('filament.admin.resources.robots.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Redirigir al panel de Filament para crear un nuevo robot
        return redirect()->route('filament.admin.resources.robots.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Este método no se utiliza ya que usamos Filament
        return redirect()->route('filament.admin.resources.robots.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Robot $robot)
    {
        // Verificar que el robot pertenezca al usuario actual
        $userId = Auth::id();
        $equipoIds = Equipo::where('user_id', $userId)->pluck('id')->toArray();
        
        if (!in_array($robot->equipo_id, $equipoIds)) {
            abort(403);
        }
        
        // Redirigir a ver el robot en Filament
        return redirect()->route('filament.admin.resources.robots.view', ['record' => $robot->id]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Robot $robot)
    {
        // Verificar que el robot pertenezca al usuario actual
        $userId = Auth::id();
        $equipoIds = Equipo::where('user_id', $userId)->pluck('id')->toArray();
        
        if (!in_array($robot->equipo_id, $equipoIds)) {
            abort(403);
        }
        
        // Redirigir a editar el robot en Filament
        return redirect()->route('filament.admin.resources.robots.edit', ['record' => $robot->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Robot $robot)
    {
        // Este método no se utiliza ya que usamos Filament
        return redirect()->route('filament.admin.resources.robots.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Robot $robot)
    {
        // Verificar que el robot pertenezca al usuario actual
        $userId = Auth::id();
        $equipoIds = Equipo::where('user_id', $userId)->pluck('id')->toArray();
        
        if (!in_array($robot->equipo_id, $equipoIds)) {
            abort(403);
        }
        
        // Eliminar archivos asociados
        if ($robot->foto) {
            Storage::disk('public')->delete($robot->foto);
        }
        
        $robot->delete();
        
        return redirect()->route('filament.admin.resources.robots.index')
            ->with('success', 'Robot eliminado exitosamente');
    }
}
