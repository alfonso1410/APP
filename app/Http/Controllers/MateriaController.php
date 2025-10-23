<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
// No son necesarios otros modelos aquí para las verificaciones
// porque usamos las relaciones del modelo Materia.

class MateriaController extends Controller
{
    /**
     * Muestra la lista de materias.
     */
    public function index()
    {
        // Usamos la relación 'camposFormativos' que definiste en el modelo Materia
        $materias = Materia::with(['camposFormativos'])
        ->orderBy('nombre')
        ->get();

        return view('materias.index', compact('materias'));
    }

    // -----------------------------------------------------------------------

    /**
     * Guarda la nueva materia.
     */
    public function store(Request $request)
    {
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
     * Actualiza la materia.
     */
    public function update(Request $request, Materia $materia)
    {
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
     * Elimina la materia, verificando dependencias primero.
     */
    public function destroy(Materia $materia)
    {
        // --- INICIO DE LA VERIFICACIÓN ---

        // 1. Verificar en Estructura Curricular (usando la relación de tu modelo)
        if ($materia->estructuraCurricular()->exists()) {
             return redirect()->route('admin.materias.index')
                             ->with('error', "No se puede eliminar '{$materia->nombre}'. Está asignada a planes de estudio.");
        }

        // 2. Verificar en Asignaciones a Grupos/Maestros (usando la relación de tu modelo)
         if ($materia->asignacionesGrupo()->exists()) {
             return redirect()->route('admin.materias.index')
                             ->with('error', "No se puede eliminar '{$materia->nombre}'. Está asignada a grupos y maestros.");
        }

        // 3. Verificar en Criterios de Evaluación (usando la relación de tu modelo)
        if ($materia->criterios()->exists()) {
            return redirect()->route('admin.materias.index')
                            ->with('error', "No se puede eliminar '{$materia->nombre}'. Tiene criterios de evaluación definidos.");
        }

        // 4. Verificar en Calificaciones (Añadir si es necesario, basado en tu estructura)
        /*
        if (\App\Models\Calificacion::where('materia_id', $materia->materia_id)->exists()) { // Asegúrate que Calificacion use materia_id
             return redirect()->route('admin.materias.index')
                              ->with('error', "No se puede eliminar '{$materia->nombre}'. Ya existen calificaciones registradas.");
        }
        */

        // --- FIN DE LA VERIFICACIÓN ---

        // Si pasó todas las verificaciones, intentamos borrar
        try {
            $materia->delete();
            return redirect()->route('admin.materias.index')
                             ->with('success', 'Materia eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Este catch actúa como seguro adicional o para restricciones de BD
            report($e); // Loguea el error real para depuración
            return redirect()->route('admin.materias.index')
                             ->with('error', 'No se pudo eliminar la materia debido a una restricción de base de datos o un error inesperado. Verifica que no esté en uso.');
        }
    }
}