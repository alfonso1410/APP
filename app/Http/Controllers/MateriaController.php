<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    /**
     * Muestra la lista de materias y maneja los modales.
     */
    public function index()
    {
        $materias = Materia::with([
            'camposFormativos',
            'asignacionesGrupo' => function ($query) {
                $query->with(['maestro', 'grupo.grado']); 
            }
        ])
        ->orderBy('nombre')
        ->get();
        
        return view('materias.index', compact('materias'));
    }

    /**
     * Guarda la nueva materia desde el modal.
     */
    public function store(Request $request)
    {
        // --- CORRECCIÓN: Usamos 'validateWithBag' ---
        // Esto guarda los errores en un "contenedor" llamado 'store'.
        $validatedData = $request->validateWithBag('store', [
            'nombre' => 'required|string|max:100|unique:materias,nombre',
        ], [
            'nombre.unique' => 'La materia ya existe.',
        ]);

        Materia::create($validatedData);

        return redirect()->route('materias.index')
                         ->with('success', 'Materia creada exitosamente.');
    }

    /**
     * --- MÉTODO ELIMINADO ---
     * Ya no se necesita una página separada para 'edit'.
     * La lógica del modal está en 'index'.
     */
    // public function edit(Materia $materia)
    // {
    //     return view('materias.edit', compact('materia'));
    // }

    /**
     * Actualiza la materia desde el modal.
     */
    public function update(Request $request, Materia $materia)
    {
        // --- CORRECCIÓN: Usamos 'validateWithBag' ---
        // Esto guarda los errores en un "contenedor" llamado 'update'.
        $validatedData = $request->validateWithBag('update', [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('materias')->ignore($materia->materia_id, 'materia_id'),
            ],
        ], [
            'nombre.unique' => 'La materia ya existe.',
        ]);

        $materia->update($validatedData);

        return redirect()->route('materias.index')
                         ->with('success', 'Materia actualizada exitosamente.');
    }

    /**
     * Elimina la materia. (Sin cambios)
     */
    public function destroy(Materia $materia)
    {
        try {
            $materia->delete();
            return redirect()->route('materias.index')
                             ->with('success', 'Materia eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('materias.index')
                             ->with('error', 'No se puede eliminar la materia, está siendo utilizada.');
        }
    }
}