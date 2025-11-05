<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Grado;

class NivelController extends Controller
{

     public function index()
    {
        $niveles = Nivel::orderBy('nombre')->get(); 
        return view('admin.niveles.index', compact('niveles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:niveles,nombre',
        ]);

        Nivel::create($validated);

        return redirect()->route('admin.niveles.index')
                         ->with('success', 'Nivel registrado correctamente.');
    }

    public function update(Request $request, Nivel $nivel)
    {
        $validated = $request->validate([
            'nombre' => [
                'required','string','max:255',
                Rule::unique('niveles')->ignore($nivel->nivel_id, 'nivel_id')
            ],
        ]);

        $nivel->update($validated);

        return redirect()->route('admin.niveles.index')
                         ->with('success', 'Nivel actualizado exitosamente.');
    }

    public function destroy(Nivel $nivel)
    {
        if ($nivel->grados()->exists()) {
            return redirect()->route('admin.niveles.index')
                             ->with('error', 'No se puede eliminar el nivel porque tiene grados asociados.');
        }

        $nivel->delete();

        return redirect()->route('admin.niveles.index')
                         ->with('success', 'Nivel eliminado exitosamente.');
    }
}