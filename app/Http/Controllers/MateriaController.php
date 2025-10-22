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
        // ✅ OPTIMIZACIÓN: Solo cargamos camposFormativos, ya que es la única relación visible en la tabla principal.
        $materias = Materia::with(['camposFormativos'])
        ->orderBy('nombre')
        ->get();
        
        return view('materias.index', compact('materias'));
    }

    // -----------------------------------------------------------------------

    /**
     * Guarda la nueva materia desde el modal.
     */
    public function store(Request $request)
    {
        // El código de validación es correcto para nombre y tipo.
        $validatedData = $request->validateWithBag('store', [
            'nombre' => 'required|string|max:100|unique:materias,nombre',
            'tipo' => 'required|in:REGULAR,EXTRA',
        ], [
            'nombre.unique' => 'La materia ya existe.',
            'tipo.required' => 'Debe seleccionar un tipo de materia.',
            'tipo.in' => 'El tipo de materia seleccionado no es válido.',
        ]);

        Materia::create($validatedData);

        return redirect()->route('admin.materias.index')
                         ->with('success', 'Materia creada exitosamente.');
    }

    // -----------------------------------------------------------------------

    /**
     * Actualiza la materia desde el modal.
     */
    public function update(Request $request, Materia $materia)
    {
        // El código de validación es correcto para nombre y tipo.
        $validatedData = $request->validateWithBag('update', [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('materias')->ignore($materia->materia_id, 'materia_id'),
            ],
            'tipo' => 'required|in:REGULAR,EXTRA',
        ], [
            'nombre.unique' => 'La materia ya existe.',
            'tipo.required' => 'Debe seleccionar un tipo de materia.',
            'tipo.in' => 'El tipo de materia seleccionado no es válido.',
        ]);

        $materia->update($validatedData);

        return redirect()->route('admin.materias.index')
                         ->with('success', 'Materia actualizada exitosamente.');
    }

    // -----------------------------------------------------------------------

    /**
     * Elimina la materia. (Sin cambios)
     */
    public function destroy(Materia $materia)
    {
        try {
            $materia->delete();
            return redirect()->route('admin.materias.index')
                             ->with('success', 'Materia eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.materias.index')
                             ->with('error', 'No se puede eliminar la materia, está siendo utilizada.');
        }
    }
}