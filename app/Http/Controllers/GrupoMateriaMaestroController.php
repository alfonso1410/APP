<?php

namespace App\Http\Controllers;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class GrupoMateriaMaestroController extends Controller
{
    /**
     * Muestra el formulario para asignar maestros (del pool) a materias (del grupo).
     */
public function create(Grupo $grupo)
    {
        // 1. Obtenemos los TITULARES
      $soloTitulares = $grupo->asignacionesTitulares()
            ->with('titular') // Solo cargamos al titular
            ->get()
            ->map(function ($asignacion) {
                return $asignacion->titular; // <--- AQUÍ EL CAMBIO: Solo retornamos al titular
            })
            ->filter(); // Elimina nulos por seguridad
        // 2. Obtenemos los COMPLEMENTARIOS (Computación, Fe, etc.)
        // Esta relación la creamos en los pasos anteriores en el modelo Grupo
        $complementarios = $grupo->maestrosComplementarios; 

        // 3. UNIMOS ambas listas para crear el "Pool Completo"
        $maestrosDelPool = $soloTitulares
            ->merge($complementarios) // Unimos las dos colecciones
            ->unique('id')            // Evitamos duplicados (por si un titular también se marcó como complementario)
            ->sortBy('name')          // Ordenamos alfabéticamente
            ->values();               // Re-indexamos para que la vista lo lea bien

        // 4. Obtenemos las MATERIAS de este grupo
        if ($grupo->tipo_grupo === 'REGULAR') {
            $materiasDelGrupo = $grupo->grado->materias; 
        } else {
            $materiasDelGrupo = $grupo->materias; 
        }

        // 5. Obtenemos las asignaciones ACTUALES [materia_id => maestro_id]
        $asignacionesActuales = DB::table('grupo_materia_maestro')
            ->where('grupo_id', $grupo->grupo_id)
            ->pluck('maestro_id', 'materia_id');

        return view('grupos.materias-maestros-form', compact(
            'grupo',
            'maestrosDelPool',
            'materiasDelGrupo',
            'asignacionesActuales'
        ));
    }
    /**
     * Guarda las asignaciones en la tabla 'grupo_materia_maestro'.
     */
    public function store(Request $request, Grupo $grupo)
    {
        // El $request->input('materias') vendrá como un array:
        // [ materia_id_1 => maestro_id_A, materia_id_2 => maestro_id_B, ... ]
        $asignaciones = $request->input('materias', []);

        // 1. Borramos las asignaciones ANTERIORES solo para este grupo
        DB::table('grupo_materia_maestro')->where('grupo_id', $grupo->grupo_id)->delete();

        // 2. Preparamos los nuevos datos para insertar
        $datosAInsertar = [];
        foreach ($asignaciones as $materiaId => $maestroId) {
            // Solo insertamos si se seleccionó un maestro (no es 'Sin Asignar')
            if (!empty($maestroId)) {
                $datosAInsertar[] = [
                    'grupo_id' => $grupo->grupo_id,
                    'materia_id' => $materiaId,
                    'maestro_id' => $maestroId, // Asumiendo que tu columna se llama 'maestro_id'
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 3. Insertamos todos los nuevos registros
        if (!empty($datosAInsertar)) {
            DB::table('grupo_materia_maestro')->insert($datosAInsertar);
        }

        // 4. Redirigimos de vuelta a la lista de materias (donde se verá la tabla actualizada)
        return redirect()->route('admin.grupos.materias.index', $grupo)
                         ->with('success', 'Maestros asignados a las materias exitosamente.');
    }
}