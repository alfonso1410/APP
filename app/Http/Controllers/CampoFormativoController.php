<?php

namespace App\Http\Controllers;

use App\Models\CampoFormativo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Nivel;
use App\Models\Materia;

class CampoFormativoController extends Controller
{
    public function index(Request $request)
    {
        $niveles = Nivel::orderBy('nivel_id')->get();
        $activeNivelId = $request->input('nivel');

        if (is_null($activeNivelId) && $niveles->isNotEmpty()) {
            $activeNivelId = $niveles->first()->nivel_id;
        }

        $query = CampoFormativo::query();

        // --- INICIO CORRECCIÓN EAGER LOADING ---
        // 1. Quitamos distinct() más adelante.
        // 2. Cargamos 'materias' y SUS relaciones anidadas necesarias para el modal.
        $query->with([
            'materias.asignacionesGrupo.maestro', // Carga materia -> asignación -> maestro
            'materias.asignacionesGrupo.grupo.grado' // Carga materia -> asignación -> grupo -> grado
        ]);
        // --- FIN CORRECCIÓN EAGER LOADING ---

        // Lógica de filtrado (igual)
        if ($activeNivelId == '0') {
             $query->whereDoesntHave('asignacionesEstructura');
        } else if ($activeNivelId) {
             $query->whereHas('asignacionesEstructura.grado', function ($q) use ($activeNivelId) {
                 $q->where('nivel_id', $activeNivelId);
             });
        }

        // --- CORRECCIÓN: Quitamos distinct() ---
        $camposFormativos = $query->orderBy('nombre')->get(); // distinct() eliminado

        $allMaterias = Materia::orderBy('nombre')->get();

        return view('campos-formativos.index', compact(
            'camposFormativos',
            'niveles',
            'activeNivelId',
            'allMaterias'
        ));
    }

    /**
     * Guarda un nuevo campo formativo (desde el modal).
     * (Este método se mantiene como lo tenías, ya es correcto)
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:100|unique:campos_formativos,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este campo formativo ya existe.',
        ]);
        CampoFormativo::create($validatedData);
        return redirect()->route('campos-formativos.index')->with('success', 'Campo formativo creado exitosamente.');
    }

    /**
     * Actualiza un campo formativo (desde el modal).
     * (Este método se mantiene como lo tenías, ya es correcto)
     */
    public function update(Request $request, CampoFormativo $camposFormativo)
    {
        $validatedData = $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('campos_formativos')->ignore($camposFormativo->campo_id, 'campo_id'),
            ],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este campo formativo ya existe.',
        ]);
        $camposFormativo->update($validatedData);
        return redirect()->route('campos-formativos.index')->with('success', 'Campo formativo actualizado exitosamente.');
    }

    /**
     * Elimina un campo formativo.
     * (Este método se mantiene como lo tenías, ya es correcto)
     */
    public function destroy(CampoFormativo $camposFormativo)
    {
        try {
            $camposFormativo->delete();
            return redirect()->route('campos-formativos.index')->with('success', 'Campo formativo eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('campos-formativos.index')->with('error', 'No se puede eliminar el campo formativo, está siendo utilizado en una estructura curricular.');
        }
    }
}