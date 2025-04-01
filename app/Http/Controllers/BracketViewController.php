<?php

namespace App\Http\Controllers;

use App\Models\Llave;
use Illuminate\Http\Request;

class BracketViewController extends Controller
{
    /**
     * Redirige a la vista de administración de brackets de Filament
     */
    public function adminView($id)
    {
        // Verificar que el bracket existe
        $bracket = Llave::findOrFail($id);
        return redirect("/admin/pages/bracket-admin?id={$id}");
    }

    /**
     * Redirige a la vista pública de brackets de Filament
     */
    public function publicView($id)
    {
        // Verificar que el bracket existe
        $bracket = Llave::findOrFail($id);
        return redirect("/admin/pages/bracket-public-view?id={$id}");
    }

    /**
     * Redirige a la vista de administración de brackets para jueces
     */
    public function judgeAdminView($id)
    {
        // Verificar que el bracket existe
        $bracket = Llave::findOrFail($id);
        return redirect("/judge/pages/bracket-admin-view?id={$id}");
    }

    /**
     * Redirige a la vista pública de brackets para jueces
     */
    public function judgePublicView($id)
    {
        // Verificar que el bracket existe
        $bracket = Llave::findOrFail($id);
        return redirect("/judge/pages/bracket-public-view?id={$id}");
    }
} 