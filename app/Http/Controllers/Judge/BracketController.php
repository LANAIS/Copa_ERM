<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\Llave;
use Illuminate\Http\Request;

class BracketController extends Controller
{
    /**
     * Mostrar la vista pública de un bracket
     */
    public function viewPublic($id)
    {
        $llave = Llave::with(['enfrentamientos.equipo1', 'enfrentamientos.equipo2', 'enfrentamientos.ganador', 'categoriaEvento.categoria'])
            ->findOrFail($id);
        
        return view('judge.brackets.public', [
            'llave' => $llave,
        ]);
    }
} 