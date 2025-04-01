<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $registrations = Registration::with(['team', 'robot', 'competitionEvent', 'competitionEvent.competition', 'competitionEvent.category'])
            ->orderBy('registration_date', 'desc')
            ->paginate(20);
        
        return view('admin.registrations.index', compact('registrations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Registration $registration)
    {
        $registration->load(['team', 'robot', 'competitionEvent', 'competitionEvent.competition', 'competitionEvent.category', 'scores']);
        
        return view('admin.registrations.show', compact('registration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Aprobar una inscripción
     */
    public function approve(Registration $registration)
    {
        if ($registration->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Solo se pueden aprobar inscripciones con estado pendiente.');
        }
        
        $registration->status = 'approved';
        $registration->approval_date = now();
        $registration->approved_by = Auth::id();
        $registration->save();
        
        return redirect()->route('admin.registrations.show', $registration)
            ->with('success', 'Inscripción aprobada exitosamente.');
    }

    /**
     * Rechazar una inscripción
     */
    public function reject(Registration $registration)
    {
        if ($registration->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Solo se pueden rechazar inscripciones con estado pendiente.');
        }
        
        $registration->status = 'rejected';
        $registration->approval_date = now();
        $registration->approved_by = Auth::id();
        $registration->save();
        
        return redirect()->route('admin.registrations.show', $registration)
            ->with('success', 'Inscripción rechazada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Registration $registration)
    {
        $registration->delete();
        
        return redirect()->route('admin.registrations.index')
            ->with('success', 'Inscripción eliminada exitosamente.');
    }
}
