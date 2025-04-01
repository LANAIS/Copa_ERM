<?php

namespace App\Http\Controllers;

use App\Models\CompetitionEvent;
use App\Models\Registration;
use App\Models\Robot;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $teams = $user->teams;
        $teamIds = $teams->pluck('id')->toArray();
        
        $registrations = Registration::whereIn('team_id', $teamIds)
            ->with(['team', 'robot', 'competitionEvent', 'competitionEvent.competition', 'competitionEvent.category'])
            ->orderBy('registration_date', 'desc')
            ->get();
        
        return view('registrations.index', compact('registrations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $teams = $user->teams;
        
        if ($teams->isEmpty()) {
            return redirect()->route('teams.create')
                ->with('error', 'Primero debes crear un equipo para poder inscribirte en competencias.');
        }
        
        $robots = Robot::whereIn('team_id', $teams->pluck('id'))->get();
        
        if ($robots->isEmpty()) {
            return redirect()->route('teams.robots.create', $teams->first())
                ->with('error', 'Primero debes registrar al menos un robot para poder inscribirte en competencias.');
        }
        
        $events = CompetitionEvent::with(['competition', 'category'])
            ->where('registration_end', '>=', now())
            ->orderBy('event_date')
            ->get();
        
        if ($events->isEmpty()) {
            return redirect()->route('registrations.index')
                ->with('error', 'No hay competencias disponibles para inscripción en este momento.');
        }
        
        return view('registrations.create', compact('teams', 'robots', 'events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'robot_id' => 'required|exists:robots,id',
            'competition_event_id' => 'required|exists:competition_events,id',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Verificar que el robot pertenece al equipo
        $robot = Robot::findOrFail($request->robot_id);
        
        if ($robot->team_id != $request->team_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El robot seleccionado no pertenece al equipo seleccionado.');
        }
        
        // Verificar que el usuario es propietario del equipo
        $team = Team::findOrFail($request->team_id);
        
        if ($team->user_id != Auth::id()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No tienes permiso para inscribir a este equipo.');
        }
        
        // Verificar que la competición está abierta para inscripciones
        $event = CompetitionEvent::findOrFail($request->competition_event_id);
        
        if (now()->isAfter($event->registration_end)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El período de inscripción para esta competencia ha finalizado.');
        }
        
        // Verificar que no hay una inscripción previa del mismo equipo y robot en esta competencia
        $existingRegistration = Registration::where('team_id', $request->team_id)
            ->where('robot_id', $request->robot_id)
            ->where('competition_event_id', $request->competition_event_id)
            ->first();
        
        if ($existingRegistration) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe una inscripción para este equipo y robot en esta competencia.');
        }
        
        $registration = new Registration();
        $registration->team_id = $request->team_id;
        $registration->robot_id = $request->robot_id;
        $registration->competition_event_id = $request->competition_event_id;
        $registration->status = 'pending';
        $registration->registration_date = now();
        $registration->notes = $request->notes;
        $registration->save();
        
        return redirect()->route('registrations.index')
            ->with('success', 'Inscripción realizada exitosamente. Tu inscripción está pendiente de aprobación.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Registration $registration)
    {
        // Verificar que el usuario es propietario del equipo
        if ($registration->team->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            return redirect()->route('registrations.index')
                ->with('error', 'No tienes permiso para ver esta inscripción.');
        }
        
        $registration->load(['team', 'robot', 'competitionEvent', 'competitionEvent.competition', 'competitionEvent.category', 'scores']);
        
        return view('registrations.show', compact('registration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Registration $registration)
    {
        // Verificar que el usuario es propietario del equipo
        if ($registration->team->user_id != Auth::id()) {
            return redirect()->route('registrations.index')
                ->with('error', 'No tienes permiso para editar esta inscripción.');
        }
        
        // Solo se pueden editar inscripciones pendientes
        if ($registration->status !== 'pending') {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'Solo se pueden editar inscripciones con estado pendiente.');
        }
        
        $registration->load(['team', 'robot', 'competitionEvent']);
        
        $team = $registration->team;
        $robots = $team->robots;
        
        $event = $registration->competitionEvent;
        
        return view('registrations.edit', compact('registration', 'team', 'robots', 'event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Registration $registration)
    {
        // Verificar que el usuario es propietario del equipo
        if ($registration->team->user_id != Auth::id()) {
            return redirect()->route('registrations.index')
                ->with('error', 'No tienes permiso para editar esta inscripción.');
        }
        
        // Solo se pueden editar inscripciones pendientes
        if ($registration->status !== 'pending') {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'Solo se pueden editar inscripciones con estado pendiente.');
        }
        
        $request->validate([
            'robot_id' => 'required|exists:robots,id',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Verificar que el robot pertenece al equipo
        $robot = Robot::findOrFail($request->robot_id);
        
        if ($robot->team_id != $registration->team_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El robot seleccionado no pertenece al equipo.');
        }
        
        // Verificar que no hay una inscripción previa del mismo equipo y robot en esta competencia (excepto esta misma)
        $existingRegistration = Registration::where('team_id', $registration->team_id)
            ->where('robot_id', $request->robot_id)
            ->where('competition_event_id', $registration->competition_event_id)
            ->where('id', '!=', $registration->id)
            ->first();
        
        if ($existingRegistration) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe una inscripción para este equipo y robot en esta competencia.');
        }
        
        $registration->robot_id = $request->robot_id;
        $registration->notes = $request->notes;
        $registration->save();
        
        return redirect()->route('registrations.show', $registration)
            ->with('success', 'Inscripción actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Registration $registration)
    {
        // Verificar que el usuario es propietario del equipo
        if ($registration->team->user_id != Auth::id() && !Auth::user()->isAdmin()) {
            return redirect()->route('registrations.index')
                ->with('error', 'No tienes permiso para cancelar esta inscripción.');
        }
        
        // Solo se pueden eliminar inscripciones pendientes (usuarios normales)
        if ($registration->status !== 'pending' && !Auth::user()->isAdmin()) {
            return redirect()->route('registrations.show', $registration)
                ->with('error', 'Solo se pueden cancelar inscripciones con estado pendiente.');
        }
        
        $registration->delete();
        
        return redirect()->route('registrations.index')
            ->with('success', 'Inscripción cancelada exitosamente.');
    }
}
