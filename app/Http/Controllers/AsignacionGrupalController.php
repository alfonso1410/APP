<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Alumno;
use Illuminate\Support\Facades\DB;

class AsignacionGrupalController extends Controller
{
    /**
     * Mostrar formulario de asignación para un grupo.
     */
    public function create(Grupo $grupo)
{
    $idsAlumnosAsignados = $grupo->alumnosActuales->pluck('alumno_id')->toArray();

    $query = Alumno::where('estado_alumno', 'ACTIVO');

    if ($grupo->tipo_grupo === 'REGULAR') {
        $query->whereDoesntHave('grupos', function ($q) {
            $q->where('tipo_grupo', 'REGULAR')->where('asignacion_grupal.es_actual', 1);
        });
    } else {
        $idsGradosPermitidos = $grupo->grado->gradosRegularesMapeados()->pluck('grados.grado_id');

        if ($idsGradosPermitidos->isEmpty()) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereHas('grupos', function ($q) use ($idsGradosPermitidos) {
                $q->where('tipo_grupo', 'REGULAR')
                  ->where('asignacion_grupal.es_actual', 1)
                  ->whereIn('grupos.grado_id', $idsGradosPermitidos);
            });

            $query->whereDoesntHave('grupos', function ($q) {
                $q->where('tipo_grupo', 'EXTRA')->where('asignacion_grupal.es_actual', 1);
            });
        }
    }

    // Cargar solo grupos ACTIVOS con asignacion vigente
    $alumnosElegibles = $query->with(['grupos' => function ($q) {
            $q->where('asignacion_grupal.es_actual', 1)
              ->where('grupos.estado', 'ACTIVO');
        }, 'grupos.materias'])
        ->orderByRaw("apellido_paterno COLLATE utf8mb4_unicode_ci ASC")
        ->orderByRaw("apellido_materno COLLATE utf8mb4_unicode_ci ASC")
        ->get();

    $alumnosYaAsignados = Alumno::whereIn('alumno_id', $idsAlumnosAsignados)
        ->with(['grupos' => function ($q) {
            $q->where('asignacion_grupal.es_actual', 1)
              ->where('grupos.estado', 'ACTIVO');
        }, 'grupos.materias'])
        ->get();

    // Ordenamiento de Collection que maneja acentos
    $alumnosDisponibles = $alumnosElegibles
        ->merge($alumnosYaAsignados)
        ->unique('alumno_id')
        ->sortBy(function ($alumno) {
            return \Illuminate\Support\Str::ascii($alumno->apellido_paterno) 
                 . ' ' . \Illuminate\Support\Str::ascii($alumno->apellido_materno);
        })
        ->values();

    return view('grupos.alumnos', compact('grupo', 'alumnosDisponibles', 'idsAlumnosAsignados'));
}

    /**
     * Procesa y guarda las asignaciones de alumnos.
     */
    public function store(Request $request, Grupo $grupo)
    {
        $request->validate([
            'alumnos' => 'nullable|array',
            'alumnos.*' => 'exists:alumnos,alumno_id',
        ]);

        $alumnosIdsSeleccionados = $request->input('alumnos', []);
        $tipoGrupoAAsignar = $grupo->tipo_grupo; // REGULAR o EXTRA

        try {
            DB::transaction(function () use ($grupo, $alumnosIdsSeleccionados, $tipoGrupoAAsignar) {

                // --- VALIDACIONES ANTES DE CAMBIAR ---
                if ($tipoGrupoAAsignar === 'REGULAR') {
                    // IDs actuales en este grupo
                    $idsActualesEnGrupo = $grupo->alumnosActuales->pluck('alumno_id')->toArray();
                    $idsADesvincular = array_diff($idsActualesEnGrupo, $alumnosIdsSeleccionados);

                    if (!empty($idsADesvincular)) {
                        $alumnosADesvincular = Alumno::with('grupos')->findMany($idsADesvincular);

                        foreach ($alumnosADesvincular as $alumno) {
                            // Si tiene EXTRA activo, no permitir desvinculación del REGULAR
                            $tieneGrupoExtra = $alumno->grupos()
                                ->where('tipo_grupo', 'EXTRA')
                                ->wherePivot('es_actual', 1)
                                ->exists();

                            if ($tieneGrupoExtra) {
                                throw new \Exception("El alumno {$alumno->nombres} {$alumno->apellido_paterno} no puede ser desvinculado del grupo regular porque está inscrito en un grupo extracurricular.");
                            }
                        }
                    }
                }

                // --- DESACTIVAR ASIGNACIONES ACTIVAS PREVIAS DEL MISMO TIPO ---
                // Esto evita duplicados en cualquier grado o grupo del mismo tipo.
                if (!empty($alumnosIdsSeleccionados)) {
                    DB::table('asignacion_grupal AS ag')
                        ->join('grupos AS g', 'ag.grupo_id', '=', 'g.grupo_id')
                        ->whereIn('ag.alumno_id', $alumnosIdsSeleccionados)
                        ->where('g.tipo_grupo', $tipoGrupoAAsignar)
                        ->update(['ag.es_actual' => 0]);
                }

                // --- INSERTAR / REACTIVAR ASIGNACIONES EN EL GRUPO SELECCIONADO ---
                foreach ($alumnosIdsSeleccionados as $alumnoId) {
                    $alumno = Alumno::find($alumnoId);

                    // Si es EXTRA, verificar que tenga grupo REGULAR activo
                    if ($tipoGrupoAAsignar === 'EXTRA') {
                        $tieneGrupoRegular = $alumno->grupos()
                            ->where('tipo_grupo', 'REGULAR')
                            ->wherePivot('es_actual', 1)
                            ->exists();

                        if (!$tieneGrupoRegular) {
                            throw new \Exception("El alumno {$alumno->nombres} {$alumno->apellido_paterno} no tiene grupo regular activo.");
                        }
                    }

                    DB::table('asignacion_grupal')->updateOrInsert(
                        [
                            'alumno_id' => $alumnoId,
                            'grupo_id'  => $grupo->grupo_id
                        ],
                        [
                            'es_actual'  => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }

                // --- DESVINCULAR (marcar es_actual = 0) a los alumnos que fueron deseleccionados EN ESTE GRUPO ---
                DB::table('asignacion_grupal')
                    ->where('grupo_id', $grupo->grupo_id)
                    ->whereNotIn('alumno_id', $alumnosIdsSeleccionados ?: [0]) // evita whereNotIn vacío
                    ->update(['es_actual' => 0]);

            }, 5); // reintentos de transacción si deadlock
        } catch (\Exception $e) {
            return back()->with('error', 'Operación fallida: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.grupos.alumnos.index', $grupo)->with('success', 'Alumnos asignados correctamente.');
    }
}
