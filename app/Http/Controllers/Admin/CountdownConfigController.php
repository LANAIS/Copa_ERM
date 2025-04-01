<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountdownConfig;
use Illuminate\Http\Request;

class CountdownConfigController extends Controller
{
    public function index()
    {
        $targetDate = CountdownConfig::getTargetDate();
        return view('admin.countdown-config.index', compact('targetDate'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'target_date' => 'required|date'
        ]);

        CountdownConfig::setTargetDate($request->target_date);

        return redirect()->back()->with('success', 'Fecha del countdown actualizada correctamente');
    }
} 