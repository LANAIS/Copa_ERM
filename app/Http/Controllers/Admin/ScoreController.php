<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $scores = Score::with(['registration', 'registration.team', 'registration.robot', 'registration.competitionEvent', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.scores.index', compact('scores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $registrations = Registration::where('status', 'approved')
            ->with(['team', 'robot', 'competitionEvent', 'competitionEvent.category'])
            ->get();
            
        return view('admin.scores.create', compact('registrations'));
    }
    
    /**
     * Show the form for creating a new score for a specific registration.
     */
    public function createForRegistration(Registration $registration)
    {
        if ($registration->status !== 'approved') {
            return redirect()->route('admin.registrations.show', $registration)
                ->with('error', 'Solo se pueden asignar puntajes a inscripciones aprobadas.');
        }
        
        $registration->load(['team', 'robot', 'competitionEvent', 'competitionEvent.category', 'scores']);
        
        // Verificar si ya hay puntajes asignados
        if ($registration->scores->isNotEmpty()) {
            return redirect()->route('admin.scores.edit', $registration->scores->first())
                ->with('info', 'Ya existen puntajes para esta inscripción. Puedes editarlos.');
        }
        
        return view('admin.scores.create_for_registration', compact('registration'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'criteria' => 'required|string|max:100',
            'points' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string|max:500',
        ]);
        
        // Verificar que la inscripción está aprobada
        $registration = Registration::findOrFail($request->registration_id);
        
        if ($registration->status !== 'approved') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Solo se pueden asignar puntajes a inscripciones aprobadas.');
        }
        
        $score = new Score();
        $score->registration_id = $request->registration_id;
        $score->criteria = $request->criteria;
        $score->points = $request->points;
        $score->comments = $request->comments;
        $score->assigned_by = Auth::id();
        $score->save();
        
        return redirect()->route('admin.registrations.show', $registration)
            ->with('success', 'Puntaje asignado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Score $score)
    {
        $score->load(['registration', 'registration.team', 'registration.robot', 'registration.competitionEvent', 'assignedBy']);
        
        return view('admin.scores.show', compact('score'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Score $score)
    {
        $score->load(['registration', 'registration.team', 'registration.robot', 'registration.competitionEvent']);
        
        return view('admin.scores.edit', compact('score'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Score $score)
    {
        $request->validate([
            'criteria' => 'required|string|max:100',
            'points' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string|max:500',
        ]);
        
        $score->criteria = $request->criteria;
        $score->points = $request->points;
        $score->comments = $request->comments;
        $score->save();
        
        return redirect()->route('admin.scores.show', $score)
            ->with('success', 'Puntaje actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Score $score)
    {
        $registration = $score->registration;
        
        $score->delete();
        
        return redirect()->route('admin.registrations.show', $registration)
            ->with('success', 'Puntaje eliminado exitosamente.');
    }
}
