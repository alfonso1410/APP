<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Alumno;
use Illuminate\Support\Facades\DB;
class AsignacionGrupalController extends Controller
{
    public function create(Grupo $grupo)
{
    // Obtenemos los IDs de los alumnos que ya están asignados a ESTE grupo.
    $idsAlumnosAsignados = $grupo->alumnosActuales()->pluck('alumnos.alumno_id')->toArray();

    $query = Alumno::where('estado_alumno', 'ACTIVO');

    // --- LÓGICA CONDICIONAL ---
    if ($grupo->tipo_grupo === 'REGULAR') {
        // Regla: Mostrar solo alumnos que NO tengan un grupo regular activo.
        $query->whereDoesntHave('grupos', function ($q) {
            $q->where('tipo_grupo', 'REGULAR')->where('asignacion_grupal.es_actual', true);
        });

    } else { // tipo_grupo es 'EXTRA'

        // --- INICIO DE LA LÓGICA MEJORADA ---

        // 1. Obtenemos los IDs de los grados regulares PERMITIDOS según el mapeo.
        $idsGradosPermitidos = $grupo->grado->gradosRegularesMapeados()->pluck('grados.grado_id');

        // 2. Si no hay grados mapeados, nadie es elegible.
        if ($idsGradosPermitidos->isEmpty()) {
            $query->whereRaw('1 = 0'); // Una forma de forzar que la consulta no devuelva nada.
        } else {
            // Regla 1 (MODIFICADA): DEBE tener un grupo regular activo Y el grado de ese grupo
            // DEBE estar en la lista de grados permitidos.
            $query->whereHas('grupos', function ($q) use ($idsGradosPermitidos) {
                $q->where('tipo_grupo', 'REGULAR')
                  ->where('asignacion_grupal.es_actual', true)
                  ->whereIn('grupos.grado_id', $idsGradosPermitidos); // <-- ¡LA LÍNEA CLAVE QUE SOLUCIONA EL PROBLEMA!
            });

            // Regla 2 (sin cambios): NO DEBE tener OTRO grupo extracurricular activo.
            $query->whereDoesntHave('grupos', function ($q) {
                $q->where('tipo_grupo', 'EXTRA')->where('asignacion_grupal.es_actual', true);
            });
        }
        // --- FIN DE LA LÓGICA MEJORADA ---
    }

    // Unimos los alumnos elegibles con los que ya estaban asignados (para que sigan apareciendo marcados)
      $alumnosElegibles = $query->with(['grupos.materias']) // <-- ¡LÍNEA CLAVE!
                             ->orderBy('apellido_paterno')
                             ->get();

    $alumnosYaAsignados = Alumno::whereIn('alumno_id', $idsAlumnosAsignados)
                                ->with(['grupos.materias']) // <-- También aquí por si acaso.
                                ->get();
    
    $alumnosDisponibles = $alumnosElegibles->merge($alumnosYaAsignados)->unique('alumno_id')->sortBy('apellido_paterno');

    return view('grupos.alumnos', compact('grupo', 'alumnosDisponibles', 'idsAlumnosAsignados'));
}
    /**
     * Procesa y guarda las asignaciones de alumnos.
     * Reemplaza el antiguo 'storeAlumnos' y el método sync().
     */
  public function store(Request $request, Grupo $grupo)
{
    $request->validate([
        'alumnos' => 'nullable|array',
        'alumnos.*' => 'exists:alumnos,alumno_id',
    ]);

    $alumnosIdsSeleccionados = $request->input('alumnos', []);
    $tipoGrupoAAsignar = $grupo->tipo_grupo; // Guardamos el tipo de grupo actual (REGULAR o EXTRA)

   
    try {
        DB::transaction(function () use ($grupo, $alumnosIdsSeleccionados, $tipoGrupoAAsignar) {

            // --- INICIO: NUEVA LÓGICA DE VALIDACIÓN ---
            if ($tipoGrupoAAsignar === 'REGULAR') {
                // 1. Identificamos qué alumnos se están intentando desvincular (los deseleccionados)
                $idsActuales = $grupo->alumnosActuales()->pluck('alumnos.alumno_id')->toArray();
                $idsADesvincular = array_diff($idsActuales, $alumnosIdsSeleccionados);

                if (!empty($idsADesvincular)) {
                    // 2. Revisamos cada alumno que se va a desvincular
                    $alumnosADesvincular = Alumno::with('grupos')->findMany($idsADesvincular);

                    foreach ($alumnosADesvincular as $alumno) {
                        // 3. Verificamos si tiene un grupo extracurricular activo
                        $tieneGrupoExtra = $alumno->grupos()
                            ->where('tipo_grupo', 'EXTRA')
                            ->wherePivot('es_actual', 1)
                            ->exists();

                        if ($tieneGrupoExtra) {
                            // 4. Si lo tiene, lanzamos un error y detenemos todo.
                            throw new \Exception("El alumno {$alumno->nombres} {$alumno->apellido_paterno} no puede ser desvinculado del grupo regular porque está inscrito en un grupo extracurricular.");
                        }
                    }
                }
            }
            // --- FIN: NUEVA LÓGICA DE VALIDACIÓN ---


            // La lógica existente para asignar/actualizar alumnos se mantiene igual
            DB::table('asignacion_grupal')
                ->join('grupos', 'asignacion_grupal.grupo_id', '=', 'grupos.grupo_id')
                ->where('grupos.tipo_grupo', $tipoGrupoAAsignar)
                ->whereIn('asignacion_grupal.alumno_id', $alumnosIdsSeleccionados)
                ->update(['es_actual' => 0]);

            foreach ($alumnosIdsSeleccionados as $alumnoId) {
                // (Lógica para asignar y verificar dependencia de grupo regular si es EXTRA...)
                // ... esta parte no cambia ...
                $alumno = Alumno::find($alumnoId);
                if ($tipoGrupoAAsignar === 'EXTRA') {
                    $tieneGrupoRegular = $alumno->grupos()->where('tipo_grupo', 'REGULAR')->wherePivot('es_actual', 1)->exists();
                    if (!$tieneGrupoRegular) {
                        throw new \Exception("El alumno {$alumno->nombres} {$alumno->apellido_paterno} no tiene un grupo regular activo.");
                    }
                }
                
                DB::table('asignacion_grupal')->updateOrInsert(
                    ['alumno_id' => $alumnoId, 'grupo_id'  => $grupo->grupo_id],
                    ['es_actual'  => 1, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // Desvinculamos a los alumnos que fueron deseleccionados (y que ya pasaron la validación)
            DB::table('asignacion_grupal')
              ->where('grupo_id', $grupo->grupo_id)
              ->whereNotIn('alumno_id', $alumnosIdsSeleccionados)
              ->update(['es_actual' => 0]);

        });

    } catch (\Exception $e) {
        return back()->with('error', 'Operación fallida: ' . $e->getMessage())->withInput();
    }

    return redirect()->route('admin.grupos.alumnos.index', $grupo)->with('success', 'Alumnos asignados correctamente.');
}
}
