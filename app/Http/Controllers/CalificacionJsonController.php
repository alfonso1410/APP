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
use App\Models\CampoFormativo; 

class CalificacionJsonController extends Controller
{
    /**
     * Devuelve los grados de un nivel académico (excluye 'EXTRA').
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
    
    // ====================================================================
    // 🔥 FUNCIÓN CLAVE CORREGIDA: Obtiene materias filtradas por GRUPO 🔥
    // (Reemplaza la lógica anterior de getMaterias(Grado $grado) para el frontend)
    // ====================================================================
    /**
     * Devuelve solo las materias que están ASIGNADAS al grupo.
     * Esto asegura que solo 'Yoga 1' aparezca para el grupo 'Yoga 1'.
     */
    public function getMateriasPorGrupo(Grupo $grupo)
    {
        // Se consulta la tabla pivote que contiene las asignaciones reales (grupo-materia).
        $materias = Materia::join('grupo_materia_maestro as gmm', 'materias.materia_id', '=', 'gmm.materia_id')
            ->where('gmm.grupo_id', $grupo->grupo_id)
            ->distinct() // Evita duplicados si hay múltiples asignaciones de maestros al mismo grupo/materia
            ->orderBy('materias.nombre')
            ->select('materias.materia_id as id', 'materias.nombre')
            ->get();
        
        $materias = $materias->map(function ($materia) {
            return [
                'id' => $materia->id ?? null,
                'nombre' => $materia->nombre ?? 'Nombre no disponible',
            ];
        });

        return response()->json($materias);
    }

    // ====================================================================
    // FUNCIONES HELPER Y CARGA DE TABLA
    // ====================================================================

    private function getPromedioMateria($alumno_id, $materia_id, $periodo_id)
    {
        $materiaCriterioPromedio = MateriaCriterio::where('materia_id', $materia_id)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Promedio');
            })
            ->select('materia_criterio_id')
            ->first();

        if (!$materiaCriterioPromedio) {
            return 0;
        }

        $calificacion = Calificacion::where('alumno_id', $alumno_id)
            ->where('periodo_id', $periodo_id)
            ->where('materia_criterio_id', $materiaCriterioPromedio->materia_criterio_id)
            ->value('calificacion_obtenida');

        return $calificacion ?? 0;
    }

    private function buildMetaMateriaTabla(Request $request, Materia $materia, Periodo $periodo, Grupo $grupo, $nombreMaestro)
    {
        $idiomaParaFaltas = null;

        $asignacion = DB::table('grupo_materia_maestro')
            ->where('grupo_id', $grupo->grupo_id)
            ->where('materia_id', $materia->materia_id)
            ->first();

        if ($asignacion) {
            $maestroAsignado = User::find($asignacion->maestro_id);
            if ($maestroAsignado) {
                $pivote = $maestroAsignado->gruposTitulares()->find($grupo->grupo_id);
                
                if ($pivote && isset($pivote->pivot->idioma)) {
                    $idiomaParaFaltas = $pivote->pivot->idioma;
                } 
                elseif ($grupo->maestrosComplementarios()->where('users.id', $maestroAsignado->id)->exists()) {
                    $idiomaParaFaltas = 'ESPAÑOL';
                }
            }
        }

        if (!$idiomaParaFaltas) {
            $idiomaParaFaltas = 'INGLES'; 
        }

        $alumnos = $grupo->alumnosActuales()
            ->where('estado_alumno', 'ACTIVO')
            ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')
            ->get(['alumnos.alumno_id as id', 'nombres', 'apellido_paterno', 'apellido_materno']);

        $nombreCampoFuente = 'English';
        
        $campoFuente = CampoFormativo::where('nombre', $nombreCampoFuente)
            ->where('nivel_id', $grupo->grado->nivel_id)
            ->first();
            
        $criterioPromedioReal = MateriaCriterio::where('materia_id', $materia->materia_id)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Promedio');
            })
            ->first();

        $criterioFaltasReal = MateriaCriterio::where('materia_id', $materia->materia_id)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Faltas');
            })
            ->first();

        if (!$campoFuente || !$criterioPromedioReal) {
            $warning = 'Error de Configuración: ';
            if (!$campoFuente) {
                $warning .= "No se encontró el campo formativo '$nombreCampoFuente'. ";
            }
            if (!$criterioPromedioReal) {
                $warning .= "La materia 'Lengua Extranjera' no tiene asignado el criterio 'Promedio'.";
            }
            return response()->json([
                'alumnos' => $alumnos, 'criterios' => [], 'calificaciones' => [], 'promedioGrupo' => 0,
                'nombreMaestro' => $nombreMaestro, 'setup_warning' => $warning
            ]);
        }
            
        
        $materiasFuente = $grupo->grado->materias()
            ->wherePivot('campo_id', $campoFuente->campo_id)
            ->orderBy('nombre')
            ->get();

        if ($materiasFuente->isEmpty()) {
                $warning = "No se encontraron materias fuente asignadas al campo '$nombreCampoFuente' para este grado.";
                return response()->json([
                    'alumnos' => $alumnos, 'criterios' => [], 'calificaciones' => [], 'promedioGrupo' => 0,
                    'nombreMaestro' => $nombreMaestro, 'setup_warning' => $warning
                ]);
        }

        $criteriosJson = [];
        foreach ($materiasFuente as $materiaFuente) {
            $criteriosJson[] = [
                'id' => $materiaFuente->materia_id, 
                'nombre_criterio' => $materiaFuente->nombre, 
                'es_promedio' => false,
                'es_faltas' => false,
                'es_calculado' => true 
            ];
        }

        if ($criterioFaltasReal) {
            $criteriosJson[] = [
                'id' => $criterioFaltasReal->materia_criterio_id, 
                'nombre_criterio' => 'Faltas', 
                'es_promedio' => false,
                'es_faltas' => true,
                'es_calculado' => true 
            ];
        }

        $criteriosJson[] = [
            'id' => $criterioPromedioReal->materia_criterio_id, 
            'nombre_criterio' => 'Promedio',
            'es_promedio' => true,
            'es_faltas' => false,
            'es_calculado' => true 
        ];

        $mapaCalificaciones = [];
        $promediosIndividuales = [];

        foreach ($alumnos as $alumno) {
            $mapaCalificaciones[$alumno->id] = [];
            $sumaCalificaciones = 0;
            $totalCalificaciones = 0;

            foreach ($materiasFuente as $materiaFuente) {
                
                $calificacion = $this->getPromedioMateria(
                    $alumno->id, 
                    $materiaFuente->materia_id, 
                    $request->periodo_id
                );
                
                $mapaCalificaciones[$alumno->id][$materiaFuente->materia_id] = $calificacion;

                if ($calificacion > 0) { 
                    $sumaCalificaciones += $calificacion;
                    $totalCalificaciones++;
                }
            }
            
            if ($criterioFaltasReal) {
                $totalFaltas = RegistroAsistencia::where('alumno_id', $alumno->id)
                    ->where('periodo_id', $request->periodo_id)
                    ->where('tipo_asistencia', 'FALTA')
                    ->where('idioma', $idiomaParaFaltas)
                    ->count();

                $mapaCalificaciones[$alumno->id][$criterioFaltasReal->materia_criterio_id] = $totalFaltas;
            }
            
            $promedioFinal = 0;
            if ($totalCalificaciones > 0) {
                $calculo = $sumaCalificaciones / $totalCalificaciones;
                $promedioFinal = floor($calculo * 100) / 100;
            }
            
            $mapaCalificaciones[$alumno->id][$criterioPromedioReal->materia_criterio_id] = $promedioFinal;
            $promediosIndividuales[] = $promedioFinal;
        }
        
        $promedioGrupo = 0;
        if (count($promediosIndividuales) > 0) {
            $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
        }

        return response()->json([
            'alumnos' => $alumnos,
            'criterios' => $criteriosJson,
            'calificaciones' => $mapaCalificaciones,
            'promedioGrupo' => round($promedioGrupo, 2),
            'nombreMaestro' => trim($nombreMaestro),
            'setup_warning' => ''
        ]);
    }

    /**
     * Devuelve la tabla de alumnos y criterios para la captura.
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

        $materia = Materia::find($request->materia_id);
        
        $nombreMaestro = 'Sin asignar'; 
        $idiomaDeLaMateria = null;
        
        $asignacion = DB::table('grupo_materia_maestro')
            ->where('grupo_id', $request->grupo_id)
            ->where('materia_id', $request->materia_id)
            ->first();

        if ($asignacion && isset($asignacion->maestro_id)) {
            $maestro = User::find($asignacion->maestro_id);

            if ($maestro) {
                $nombreMaestro = $maestro->name . ' ' . $maestro->apellido_paterno . ' ' . $maestro->apellido_materno;

                try {
                    $pivote = $maestro->gruposTitulares()->find($grupo->grupo_id);
                    
                    if ($pivote && isset($pivote->pivot->idioma)) {
                        $idiomaDeLaMateria = $pivote->pivot->idioma;
                    }
                    else {
                        $esComplementario = $grupo->maestrosComplementarios()
                            ->where('users.id', $maestro->id)
                            ->exists();

                        if ($esComplementario) {
                            $idiomaDeLaMateria = 'ESPAÑOL'; 
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Error al buscar idioma del maestro: " . $e->getMessage());
                }
            }
        }

        // --- MANEJO DE MATERIAS META ---
        if ($materia && $materia->nombre === 'Lengua Extranjera') {
            return $this->buildMetaMateriaTabla($request, $materia, $periodo, $grupo, $nombreMaestro);
        }
        // --- FIN DE MANEJO DE MATERIAS META ---

        $alumnos = $grupo->alumnosActuales() 
            ->where('estado_alumno', 'ACTIVO') 
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get(['alumnos.alumno_id as id', 'nombres', 'apellido_paterno', 'apellido_materno']);
        
        
        $materiaCriterios = MateriaCriterio::where('materia_id', $request->materia_id)
            ->with('catalogoCriterio') 
            ->orderBy('materia_criterio_id')
            ->get();

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
                'es_faltas' => $esFaltas,
                'es_calculado' => ($esPromedio || $esFaltas)
            ];
        });

        list($faltas, $criteriosSinFaltas) = $criterios->partition(fn ($c) => $c['es_faltas']);
        list($promedios, $otrosCriterios) = $criteriosSinFaltas->partition(fn ($c) => $c['es_promedio']);

        $criteriosOrdenados = $otrosCriterios->merge($faltas)->merge($promedios)->values();
        
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
            
        $mapaCalificaciones = [];
        $califsPorAlumno = $calificacionesExistentes->groupBy('alumno_id');
        $promediosIndividuales = [];

        foreach ($alumnos as $alumno) {
            $mapaCalificaciones[$alumno->id] = []; 
            $sumaPonderada = 0;
            $sumaPonderaciones = 0;
            
            // --- CÁLCULO DE FALTAS ---
            if ($criterioFaltasId) {
                $totalFaltas = 0;
                if ($idiomaDeLaMateria) { 
                    
                    $totalFaltas = RegistroAsistencia::where('alumno_id', $alumno->id)
                        ->where('periodo_id', $periodo->periodo_id) 
                        ->where('tipo_asistencia', 'FALTA')
                        ->where('idioma', $idiomaDeLaMateria) 
                        ->count();
                }
                $mapaCalificaciones[$alumno->id][$criterioFaltasId] = $totalFaltas;
            }

            // --- PROCESO DE CALIFICACIONES GUARDADAS Y PONDERACIÓN ---
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

            // --- CÁLCULO Y ASIGNACIÓN DEL PROMEDIO ---
            if ($criterioPromedioId) {
                $promedioCalculado = 0;
                if ($sumaPonderaciones > 0) {
                    $promedioCalculado = $sumaPonderada / $sumaPonderaciones;
                }
                $mapaCalificaciones[$alumno->id][$criterioPromedioId] = round($promedioCalculado, 2);
                $promediosIndividuales[] = $promedioCalculado;
            }
        }
        
        $promedioGrupo = 0;
        if (count($promediosIndividuales) > 0) {
            $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
        }

        return response()->json([
            'alumnos' => $alumnos,
            'criterios' => $criteriosOrdenados,
            'calificaciones' => $mapaCalificaciones,
            'promedioGrupo' => round($promedioGrupo, 2),
            'nombreMaestro' => trim($nombreMaestro)
        ]);
    }
}