<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteConfigController extends Controller
{
    /**
     * Mostrar la página de configuraciones del sitio
     */
    public function index()
    {
        $logo = SiteConfig::getLogo();
        
        return view('admin.site-config.index', compact('logo'));
    }

    /**
     * Actualizar las configuraciones del sitio
     */
    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $this->updateLogo($request->file('logo'));
        }

        return redirect('/admin')->with('success', 'Configuración actualizada con éxito');
    }

    private function updateLogo($logo)
    {
        // Eliminar el logo anterior si existe
        $oldLogo = SiteConfig::getValue('site_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }
        
        // Guardar el nuevo logo
        $logoPath = $logo->store('logos', 'public');
        SiteConfig::setLogo($logoPath);
    }
} 