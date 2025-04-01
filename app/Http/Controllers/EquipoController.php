<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Redirigir al panel de Filament donde se gestionarán los equipos
        return redirect()->route('filament.admin.resources.equipos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Redirigir al panel de Filament para crear un nuevo equipo
        return redirect()->route('filament.admin.resources.equipos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Este método no se utiliza ya que usamos Filament
        return redirect()->route('filament.admin.resources.equipos.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipo $equipo)
    {
        // Verificar que el equipo pertenezca al usuario actual
        if ($equipo->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Redirigir a ver el equipo en Filament
        return redirect()->route('filament.admin.resources.equipos.view', ['record' => $equipo->id]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Equipo $equipo)
    {
        // Verificar que el equipo pertenezca al usuario actual
        if ($equipo->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Redirigir a editar el equipo en Filament
        return redirect()->route('filament.admin.resources.equipos.edit', ['record' => $equipo->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Equipo $equipo)
    {
        // Este método no se utiliza ya que usamos Filament
        return redirect()->route('filament.admin.resources.equipos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipo $equipo)
    {
        // Verificar que el equipo pertenezca al usuario actual
        if ($equipo->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Eliminar archivos asociados
        if ($equipo->logo) {
            Storage::disk('public')->delete($equipo->logo);
        }
        
        if ($equipo->banner) {
            Storage::disk('public')->delete($equipo->banner);
        }
        
        $equipo->delete();
        
        return redirect()->route('filament.admin.resources.equipos.index')
            ->with('success', 'Equipo eliminado exitosamente');
    }
}
