<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
      public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:niveles,nombre',
        ]);

        Nivel::create($validated);

        return back()->with('success', 'Nivel creado exitosamente.');
    }
}
