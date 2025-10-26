<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\MateriaCriterio;
use App\Models\CatalogoCriterio;
use App\Models\Alumno;
use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalificacionJsonController extends Controller
{
    /**
     * Devuelve los grupos activos de un grado.
     * (Versión explícita anti-errores)
     */
      public function getGradosPorNivel(Nivel $nivel)
    {
        // Asumo que Nivel tiene 'nivel_id' como PK
        $grados = Grado::where('nivel_id', $nivel->nivel_id)
                       ->where('tipo_grado', 'REGULAR') // Solo grados académicos
                       ->orderBy('orden') // Ordenar por 1ro, 2do, 3ro
                       ->get(['grado_id as id', 'nombre']);
        
        // Mapear para garantizar que cada objeto tenga 'id' y 'nombre'
        $grados = $grados->map(function ($grado) {
            return [
                'id' => $grado->id ?? null, // Asegura que siempre haya un valor para 'id'
                'nombre' => $grado->nombre ?? 'Nombre no disponible',
            ];
        });

        return response()->json($grados);
    }

    public function getGrupos(Grado $grado)
    {
        $grupos = Grupo::where('grado_id', $grado->grado_id)
                       ->where('estado', 'ACTIVO')
                       ->orderBy('nombre_grupo')
                       ->get(['grupo_id as id', 'nombre_grupo']);
        
        // Mapear para garantizar que cada objeto tenga 'id' y 'nombre_grupo'
        $grupos = $grupos->map(function ($grupo) {
            return [
                'id' => $grupo->id ?? null, // Asegura que siempre haya un valor para 'id'
                'nombre_grupo' => $grupo->nombre_grupo ?? 'Nombre no disponible',
            ];
        });

        return response()->json($grupos);
    }

    /**
     * Devuelve las materias de un grado.
     * (Versión explícita anti-errores)
     */
    public function getMaterias(Grado $grado)
    {
        $materia_ids = DB::table('estructura_curricular')
                         ->where('grado_id', $grado->grado_id)
                         ->distinct()
                         ->pluck('materia_id');

        $materias = Materia::whereIn('materia_id', $materia_ids)
                           ->orderBy('nombre')
                           ->get(['materia_id as id', 'nombre']);

        // Mapear para garantizar que cada objeto tenga 'id' y 'nombre'
        $materias = $materias->map(function ($materia) {
            return [
                'id' => $materia->id ?? null, // Asegura que siempre haya un valor para 'id'
                'nombre' => $materia->nombre ?? 'Nombre no disponible',
            ];
        });

        return response()->json($materias);
    }

    /**
     * Devuelve la tabla de alumnos y criterios para la captura.
     * (Esta es la versión que ya corregimos)
     */
    public function getTablaCalificaciones(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|integer|exists:grupos,grupo_id', 
            'materia_id' => 'required|integer|exists:materias,materia_id',
            'periodo_id' => 'required|integer|exists:periodos,periodo_id',
        ]);

        $grupo = Grupo::find($request->grupo_id);
        if (!$grupo) {
            return response()->json(['error' => 'Grupo no encontrado'], 404);
        }

        $alumnos = $grupo->alumnosActuales() 
                         ->where('estado_alumno', 'ACTIVO') 
                         ->orderBy('apellido_paterno')
                         ->orderBy('apellido_materno')
                         ->orderBy('nombres')
                         ->get(['alumnos.alumno_id as id', 'nombres', 'apellido_paterno', 'apellido_materno']);
        
        // 1. Obtener criterios CON sus datos de ponderación
        $materiaCriterios = MateriaCriterio::where('materia_id', $request->materia_id)
                                            ->with('catalogoCriterio') 
                                            ->orderBy('materia_criterio_id')
                                            ->get(); // Traemos la colección completa

        // 2. Preparar los criterios
        $criterioPromedioId = null;
        $criteriosParaPromediar = []; // [criterio_id => ponderacion]
        
        $criterios = $materiaCriterios->map(function ($mc) use (&$criterioPromedioId, &$criteriosParaPromediar) {
            
            // Asumo que tu modelo CatalogoCriterio tiene 'nombre'
            $nombreCriterio = $mc->catalogoCriterio->nombre ?? 'Criterio s/n';
            $esPromedio = (strcasecmp($nombreCriterio, 'Promedio') == 0); // Compara ignorando mayúsculas

            // Guardamos los datos para el cálculo
            if ($mc->incluido_en_promedio) {
                // Si la ponderación es 0 o nula, trátala como 1 (promedio simple)
                $criteriosParaPromediar[$mc->materia_criterio_id] = $mc->ponderacion > 0 ? $mc->ponderacion : 1;
            }
            
            if ($esPromedio) {
                $criterioPromedioId = $mc->materia_criterio_id;
            }

            return [
                'id' => $mc->materia_criterio_id, 
                'nombre_criterio' => $nombreCriterio,
                // Le pasamos esta info al frontend para que pueda deshabilitar el input
                'es_promedio' => $esPromedio 
            ];

            
        });
        // ==========================================================
        // == INICIO DE CORRECCIÓN: Reordenar "Promedio" al final ==
        // ==========================================================

        // 1. Particionamos la colección.
        // $promedios tendrá los que 'es_promedio' = true
        // $otrosCriterios tendrá los que 'es_promedio' = false
        list($promedios, $otrosCriterios) = $criterios->partition(function ($criterio) {
            return $criterio['es_promedio'];
        });

        // 2. Unimos las dos colecciones, poniendo los 'otros' primero
        // y el 'promedio' al final.
        // Usamos values() para resetear las keys y asegurar que sea un array JSON.
        $criteriosOrdenados = $otrosCriterios->merge($promedios)->values();
        // 3. Obtener calificaciones existentes
        $calificacionesExistentes = Calificacion::where('periodo_id', $request->periodo_id)
            ->whereIn('alumno_id', $alumnos->pluck('id'))
            ->whereIn('materia_criterio_id', $materiaCriterios->pluck('materia_criterio_id'))
            ->get();
        
        // 4. Mapear calificaciones Y CALCULAR PROMEDIOS
        $mapaCalificaciones = [];
        $califsPorAlumno = $calificacionesExistentes->groupBy('alumno_id');

        $promediosIndividuales = [];

        foreach ($alumnos as $alumno) {
            $mapaCalificaciones[$alumno->id] = [];
            $sumaPonderada = 0;
            $sumaPonderaciones = 0;
            
            if ($califsPorAlumno->has($alumno->id)) {
                foreach ($califsPorAlumno[$alumno->id] as $cal) {
                    $criterioId = $cal->materia_criterio_id;
                    $mapaCalificaciones[$alumno->id][$criterioId] = $cal->calificacion_obtenida;

                    if (isset($criteriosParaPromediar[$criterioId])) {
                        $ponderacion = $criteriosParaPromediar[$criterioId];
                        $sumaPonderada += $cal->calificacion_obtenida * $ponderacion;
                        $sumaPonderaciones += $ponderacion;
                    }
                }
            }

            if ($criterioPromedioId && $sumaPonderaciones > 0) {
                $promedioCalculado = $sumaPonderada / $sumaPonderaciones;
                $mapaCalificaciones[$alumno->id][$criterioPromedioId] = round($promedioCalculado, 2);
                
                $promediosIndividuales[] = $promedioCalculado; // <-- 2. Guardar promedio
            }
        }
        
        // 3. Calcular el promedio del GRUPO
        $promedioGrupo = 0;
        if (count($promediosIndividuales) > 0) {
            $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
        }

        return response()->json([
            'alumnos' => $alumnos,
            'criterios' => $criteriosOrdenados,
            'calificaciones' => $mapaCalificaciones,
            'promedioGrupo' => round($promedioGrupo, 2) // <-- 4. Enviar nuevo dato
        ]);
    }
}
