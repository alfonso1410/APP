<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Periodo;
use App\Models\ActividadMateria;
use App\Models\CalificacionActividad;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\MateriaCriterio;
use Exception;
class ActividadService {

public function crearActividad(array $datos, array $gruposReplicarIds = []): ActividadMateria
    {
        $periodo = Periodo::findOrFail($datos['periodo_id']);
        if ($periodo->estado !== 'ABIERTO') {
            throw new Exception('El periodo está cerrado. No se pueden crear actividades.');
        }

        return DB::transaction(function () use ($datos, $gruposReplicarIds) {
            // Crear la actividad 
            $actividad = ActividadMateria::create([
                'grupo_id'            => $datos['grupo_id'],
                'materia_id'          => $datos['materia_id'],
                'materia_criterio_id' => $datos['materia_criterio_id'],
                'periodo_id'          => $datos['periodo_id'],
                'maestro_id'          => $datos['maestro_id'] ?? auth()->id(),
                'nombre_actividad'    => $datos['nombre_actividad'],
                'descripcion'         => $datos['descripcion'] ?? null,
                'fecha_actividad'     => $datos['fecha_actividad'],
                'valor_maximo'        => $datos['valor_maximo'] ?? 10.00,
                'calculado_por'       => null,
                'calculado_en'        => null,
            ]);

            // Obtener alumnos activos del grupo 
            $grupo = Grupo::findOrFail($datos['grupo_id']);
            $alumnosIds = $grupo->alumnos()->wherePivot('es_actual', true)->pluck('alumnos.alumno_id');

            // Precargar calificaciones_actividad 
            $calificacionesBatch = [];
            foreach ($alumnosIds as $alumnoId) {
                $calificacionesBatch[] = [
                    'actividad_id'          => $actividad->actividad_id,
                    'alumno_id'             => $alumnoId,
                    'calificacion_obtenida' => null,
                    'estado_entrega'        => 'ENTREGADO',
                    'observaciones'         => null,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ];
            }

            if (! empty($calificacionesBatch)) {
                DB::table('calificaciones_actividad')->insert($calificacionesBatch);
            }

            //  Si se marcaron grupos adicionales, replicar como copias independientes
            if (! empty($gruposReplicarIds)) {
                $this->replicarActividad($actividad->actividad_id, $gruposReplicarIds, $actividad->maestro_id);
            }

            return $actividad;
        });
    }

public function calcularCriterio(int $grupoId, int $materiaId, int $periodoId, int $materiaCriterioId, ?int $userId = null){

    $userId = $userId ?? auth()->id();

    $periodo = Periodo::FindOrFail($periodoId);
    if($periodo->estado !== "ABIERTO"){
        throw new Exception("El periodo esta cerrado. No se puede realizar el calculo de calificaciones");
    }
    //obtener actividades por criterio
    return DB::transaction(function () use ($grupoId, $materiaId, $periodoId, $materiaCriterioId, $userId ){
        $actividades = ActividadMateria::where('grupo_id', $grupoId)
        ->where('materia_id', $materiaId)
        ->where('periodo_id', $periodoId)
        ->where('materia_criterio_id', $materiaCriterioId)
        ->with('calificaciones')->get();
      
        if($actividades->isEmpty()){
            return[
                'criterio_id' => $materiaCriterioId,
                'procesados' => 0,
                'mensaje' => "No hay actividades registradas en este criterio",
            ];
        }
 
        $grupo = Grupo::FindOrFail($grupoId);
        $alumnos = $grupo->alumnos()->wherePivot('es_actual', true)->pluck('alumnos.alumno_id');
 
        $procesados = 0;

        foreach($alumnos as $alumnoId){
            $numerador = 0.0;
            $denominador = 0.0;
            $tieneRegistros = false;

            foreach($actividades as $actividad){
                $calificacion = $actividad->calificaciones->firstWhere('alumno_id', $alumnoId);
              
                if(!$calificacion){
                    continue;
                }

                if($calificacion->estado_entrega === "FALTA_JUSTIFICADA"){ 
                    continue;

                }

                $tieneRegistros = true;
                $denominador += (float) $actividad->valor_maximo;
                
                if($calificacion->estado_entrega === "ENTREGADO"){
                    $numerador += (float) ($calificacion->calificacion_obtenida ?? 0);
                }
            }

            if($denominador === 0.0 || !$tieneRegistros){
                continue;
            }
            $promedioBase10 = ($numerador / $denominador) * 10;
            $promedioRedondeado = round($promedioBase10, 2);

            Calificacion::UpdateOrCreate([
                
                'alumno_id' => $alumnoId,
                'materia_criterio_id' => $materiaCriterioId,
                'periodo_id' => $periodoId,
                
            ],[
                'calificacion_obtenida' => $promedioRedondeado,
                'updated_at' => now()
            ]);
            $procesados++;
        }
    ActividadMateria::where('grupo_id', $grupoId)
                    ->where('materia_id', $materiaId)
                    ->where('periodo_id', $periodoId)
                    ->where('materia_criterio_id', $materiaCriterioId)
                    ->update([
                        'calculado_en' => now(),
                        'calculado_por' => $userId
                    ]);
    return [
        'criterio_id' => $materiaCriterioId,
        'procesados' => $procesados,
        'estado' => 'al_dia',
    ];
    });

   

}

public function sincronizarTodoElGrupo(int $grupoId, int $materiaId, int $periodoId, ?int $userId = null): array
    {
        $userId = $userId ?? auth()->id();

        // Obtener los criterios que tienen actividades asignadas
        $criteriosConActividades = ActividadMateria::where('grupo_id', $grupoId)
            ->where('materia_id', $materiaId)
            ->where('periodo_id', $periodoId)
            ->pluck('materia_criterio_id')
            ->unique();

        $resumen = [];

        foreach ($criteriosConActividades as $criterioId) {
            $resumen[] = $this->calcularCriterio($grupoId, $materiaId, $periodoId, $criterioId, $userId);
        }

        return $resumen;
    }

public function replicarActividad(int $actividadOrigenId, array $gruposDestinoIds, int $maestroId): array
    {
        $origen = ActividadMateria::findOrFail($actividadOrigenId);
        $actividadesCreadas = [];

        DB::transaction(function () use ($origen, $gruposDestinoIds, $maestroId, &$actividadesCreadas) {
            foreach ($gruposDestinoIds as $grupoId) {
                // Evitar clonar sobre el grupo original
                if ((int) $grupoId === (int) $origen->grupo_id) {
                    continue;
                }

                $clon = ActividadMateria::create([
                    'grupo_id'            => $grupoId,
                    'materia_id'          => $origen->materia_id,
                    'materia_criterio_id' => $origen->materia_criterio_id,
                    'periodo_id'          => $origen->periodo_id,
                    'maestro_id'          => $maestroId,
                    'nombre_actividad'    => $origen->nombre_actividad,
                    'descripcion'         => $origen->descripcion,
                    'fecha_actividad'     => $origen->fecha_actividad,
                    'valor_maximo'        => $origen->valor_maximo,
                    'calculado_por'       => null,
                    'calculado_en'        => null,
                ]);

                // Precargar las filas vacías para los alumnos activos del nuevo grupo
                $alumnosGrupo = DB::table('asignacion_grupal')
                    ->where('grupo_id', $grupoId)
                    ->where('es_actual', true)
                    ->pluck('alumno_id');

                $calificacionesBatch = [];
                foreach ($alumnosGrupo as $alumnoId) {
                    $calificacionesBatch[] = [
                        'actividad_id'          => $clon->actividad_id,
                        'alumno_id'             => $alumnoId,
                        'calificacion_obtenida' => null,
                        'estado_entrega'        => 'ENTREGADO',
                        'observaciones'         => null,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }

                if (! empty($calificacionesBatch)) {
                    DB::table('calificaciones_actividad')->insert($calificacionesBatch);
                }

                $actividadesCreadas[] = $clon->actividad_id;
            }
        });

        return $actividadesCreadas;
    }


    public function getDatosTabla(int $grupoId, int $materiaId, int $periodoId, ?int $userId = null){
        
       
        $grupo = Grupo::FindOrFail($grupoId);
        $criterios = MateriaCriterio::where('materia_id', $materiaId)->with('catalogoCriterio')->get()
                                            ->filter(function ($mc) {
            $nombre = trim($mc->catalogoCriterio->nombre ?? '');
            // Descarta si el criterio se llama 'Promedio' o 'Faltas'
            return strcasecmp($nombre, 'Promedio') !== 0 && strcasecmp($nombre, 'Faltas') !== 0;
        })
        ->values();

        $actividades = ActividadMateria::where('grupo_id', $grupoId)
                                        ->where('materia_id', $materiaId)
                                        ->where('periodo_id', $periodoId)
                                        ->with(['calificaciones','calculadoPor'])
                                        ->orderBy('fecha_actividad','asc')
                                        ->get();

        $criteriosData = $criterios->map(function ($crit) use ($actividades){
            $actsDelCriterio = $actividades->where('materia_criterio_id', $crit->materia_criterio_id);
            $totalActs = $actsDelCriterio->count();

            $estado = "sin_actividades";
            $calculadoPor = '-';
            $calculadoEn = '-';

            if($totalActs > 0){
                $hayDesincronizadas = $actsDelCriterio->contains(fn($a) => $a->esta_desincronizada);
                $estado = $hayDesincronizadas ? "desincronizado" : "al_dia"; 

                $ultimaActividad = $actsDelCriterio->sortByDesc('calculado_en')->first();
                if($ultimaActividad && $ultimaActividad->calculado_en){
                    $calculadoEn = $ultimaActividad->calculado_en->format('d/M h:i A');
                    $calculadoPor = $ultimaActividad->calculadoPor ? $ultimaActividad->calculadoPor->name : "Sistema";
                }
            }

            return [
                'id'                     => $crit->materia_criterio_id,
                'nombre'                 => $crit->catalogoCriterio->nombre ?? "Sin nombre",
                'peso' => (round(((float)$crit->ponderacion) * 100, 1)) . '%',
                'estado'                 => $estado,
                'actividadesRegistradas' => $totalActs,
                'calculadoEn'            => $calculadoEn,
                'calculadoPor'           => $calculadoPor,
            ];
        });            
        
        $alumnos = $grupo->alumnos()
            ->wherePivot('es_actual', true)
            ->orderBy('alumnos.apellido_paterno')
            ->orderBy('alumnos.apellido_materno')
            ->orderBy('alumnos.nombres')
            ->get();

        $alumnosData = $alumnos->map(function ($alumno) use ($actividades) {
            $calificacionesMap = [];

            foreach ($actividades as $act) {
                $cal = $act->calificaciones->firstWhere('alumno_id', $alumno->alumno_id);
                $calificacionesMap[$act->actividad_id] = [
                    'valor'         => $cal ? ($cal->calificacion_obtenida !== null ? (float)$cal->calificacion_obtenida : null) : null,
                    'estado'        => $cal ? $cal->estado_entrega : 'ENTREGADO',
                    'observaciones' => $cal ? $cal->observaciones : '',
                ];
            }

            return [
                'id'             => $alumno->alumno_id,
                'nombre'         => "{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombres}",
                'calificaciones' => $calificacionesMap,
            ];
        });

        return [
            'criterios'   => $criteriosData,
            'actividades' => $actividades->map(function ($a) {
                return [
                    'id'             => $a->actividad_id,
                    'criterio_id'    => $a->materia_criterio_id,
                    'nombre'         => $a->nombre_actividad,
                    'descripcion'    => $a->descripcion,
                    'valorMaximo'    => (float)$a->valor_maximo,
                    'fecha'          => $a->fecha_actividad->format('d/M'),
                    'fecha_actividad' => $a->fecha_actividad->format('Y-m-d'),
                    'desincronizada' => $a->esta_desincronizada,
                ];
            }),
            'alumnos'     => $alumnosData,
        ];
    }


    public function actualizarActividad(int $actividadId, array $datos): ActividadMateria
{
    $actividad = ActividadMateria::with('periodo')->findOrFail($actividadId);

    if ($actividad->periodo && $actividad->periodo->estado !== 'ABIERTO') {
        throw new Exception('El periodo está cerrado. No se puede editar la actividad.');
    }

    return DB::transaction(function () use ($actividad, $datos) {
        $actividad->update([
            'nombre_actividad'    => $datos['nombre_actividad'],
            'descripcion'         => $datos['descripcion'] ?? null,
            'materia_criterio_id' => $datos['materia_criterio_id'] ?? $actividad->materia_criterio_id,
            'fecha_actividad'     => $datos['fecha_actividad'],
            'valor_maximo'        => $datos['valor_maximo'],
        ]);

        // Tocar updated_at para marcar como desincronizada
        $actividad->touch();

        return $actividad;
    });
}


public function eliminarActividad(int $actividadId, ?int $userId = null): array
{
    $actividad = ActividadMateria::with('periodo')->findOrFail($actividadId);

    if ($actividad->periodo && $actividad->periodo->estado !== 'ABIERTO') {
        throw new Exception('El periodo está cerrado. No se puede eliminar la actividad.');
    }

    $grupoId = $actividad->grupo_id;
    $materiaId = $actividad->materia_id;
    $periodoId = $actividad->periodo_id;
    $materiaCriterioId = $actividad->materia_criterio_id;

    return DB::transaction(function () use ($actividad, $grupoId, $materiaId, $periodoId, $materiaCriterioId, $userId) {
        //  Eliminar los registros de calificaciones de la actividad
        CalificacionActividad::where('actividad_id', $actividad->actividad_id)->delete();

        //  Eliminar la actividad
        $actividad->delete();

        //  Verificar si quedan más actividades en este criterio
        $actividadesRestantes = ActividadMateria::where('grupo_id', $grupoId)
            ->where('materia_id', $materiaId)
            ->where('periodo_id', $periodoId)
            ->where('materia_criterio_id', $materiaCriterioId)
            ->count();

        if ($actividadesRestantes === 0) {
            // Ya no hay actividades: limpiar la tabla tradicional para liberar el input manual
            Calificacion::where('periodo_id', $periodoId)
                ->where('materia_criterio_id', $materiaCriterioId)
                ->whereIn('alumno_id', function ($query) use ($grupoId) {
                    $query->select('alumno_id')
                        ->from('asignacion_grupal')
                        ->where('grupo_id', $grupoId)
                        ->where('es_actual', true);
                })
                ->delete();

            return [
                'quedan_actividades' => false,
                'mensaje' => 'Actividad eliminada. El criterio vuelve a captura manual tradicional.',
            ];
        }

        // Si aún quedan actividades, recalcular el criterio
        $this->calcularCriterio($grupoId, $materiaId, $periodoId, $materiaCriterioId, $userId);

        return [
            'quedan_actividades' => true,
            'mensaje' => 'Actividad eliminada y calificaciones recalculadas.',
        ];
    });
}
public function getResumenEstado(int $periodoId, mixed $nivelId = null, ?int $gradoId = null, ?int $grupoId = null): array
{
      $periodo = Periodo::findOrFail($periodoId);

   
    $esExtracurricular = is_string($nivelId) 
        && in_array(strtolower(trim($nivelId)), ['extracurricular', 'extra']);


    // QUERY BASE
    $query = Grupo::with(['grado.nivel', 'materias.criterios.catalogoCriterio'])
        ->whereHas('alumnos', fn($q) => $q->where('asignacion_grupal.es_actual', true))
        ->whereHas('cicloEscolar', fn($q) => $q->where('estado', 'ACTIVO'));

    
    if ($grupoId) {
        // Filtro específico por grupo (prioridad máxima)
        $query->where('grupo_id', $grupoId);
        
        // Si es modo extracurricular, validar que el grupo sea EXTRA
        if ($esExtracurricular) {
            $query->where(function ($q) {
                $q->whereHas('grado', fn($sub) => $sub->where('tipo_grado', 'EXTRA'))
                  ->orWhere('tipo_grupo', 'EXTRA');
            });
        }
    }
    elseif ($gradoId) {
        // Filtro por grado específico
        $query->where('grado_id', $gradoId);
        
        // Si es modo extracurricular, validar que el grado sea EXTRA
        if ($esExtracurricular) {
            $query->whereHas('grado', fn($q) => $q->where('tipo_grado', 'EXTRA'));
        }
    }
    elseif ($esExtracurricular) {
        $query->where(function ($q) {
            $q->whereHas('grado', fn($sub) => $sub->where('tipo_grado', 'EXTRA'))
              ->orWhere('tipo_grupo', 'EXTRA');
        });
    }
    elseif ($nivelId && is_numeric($nivelId)) {
        // MODO REGULAR: Solo grados del nivel + tipo REGULAR
        $query->whereHas('grado', function ($q) use ($nivelId) {
            $q->where('nivel_id', (int) $nivelId)
              ->where('tipo_grado', 'REGULAR');
        });
        
        // Excluir explícitamente grupos marcados como EXTRA
        $query->where(function ($q) {
            $q->whereNull('tipo_grupo')
              ->orWhere('tipo_grupo', '!=', 'EXTRA');
        });
    }
    // Sin filtros = traer todo (comportamiento por defecto)

    $grupos = $query->get();
    if ($grupos->isEmpty()) {
        return [
            'periodo_id'     => $periodoId,
            'periodo_nombre' => $periodo->nombre,
            'periodo_estado' => $periodo->estado,
            'totales'        => ['al_dia' => 0, 'desincronizado' => 0, 'sin_actividades' => 0],
            'grupos'         => [],
        ];
    }

    //  Precargar maestros asignados a estas materias
    $maestroIds = $grupos->flatMap(fn($g) => $g->materias->pluck('pivot.maestro_id'))->filter()->unique();
    $maestrosMap = \App\Models\User::whereIn('id', $maestroIds)
        ->get(['id', 'name', 'apellido_paterno', 'apellido_materno'])
        ->keyBy('id');

    //  Precargar actividades en memoria para evitar N+1
    $actividadesPeriodo = ActividadMateria::whereIn('grupo_id', $grupos->pluck('grupo_id'))
        ->where('periodo_id', $periodoId)
        ->get();

    $gruposData = [];
    $totales = ['al_dia' => 0, 'desincronizado' => 0, 'sin_actividades' => 0];

    foreach ($grupos as $grupo) {
        $materiasData = [];
        $totalActividades = 0;
        $hayDesincronizadoEnGrupo = false;
        $haySinActividadesEnGrupo = false;

        foreach ($grupo->materias as $materia) {
            $maestroId = $materia->pivot->maestro_id ?? null;
            $maestroNombre = 'Sin asignar';

            if ($maestroId && isset($maestrosMap[$maestroId])) {
                $docente = $maestrosMap[$maestroId];
                $maestroNombre = trim("{$docente->name} {$docente->apellido_paterno} {$docente->apellido_materno}");
            }

            // Filtrar criterios computables (sin Promedio ni Faltas)
            $criteriosValidos = $materia->criterios->filter(function ($mc) {
                $nombre = trim($mc->catalogoCriterio->nombre ?? '');
                return strcasecmp($nombre, 'Promedio') !== 0 && strcasecmp($nombre, 'Faltas') !== 0;
            });

            if ($criteriosValidos->isEmpty()) {
                continue;
            }

            $actsMateria = $actividadesPeriodo->where('grupo_id', $grupo->grupo_id)
                ->where('materia_id', $materia->materia_id);
            
            $totalActsMateria = $actsMateria->count();
            $totalActividades += $totalActsMateria;

            $estadoMateria = 'sin_actividades';
            if ($totalActsMateria > 0) {
                $hayDesincronizadas = $actsMateria->contains(fn($a) => $a->esta_desincronizada);
                $estadoMateria = $hayDesincronizadas ? 'desincronizado' : 'al_dia';
                
                if ($hayDesincronizadas) {
                    $hayDesincronizadoEnGrupo = true;
                }
            } else {
                $haySinActividadesEnGrupo = true;
            }

            $totales[$estadoMateria]++;

            $materiasData[] = [
                'materia_id'     => $materia->materia_id,
                'materia_nombre' => $materia->nombre,
                'maestro_nombre' => $maestroNombre,
                'estado'         => $estadoMateria,
                'actividades'    => $totalActsMateria,
            ];
        }

        if (empty($materiasData)) {
            continue;
        }

        $estadoGrupo = 'al_dia';
        if ($hayDesincronizadoEnGrupo) {
            $estadoGrupo = 'desincronizado';
        } elseif ($haySinActividadesEnGrupo) {
            $estadoGrupo = 'sin_actividades';
        }

        // Detección robusta de si es extracurricular
        $esExtra = ($grupo->grado && $grupo->grado->tipo_grado === 'EXTRA');

        $gruposData[] = [
            'grupo_id'          => $grupo->grupo_id,
            'grupo_nombre'      => $grupo->nombre_grupo,
            'grado_id'          => $grupo->grado_id,
            'grado_nombre'      => $grupo->grado->nombre ?? '',
            'nivel_id'          => $esExtra ? 'extracurricular' : ($grupo->grado->nivel_id ?? ''),
            'nivel_nombre'      => $esExtra ? 'Extracurricular' : ($grupo->grado->nivel->nombre ?? ''),
            'estado'            => $estadoGrupo,
            'total_materias'    => count($materiasData),
            'total_actividades' => $totalActividades,
            'materias'          => $materiasData,
        ];
    }

    // Ordenar grupos: Extracurriculares al final o por nombre (opcional, pero mejora UX)
    usort($gruposData, function ($a, $b) {
        if ($a['nivel_id'] === 'extracurricular' && $b['nivel_id'] !== 'extracurricular') return 1;
        if ($a['nivel_id'] !== 'extracurricular' && $b['nivel_id'] === 'extracurricular') return -1;
        return strcmp($a['grado_nombre'] . $a['grupo_nombre'], $b['grado_nombre'] . $b['grupo_nombre']);
    });

    return [
        'periodo_id'     => $periodoId,
        'periodo_nombre' => $periodo->nombre,
        'periodo_estado' => $periodo->estado,
        'totales'        => $totales,
        'grupos'         => $gruposData,
    ];
}


public function getDetallesPendientes(int $periodoId): array
{
    $actividades = ActividadMateria::where('periodo_id', $periodoId)
        ->whereHas('calificaciones', function ($q) {
            $q->whereNotNull('calificacion_obtenida');
        })
        ->with(['grupo.grado.nivel', 'materia', 'materiaCriterio.catalogoCriterio'])
        ->get()
        ->filter(fn($a) => $a->esta_desincronizada)
        ->values();

    return $actividades->map(function ($act) {
        return [
            'actividad_id' => $act->actividad_id,
            'nombre' => $act->nombre_actividad,
            'grupo_id' => $act->grupo_id,
            'grupo_nombre' => $act->grupo->nombre_grupo ?? '',
            'grado_nombre' => $act->grupo->grado->nombre ?? '',
            'nivel_nombre' => $act->grupo->grado->nivel->nombre ?? '',
            'materia_id' => $act->materia_id,
            'materia_nombre' => $act->materia->nombre ?? '',
            'criterio_nombre' => $act->materiaCriterio->catalogoCriterio->nombre ?? '',
            'updated_at' => $act->updated_at->diffForHumans(),
            'updated_at_raw' => $act->updated_at->format('d/M/Y H:i'),
        ];
    })->values()->toArray();
}

public function getGruposDocente(int $maestroId): array
{
    return Grupo::whereHas('asignacionesMaestros', fn($q) => $q->where('maestro_id', $maestroId))
        ->whereHas('cicloEscolar', fn($q) => $q->where('estado', 'ACTIVO'))
        ->with([
            'grado.nivel',
            'asignacionesMaestros' => function ($q) use ($maestroId) {
                $q->where('maestro_id', $maestroId)->with('materia');
            }
        ])
        ->get()
        ->map(function ($grupo) {
            $esExtra = $grupo->grado && $grupo->grado->tipo_grado === 'EXTRA';
            return [
                'grupo_id'     => $grupo->grupo_id,
                'nombre_grupo' => $grupo->nombre_grupo,
                'grado_nombre' => $grupo->grado->nombre ?? '',
                'nivel_nombre' => $esExtra ? 'Extracurricular' : ($grupo->grado->nivel->nombre ?? ''),
                'es_extra'     => $esExtra,
                'materias'     => $grupo->asignacionesMaestros
                    ->pluck('materia')
                    ->filter()
                    ->unique('materia_id')
                    ->map(fn($m) => [
                        'materia_id' => $m->materia_id,
                        'nombre'     => $m->nombre,
                    ])
                    ->values()
                    ->toArray(),
            ];
        })
        ->toArray();
}

/**
 * Obtiene las tarjetas de métricas por materia para el docente en un grupo y periodo específicos.
 */
public function getMateriasResumenMaestro(int $grupoId, int $periodoId, int $maestroId): array
{
    $grupo = Grupo::with([
        'materias.criterios.catalogoCriterio',
        'asignacionesMaestros' => fn($q) => $q->where('maestro_id', $maestroId)
    ])->findOrFail($grupoId);

    $materiaIds = $grupo->asignacionesMaestros->pluck('materia_id')->unique();
    $materias = $grupo->materias->whereIn('materia_id', $materiaIds);

    $actividades = ActividadMateria::where('grupo_id', $grupoId)
        ->where('periodo_id', $periodoId)
        ->whereIn('materia_id', $materiaIds)
        ->get();

    return $materias->map(function ($materia) use ($actividades) {
        $criteriosValidos = $materia->criterios->filter(function ($mc) {
            $nombre = trim($mc->catalogoCriterio->nombre ?? '');
            return strcasecmp($nombre, 'Promedio') !== 0 && strcasecmp($nombre, 'Faltas') !== 0;
        });

        $actsMateria = $actividades->where('materia_id', $materia->materia_id);
        $totalActs = $actsMateria->count();
        $hayDesincronizadas = $actsMateria->contains(fn($a) => $a->esta_desincronizada);

        $estado = 'sin_actividades';
        if ($totalActs > 0) {
            $estado = $hayDesincronizadas ? 'desincronizado' : 'al_dia';
        }

        $ultimaActividad = $actsMateria->sortByDesc('updated_at')->first();

        return [
            'materia_id'        => $materia->materia_id,
            'nombre'            => $materia->nombre,
            'total_criterios'   => $criteriosValidos->count(),
            'total_actividades' => $totalActs,
            'estado'            => $estado,
            'ultima_actividad'  => $ultimaActividad ? $ultimaActividad->updated_at->format('d/M H:i') : 'Sin registros',
        ];
    })->values()->toArray();
}
}



