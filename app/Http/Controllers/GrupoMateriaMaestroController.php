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
        // 1. Obtenemos las asignaciones (Español e Inglés) y cargamos los usuarios
        $asignaciones = $grupo->asignacionesTitulares()
                             ->with('titular', 'auxiliar') // Carga los modelos User
                             ->get();

        // 2. Creamos el "Pool" de maestros extrayendo los usuarios de las asignaciones
        $maestrosDelPool = $asignaciones->map(function ($asignacion) {
            // Map crea una lista única
            return $asignacion->titular;
        })
        ->filter()       // Quita cualquier 'null' (si un puesto está vacío)
        ->unique('id')   // Se asegura que cada maestro esté solo una vez
        ->sortBy('name') // Ordena la lista por nombre
        ->values();      // Re-indexa la colección

        // 3. Obtenemos las MATERIAS de este grupo (según tu lógica de 'indexMaterias')
        if ($grupo->tipo_grupo === 'REGULAR') {
            $materiasDelGrupo = $grupo->grado->materias; // De la estructura
        } else {
            $materiasDelGrupo = $grupo->materias; // Asignadas directo al grupo
        }

        // 4. Obtenemos las asignaciones ACTUALES [materia_id => maestro_id]
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