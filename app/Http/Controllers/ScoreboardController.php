<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Registration;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreboardController extends Controller
{
    /**
     * Mostrar la tabla de posiciones general
     */
    public function index()
    {
        $categories = Category::all();
        $competitions = Competition::where('active', true)->get();
        
        return view('scoreboard.index', compact('categories', 'competitions'));
    }
    
    /**
     * Mostrar la tabla de posiciones por categoría
     */
    public function byCategory(Category $category)
    {
        // Obtener puntajes agrupados por equipo
        $scores = Registration::with(['team', 'robot', 'competitionEvent'])
            ->whereHas('competitionEvent', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->where('status', 'approved')
            ->join('scores', 'registrations.id', '=', 'scores.registration_id')
            ->select('registrations.team_id', DB::raw('SUM(scores.points) as total_points'))
            ->groupBy('registrations.team_id')
            ->orderByDesc('total_points')
            ->get();
            
        return view('scoreboard.category', compact('category', 'scores'));
    }
}
