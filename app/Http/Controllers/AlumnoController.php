<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Nivel; // Asegúrate que este 'use' exista
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse; // Importar RedirectResponse

class AlumnoController extends Controller
{
    /**
     * Muestra la lista de alumnos, filtros y maneja los modales.
     */
    public function index(Request $request): View
    {
        $nivel_id = $request->input('nivel', 0);
        $search = $request->input('search');
        $query = Alumno::query();

        // --- Lógica de filtrado (sin cambios) ---
        if ($nivel_id == 0) {
            $query->whereDoesntHave('grupos', function ($q) {
                $q->where('tipo_grupo', 'REGULAR')
                  ->where('asignacion_grupal.es_actual', 1);
            });
        } else {
            $query->whereHas('grupos', function ($q) use ($nivel_id) {
                $q->where('tipo_grupo', 'REGULAR')
                  ->where('asignacion_grupal.es_actual', 1)
                  ->whereHas('grado', function ($subQ) use ($nivel_id) {
                      $subQ->where('nivel_id', $nivel_id);
                  });
            });
        }
        $query->when($search, function ($q, $s) {
            $q->where(function ($subQ) use ($s) {
                $subQ->where('nombres', 'like', "%{$s}%")
                     ->orWhere('apellido_paterno', 'like', "%{$s}%")
                     ->orWhere('apellido_materno', 'like', "%{$s}%")
                     ->orWhere('curp', 'like', "%{$s}%");
            });
        });
        // --- Fin lógica de filtrado ---

        $alumnos = $query->with([
                            'grupos' => function ($q) { // Cargamos solo el grupo activo
                                $q->where('asignacion_grupal.es_actual', 1)->with('grado');
                            }
                        ])
                        ->orderBy('apellido_paterno')
                        ->paginate(15);

        // Pasamos solo los datos necesarios para la vista y los modales (si aplica)
        return view('alumnos.index', compact('alumnos', 'nivel_id', 'search'));
    }

    /**
     * Guarda un nuevo alumno desde el modal.
     */
    public function store(Request $request): RedirectResponse // Especificar tipo de retorno
    {
        // CORREGIDO: Usar validateWithBag('store', ...)
        $validatedData = $request->validateWithBag('store', [
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp'             => 'required|string|unique:alumnos,curp|size:18',
            'estado_alumno'    => 'required|string|in:ACTIVO,INACTIVO',
        ]);

        Alumno::create($validatedData);
        // Redirigimos de vuelta al index
        return redirect()->route('admin.alumnos.index', ['nivel' => $request->input('current_nivel_id', 0)]) // Mantenemos el filtro
                         ->with('success', 'Alumno creado exitosamente.');
    }

    /**
     * Actualiza un alumno desde el modal.
     */
    public function update(Request $request, Alumno $alumno): RedirectResponse // Especificar tipo de retorno
    {
        // CORREGIDO: Usar validateWithBag('update', ...)
        $validatedData = $request->validateWithBag('update', [
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp'             => [
                'required', 'string', 'size:18',
                Rule::unique('alumnos')->ignore($alumno->alumno_id, 'alumno_id'),
            ],
            'estado_alumno'    => 'required|string|in:ACTIVO,INACTIVO'
        ]);

        $alumno->update($validatedData);
        // Redirigimos de vuelta al index
        return redirect()->route('admin.alumnos.index', ['nivel' => $request->input('current_nivel_id', 0)]) // Mantenemos el filtro
                         ->with('success', 'Alumno actualizado exitosamente.');
    }

    /**
     * Inactiva (soft delete) un alumno.
     * (Sin cambios en la lógica, solo tipo de retorno)
     */
    public function destroy(Alumno $alumno): RedirectResponse // Especificar tipo de retorno
    {
        $nivelId = $alumno->grupos()->where('asignacion_grupal.es_actual', 1)->first()?->grado?->nivel_id ?? 0; // Obtenemos nivel antes de inactivar
        $alumno->estado_alumno = 'INACTIVO';
        // Opcional: Desasignar de grupos actuales si es necesario
        // $alumno->grupos()->updateExistingPivot($grupoId, ['es_actual' => 0]);
        $alumno->save();
        return redirect()->route('admin.alumnos.index', ['nivel' => $nivelId])
                         ->with('success', 'Alumno inactivado exitosamente.');
    }

    // --- MÉTODOS create() y edit() ELIMINADOS ---
}