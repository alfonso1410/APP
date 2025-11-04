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
use App\Models\Periodo;
use App\Models\RegistroAsistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CalificacionJsonController extends Controller
{
    /**
     * Devuelve los grados de un nivel.
     */
    public function getGradosPorNivel(Nivel $nivel)
    {
        $grados = Grado::where('nivel_id', $nivel->nivel_id)
                      ->where('tipo_grado', 'REGULAR')
                      ->orderBy('orden')
                      ->get(['grado_id as id', 'nombre']);
        
        $grados = $grados->map(function ($grado) {
            return [
                'id' => $grado->id ?? null,
                'nombre' => $grado->nombre ?? 'Nombre no disponible',
            ];
        });

        return response()->json($grados);
    }

    /**
     * Devuelve los grados extracurriculares.
     */
    public function getGradosExtracurriculares()
    {
        $grados = Grado::where('tipo_grado', 'EXTRA')
                      ->orderBy('nombre')
                      ->get(['grado_id as id', 'nombre']);
        
        $grados = $grados->map(function ($grado) {
            return [
                'id' => $grado->id ?? null,
                'nombre' => $grado->nombre ?? 'Nombre no disponible',
            ];
        });

        return response()->json($grados);
    }

    /**
     * Devuelve los grupos activos de un grado.
     */
    public function getGrupos(Grado $grado)
    {
        $grupos = Grupo::where('grado_id', $grado->grado_id)
                      ->where('estado', 'ACTIVO')
                      ->orderBy('nombre_grupo')
                      ->get(['grupo_id as id', 'nombre_grupo']);
        
        $grupos = $grupos->map(function ($grupo) {
            return [
                'id' => $grupo->id ?? null,
                'nombre_grupo' => $grupo->nombre_grupo ?? 'Nombre no disponible',
            ];
        });

        return response()->json($grupos);
    }

    /**
     * Devuelve las materias de un grado.
     * (Corregido para consultar ambas tablas de asignación)
     */
    public function getMaterias(Grado $grado)
    {
        // --- INICIO DE LA MODIFICACIÓN ---
        if ($grado->tipo_grado == 'REGULAR') {
            
            // 1. Obtener materias de la ESTRUCTURA CURRICULAR (Grado -> Materia)
            // (Esta es la lógica que ya tenías)
            $materia_ids_from_estructura = DB::table('estructura_curricular')
                                 ->where('grado_id', $grado->grado_id)
                                 ->distinct()
                                 ->pluck('materia_id');

            // 2. Obtener materias de ASIGNACIONES DE GRUPO (Grupo -> Materia)
            // (Esta es la lógica que faltaba y que explica el bug)
            $materia_ids_from_grupos = DB::table('grupo_materia_maestro as gmm')
                                ->join('grupos', 'gmm.grupo_id', '=', 'grupos.grupo_id')
                                ->where('grupos.grado_id', $grado->grado_id)
                                ->distinct()
                                ->pluck('gmm.materia_id');

            // 3. Combinar las dos colecciones y obtener IDs únicos
            $materia_ids_total = $materia_ids_from_estructura
                                ->merge($materia_ids_from_grupos)
                                ->unique(); // <-- Importante

            // 4. Buscar las materias
            $materias = Materia::whereIn('materia_id', $materia_ids_total)
                                ->orderBy('nombre')
                                ->get(['materia_id as id', 'nombre']);

            // Mapear para grados regulares
            $materias = $materias->map(function ($materia) {
                return [
                    'id' => $materia->id ?? null,
                    'nombre' => $materia->nombre ?? 'Nombre no disponible',
                ];
            });
        
        } else { // Si es 'EXTRA'
            
            // Lógica NUEVA para grados extracurriculares
            $materias = Materia::where('tipo', 'EXTRA')
                                ->orderBy('nombre')
                                ->get(['materia_id as id', 'nombre']);

            // Mapear para grados extracurriculares
            $materias = $materias->map(function ($materia) {
                return [
                    'id' => $materia->id ?? null,
                    'nombre' => $materia->nombre ?? 'Nombre no disponible',
                ];
            });
        }
        
        // --- FIN DE LA MODIFICACIÓN ---

        return response()->json($materias);
    }
    
    /**
     * Devuelve la tabla de alumnos y criterios para la captura.
     * (Este método ya estaba bien y no requiere cambios)
     */
    public function getTablaCalificaciones(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|integer|exists:grupos,grupo_id', 
            'materia_id' => 'required|integer|exists:materias,materia_id',
            'periodo_id' => 'required|integer|exists:periodos,periodo_id',
        ]);

        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo) {
            return response()->json(['error' => 'Periodo no encontrado'], 404);
        }

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
        
        // 1. Obtener criterios
        $materiaCriterios = MateriaCriterio::where('materia_id', $request->materia_id)
                                           ->with('catalogoCriterio') 
                                           ->orderBy('materia_criterio_id')
                                           ->get(); 

        // 2. Preparar los criterios
        $criterioPromedioId = null;
        $criterioFaltasId = null;
        $criteriosParaPromediar = []; 
        
        $criterios = $materiaCriterios->map(function ($mc) use (&$criterioPromedioId, &$criterioFaltasId, &$criteriosParaPromediar) {
            
            $nombreCriterio = $mc->catalogoCriterio->nombre ?? 'Criterio s/n';
            $esPromedio = (strcasecmp($nombreCriterio, 'Promedio') == 0);
            $esFaltas = (strcasecmp($nombreCriterio, 'Faltas') == 0);

            if ($mc->incluido_en_promedio) {
                $criteriosParaPromediar[$mc->materia_criterio_id] = $mc->ponderacion > 0 ? $mc->ponderacion : 1;
            }
            if ($esPromedio) {
                $criterioPromedioId = $mc->materia_criterio_id;
            }
            if ($esFaltas) {
                $criterioFaltasId = $mc->materia_criterio_id;
            }

            return [
                'id' => $mc->materia_criterio_id, 
                'nombre_criterio' => $nombreCriterio,
                'es_promedio' => $esPromedio,
                'es_faltas' => $esFaltas
            ];
        });

        // Reordenar "Promedio" al final
        list($promedios, $otrosCriterios) = $criterios->partition(fn ($criterio) => $criterio['es_promedio']);
        $criteriosOrdenados = $otrosCriterios->merge($promedios)->values();

        // 3. Obtener calificaciones existentes (excluyendo calculados)
        $idsACalcular = [];
        if ($criterioPromedioId) $idsACalcular[] = $criterioPromedioId;
        if ($criterioFaltasId) $idsACalcular[] = $criterioFaltasId;

        $calificacionesExistentes = Calificacion::where('periodo_id', $request->periodo_id)
            ->whereIn('alumno_id', $alumnos->pluck('id'))
            ->whereIn('materia_criterio_id', $materiaCriterios->pluck('materia_criterio_id'))
            ->when(!empty($idsACalcular), function ($query) use ($idsACalcular) {
                 return $query->whereNotIn('materia_criterio_id', $idsACalcular);
            })
            ->get();

        // 4. Mapear calificaciones Y CALCULAR PROMEDIOS
        $mapaCalificaciones = [];
        $califsPorAlumno = $calificacionesExistentes->groupBy('alumno_id');
        $promediosIndividuales = [];

        foreach ($alumnos as $alumno) {
            $mapaCalificaciones[$alumno->id] = [];
            $sumaPonderada = 0;
            $sumaPonderaciones = 0;
            
            // Calcular Faltas
            if ($criterioFaltasId) {
                $totalFaltas = RegistroAsistencia::where('alumno_id', $alumno->id)
                    // ->where('grupo_id', $grupo->grupo_id) // Descomentar si la asistencia es por grupo
                    ->where('tipo_asistencia', 'FALTA')
                    ->whereBetween('fecha', [$periodo->fecha_inicio, $periodo->fecha_fin])
                    ->count();
                $mapaCalificaciones[$alumno->id][$criterioFaltasId] = $totalFaltas;
                // (Se asume que las faltas no cuentan para el promedio ponderado)
            }

            // Procesar calificaciones GUARDADAS
            if ($califsPorAlumno->has($alumno->id)) {
                foreach ($califsPorAlumno[$alumno->id] as $cal) {
                    $criterioId = $cal->materia_criterio_id;
                    $mapaCalificaciones[$alumno->id][$criterioId] = $cal->calificacion_obtenida;

                    if (isset($criteriosParaPromediar[$criterioId]) && $criterioId != $criterioFaltasId) {
                        $ponderacion = $criteriosParaPromediar[$criterioId];
                        $sumaPonderada += $cal->calificacion_obtenida * $ponderacion;
                        $sumaPonderaciones += $ponderacion;
                    }
                }
            }

            // Calcular y asignar el PROMEDIO
            if ($criterioPromedioId) {
                if ($sumaPonderaciones > 0) {
                    $promedioCalculado = $sumaPonderada / $sumaPonderaciones;
                    $mapaCalificaciones[$alumno->id][$criterioPromedioId] = round($promedioCalculado, 2);
                    $promediosIndividuales[] = $promedioCalculado;
                } else {
                    $mapaCalificaciones[$alumno->id][$criterioPromedioId] = 0;
                }
            }
        }
        
        // 5. Calcular el promedio del GRUPO
        $promedioGrupo = 0;
        if (count($promediosIndividuales) > 0) {
            $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
        }

        // 6. Obtener nombre del maestro
        $nombreMaestro = 'Sin asignar';
        $asignacion = DB::table('grupo_materia_maestro')
                         ->where('grupo_id', $request->grupo_id)
                         ->where('materia_id', $request->materia_id)
                         ->first();

        if ($asignacion && isset($asignacion->maestro_id)) {
            $maestro = User::find($asignacion->maestro_id);
            if ($maestro) {
                $nombreMaestro = $maestro->name . ' ' . $maestro->apellido_paterno . ' ' . $maestro->apellido_materno;
            }
        }

        // 7. Devolver JSON
        return response()->json([
            'alumnos' => $alumnos,
            'criterios' => $criteriosOrdenados,
            'calificaciones' => $mapaCalificaciones,
            'promedioGrupo' => round($promedioGrupo, 2),
            'nombreMaestro' => trim($nombreMaestro)
        ]);
    }
}