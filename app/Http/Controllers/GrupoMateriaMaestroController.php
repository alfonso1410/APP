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
        // 1. Obtenemos el "Pool" de maestros (los que están en 'grupo_titular')
        $maestrosDelPool = $grupo->maestrosTitulares()->orderBy('name')->get();

        // 2. Obtenemos las MATERIAS de este grupo (según tu lógica de 'indexMaterias')
        if ($grupo->tipo_grupo === 'REGULAR') {
            $materiasDelGrupo = $grupo->grado->materias; // De la estructura
        } else {
            $materiasDelGrupo = $grupo->materias; // Asignadas directo al grupo
        }

        // 3. Obtenemos las asignaciones ACTUALES [materia_id => maestro_id]
        //    (Esto es para pre-seleccionar los dropdowns)
        $asignacionesActuales = DB::table('grupo_materia_maestro')
            ->where('grupo_id', $grupo->grupo_id)
            ->pluck('maestro_id', 'materia_id'); // Clave: materia_id, Valor: maestro_id

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