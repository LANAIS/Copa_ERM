<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Llave;
use App\Models\CategoriaEvento;
use App\Models\Enfrentamiento;
use App\Models\Evento;
use App\Models\Fecha;
use App\Models\Categoria;

class LlavesCompetenciaController extends Controller
{
    public function index()
    {
        return view('judge.llaves-competencia', [
            'title' => 'Listado de Brackets',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => route('filament.judge.pages.dashboard')],
                ['title' => 'Listado de Brackets']
            ]
        ]);
    }
} 