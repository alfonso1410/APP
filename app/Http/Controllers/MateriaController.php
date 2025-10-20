<?php

namespace App\Http\Controllers; // Make sure this namespace is correct

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    /**
     * Muestra la lista de materias con sus relaciones cargadas.
     */
    public function index()
    {
        // **CAMBIO:** Usamos with() para cargar relaciones eficientemente.
        // Esto evita múltiples consultas a la base de datos (problema N+1).
        $materias = Materia::with([
            'camposFormativos', // Carga el primer campo formativo asociado.
            'asignacionesGrupo' => function ($query) {
                // Carga las asignaciones y, dentro de ellas, el maestro y el grado.
                $query->with(['maestro', 'grupo.grado']); 
            }
        ])
        ->orderBy('nombre')
        ->get();
        
        return view('materias.index', compact('materias'));
    }

    /**
     * Muestra el formulario para crear una nueva materia.
     * (Sin cambios)
     */
    public function create()
    {
        return view('materias.create');
    }

    /**
     * Guarda la nueva materia.
     */
    public function store(Request $request)
    {
        // **CAMBIO:** Usamos validated() para obtener solo los datos validados.
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100|unique:materias,nombre',
        ], [
            'nombre.unique' => 'La materia ya existe.',
        ]);

        // **CAMBIO:** Usamos $validatedData en lugar de $request->all().
        // Esto previene errores de asignación masiva (MassAssignmentException).
        Materia::create($validatedData);

        return redirect()->route('materias.index')
                         ->with('success', 'Materia creada exitosamente.');
    }

    /**
     * Muestra el formulario para editar una materia.
     * (Sin cambios)
     */
    public function edit(Materia $materia)
    {
        return view('materias.edit', compact('materia'));
    }

    /**
     * Actualiza la materia.
     */
    public function update(Request $request, Materia $materia)
    {
        // **CAMBIO:** Usamos validated() para obtener solo los datos validados.
        $validatedData = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('materias')->ignore($materia->materia_id, 'materia_id'),
            ],
        ], [
            'nombre.unique' => 'La materia ya existe.',
        ]);

        // **CAMBIO:** Usamos $validatedData en lugar de $request->all().
        // Previene MassAssignmentException.
        $materia->update($validatedData);

        return redirect()->route('materias.index')
                         ->with('success', 'Materia actualizada exitosamente.');
    }

    /**
     * Elimina la materia.
     * (Sin cambios, pero asegúrate que el Route Model Binding funciona como esperas)
     */
    public function destroy(Materia $materia)
    {
        try {
            $materia->delete();
            return redirect()->route('materias.index')
                             ->with('success', 'Materia eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Considera loggear el error $e->getMessage() para depuración.
            return redirect()->route('materias.index')
                             ->with('error', 'No se puede eliminar la materia, está siendo utilizada.');
        }
    }
}