<?php

namespace App\Http\Controllers;

use App\Models\CampoFormativo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Nivel;
use App\Models\Materia; // Necesario para el with() en index()

class CampoFormativoController extends Controller
{
    /**
     * Muestra la lista de campos formativos, filtrada por nivel.
     */
    public function index(Request $request)
    {
        $niveles = Nivel::orderBy('nivel_id')->get();
        $activeNivelId = $request->input('nivel');

        if (is_null($activeNivelId) && $niveles->isNotEmpty()) {
            $activeNivelId = $niveles->first()->nivel_id;
        }

        // --- CORRECCIÓN: Eager Loading ---
        // La consulta ahora carga relaciones necesarias para la vista
        $query = CampoFormativo::query()->with([
            // Asumo que 'materias.asignacionesGrupo.maestro' y 'materias.grados'
            // son para algún modal o detalle, no directamente para la lista de campos.
            // Si no los usas en esta vista, puedes quitarlos para optimizar.
            'materias.asignacionesGrupo.maestro',
            'materias.grados',
            'nivel' // Necesario para el filtro de Nivel
        ]);

        if ($activeNivelId) {
            $query->where('nivel_id', $activeNivelId);
        }
        // --- FIN CORRECCIÓN ---

        $camposFormativos = $query->orderBy('nombre')->get();

        // Necesario para el modal de "Ver Materias" (si lo usas)
        $allMaterias = Materia::orderBy('nombre')->get();

        return view('campos-formativos.index', compact(
            'camposFormativos',
            'niveles',
            'activeNivelId',
            'allMaterias'
        ));
    }

    /**
     * Guarda un nuevo campo formativo.
     * (Sin cambios)
     */
    public function store(Request $request)
    {
        $validatedData = $request->validateWithBag('store', [
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('campos_formativos')->where(function ($query) use ($request) {
                    return $query->where('nivel_id', $request->nivel_id);
                }),
            ],
            'nivel_id' => 'required|integer|exists:niveles,nivel_id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este campo formativo ya existe para el nivel seleccionado.',
            'nivel_id.required' => 'Debe seleccionar un nivel educativo.',
        ]);

        CampoFormativo::create($validatedData);

        return redirect()->route('admin.campos-formativos.index', ['nivel' => $request->nivel_id])
                         ->with('success', 'Campo formativo creado exitosamente.');
    }

    /**
     * Actualiza un campo formativo.
     * (Sin cambios)
     */
    public function update(Request $request, CampoFormativo $camposFormativo)
    {
        $validatedData = $request->validateWithBag('update', [
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('campos_formativos')->where(function ($query) use ($request) {
                    return $query->where('nivel_id', $request->nivel_id);
                })->ignore($camposFormativo->campo_id, 'campo_id'),
            ],
            'nivel_id' => 'required|integer|exists:niveles,nivel_id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este campo formativo ya existe para el nivel seleccionado.',
        ]);

        $camposFormativo->update($validatedData);

        return redirect()->route('admin.campos-formativos.index', ['nivel' => $request->nivel_id])
                         ->with('success', 'Campo formativo actualizado exitosamente.');
    }

    /**
     * Elimina un campo formativo, verificando dependencias primero.
     */
    public function destroy(CampoFormativo $camposFormativo)
    {
        // --- INICIO DE LA VERIFICACIÓN ---
        // Usamos la relación 'asignacionesEstructura' del modelo CampoFormativo
        // para ver si este campo está siendo usado en la tabla estructura_curricular.
        if ($camposFormativo->asignacionesEstructura()->exists()) {
            return redirect()->route('admin.campos-formativos.index', ['nivel' => $camposFormativo->nivel_id])
                            ->with('error', "No se puede eliminar '{$camposFormativo->nombre}'. Está asignado a materias en la estructura curricular.");
        }
        // --- FIN DE LA VERIFICACIÓN ---

        // Si pasó la verificación, intentamos borrar
        try {
            $nivelId = $camposFormativo->nivel_id; // Guardamos el nivel para la redirección
            $camposFormativo->delete();

            return redirect()->route('admin.campos-formativos.index', ['nivel' => $nivelId])
                             ->with('success', 'Campo formativo eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Este catch actúa como seguro adicional o para restricciones de BD
            report($e); // Loguea el error real
            return redirect()->route('admin.campos-formativos.index', ['nivel' => $camposFormativo->nivel_id])
                             ->with('error', 'No se pudo eliminar el campo formativo debido a una restricción de base de datos o un error inesperado. Verifica que no esté en uso.');
        }
    }
}