<?php

namespace App\Http\Controllers;

use App\Models\CampoFormativo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Nivel;
use App\Models\Materia; // Asegúrate de que este 'use' exista

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

        // --- INICIO CORRECCIÓN: Eager Loading ---
        // 1. La consulta ahora carga la relación correcta para el GRADO.
        $query = CampoFormativo::query()->with([
            'materias.asignacionesGrupo.maestro', // Para la columna "Profesor"
            'materias.grados',                  // ✅ NUEVA SOLUCIÓN: Carga todos los grados de la materia
            'nivel' // Para el filtro de Nivel
        ]);

        // 2. Lógica de filtrado (sin cambios)
        if ($activeNivelId) {
            $query->where('nivel_id', $activeNivelId);
        }
        // --- FIN CORRECCIÓN ---

        $camposFormativos = $query->orderBy('nombre')->get();

        // Esto es necesario para el modal de "Ver Materias", aunque no lo usemos aquí.
        // Lo dejamos por si acaso, pero no es parte del error.
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
     * (Este método ya estaba corregido en nuestra iteración anterior)
     */
    public function store(Request $request)
    {
        // Usamos 'validateWithBag' para manejar errores en el modal 'create'
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

        return redirect()->route('campos-formativos.index', ['nivel' => $request->nivel_id])
                         ->with('success', 'Campo formativo creado exitosamente.');
    }

    /**
     * Actualiza un campo formativo.
     * (Este método ya estaba corregido en nuestra iteración anterior)
     */
    public function update(Request $request, CampoFormativo $camposFormativo)
    {
        // Usamos 'validateWithBag' para manejar errores en el modal 'edit'
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

        return redirect()->route('campos-formativos.index', ['nivel' => $request->nivel_id])
                         ->with('success', 'Campo formativo actualizado exitosamente.');
    }

    /**
     * Elimina un campo formativo.
     * (Este método ya estaba corregido en nuestra iteración anterior)
     */
    public function destroy(CampoFormativo $camposFormativo)
    {
        try {
            $nivelId = $camposFormativo->nivel_id;
            $camposFormativo->delete();
            
            return redirect()->route('campos-formativos.index', ['nivel' => $nivelId])
                             ->with('success', 'Campo formativo eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('campos-formativos.index', ['nivel' => $camposFormativo->nivel_id])
                             ->with('error', 'No se puede eliminar el campo formativo, está siendo utilizado.');
        }
    }
}