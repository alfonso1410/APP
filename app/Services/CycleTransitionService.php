<?php

namespace App\Services;

use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Alumno;
use App\Models\Grado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;
use Illuminate\Support\Carbon;

class CycleTransitionService
{
   public function executeTransition(int $oldCycleId, int $newCycleId, array $mappings)
{
    return DB::transaction(function () use ($oldCycleId, $newCycleId, $mappings) {
        
        // 1. VALIDACIONES CRÍTICAS
        if ($oldCycleId === $newCycleId) {
            throw new Exception("El ciclo origen y el ciclo destino no pueden ser el mismo.");
        }

        $oldCycle = CicloEscolar::findOrFail($oldCycleId);
        $newCycle = CicloEscolar::findOrFail($newCycleId);
        
        if ($oldCycle->estado !== 'ACTIVO') {
            throw new Exception("Solo puedes transicionar desde el ciclo que está actualmente ACTIVO.");
        }

        if ($newCycle->fecha_inicio <= $oldCycle->fecha_inicio) {
            throw new Exception("El ciclo destino debe ser posterior al ciclo origen.");
        }

        // 2. CAMBIO DE ESTADOS DE CICLOS
        $oldCycle->update(['estado' => 'CERRADO']);
        $newCycle->update(['estado' => 'ACTIVO']);

        // 3. PROCESAR MAPEOS (antes de desactivar nada)
        foreach ($mappings as $mapping) {
            $oldGroupId = $mapping['old_group_id'];
            
            $oldGroup = Grupo::with(['grado.nivel', 'alumnos' => function($q) {
                $q->wherePivot('es_actual', true)
                  ->where('estado_alumno', 'ACTIVO');
            }])
            ->where('ciclo_escolar_id', $oldCycleId)
            ->findOrFail($oldGroupId);

            $currentLevelName = strtoupper($oldGroup->grado->nivel->nombre ?? '');
            $currentOrder = $oldGroup->grado->orden;
            $isPrimary6th = ($currentLevelName === 'PRIMARIA' && $currentOrder == 6);

            // CASO A: 6TO DE PRIMARIA
            if ($isPrimary6th) {
                $graduatingStudentIds = $mapping['graduating_student_ids'] ?? [];

                if (!empty($graduatingStudentIds)) {
                    $validStudentIds = $oldGroup->alumnos->pluck('alumno_id')->toArray();
                    $invalidStudentIds = array_diff($graduatingStudentIds, $validStudentIds);

                    if (!empty($invalidStudentIds)) {
                        throw new Exception("Se intentó procesar alumnos para egreso que no pertenecen al grupo '{$oldGroup->nombre_grupo}'.");
                    }

                    Alumno::whereIn('alumno_id', $graduatingStudentIds)
                        ->update(['estado_alumno' => 'EGRESADO']);
                }
                
                continue;
            }

            // CASO B: GRUPOS REGULARES
            $studentsPayload = $mapping['students'] ?? [];
            if (empty($studentsPayload)) {
                continue;
            }

            $validStudentIds = $oldGroup->alumnos->pluck('alumno_id')->toArray();
            $requestStudentIds = collect($studentsPayload)->pluck('alumno_id')->toArray();
            $invalidStudentIds = array_diff($requestStudentIds, $validStudentIds);

            if (!empty($invalidStudentIds)) {
                throw new Exception("Se intentó procesar alumnos que no pertenecen al grupo de origen '{$oldGroup->nombre_grupo}'.");
            }

            $insertsByGroup = [];

            foreach ($studentsPayload as $item) {
                $alumnoId = $item['alumno_id'];
                $targetGroupId = $item['new_group_id'] ?? null;

                if ($targetGroupId) {
                    $insertsByGroup[$targetGroupId][] = [
                        'alumno_id'  => $alumnoId,
                        'grupo_id'   => $targetGroupId,
                        'es_actual'  => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            foreach ($insertsByGroup as $targetGroupId => $inserts) {
                $newGroup = Grupo::where('grupo_id', $targetGroupId)
                                 ->where('ciclo_escolar_id', $newCycleId)
                                 ->first();
                                 
                if (!$newGroup) {
                    throw new Exception("Uno de los grupos destino seleccionados no pertenece al nuevo ciclo escolar.");
                }

                DB::table('asignacion_grupal')->upsert(
                    $inserts,
                    ['alumno_id', 'grupo_id'],
                    ['es_actual', 'updated_at']
                );
            }
        }

        // 4. AHORA SI: Desactivar asignaciones del ciclo viejo
        $oldGroupIds = Grupo::where('ciclo_escolar_id', $oldCycleId)->pluck('grupo_id');

        DB::table('asignacion_grupal')
            ->whereIn('grupo_id', $oldGroupIds)
            ->update(['es_actual' => false, 'updated_at' => now()]);

        // 5. Cerrar todos los grupos del ciclo viejo (regulares y extras)
        Grupo::where('ciclo_escolar_id', $oldCycleId)
             ->update(['estado' => 'CERRADO']);

        return CicloEscolar::findOrFail($newCycleId);
    });
}

    public function getPromotionMatrix(int $oldCycleId, int $newCycleId)
    {
        $cycle = CicloEscolar::with([
            'grupos' => function($q) {
                $q->whereHas('grado', function($gradoQuery) {
                    $gradoQuery->where('tipo_grado', 'REGULAR');
                })
                ->withCount(['alumnos' => function($subQ) {
                    $subQ->where('asignacion_grupal.es_actual', true)
                         ->where('estado_alumno', 'ACTIVO');
                }])
                ->with([
                    'grado.nivel' => function($gQ) {
                        $gQ->select('nivel_id', 'nombre');
                    },
                    'alumnos' => function($studentQuery) {
                        $studentQuery->wherePivot('es_actual', true)
                                     ->where('estado_alumno', 'ACTIVO');
                    }
                ]);
            }
        ])->findOrFail($oldCycleId);

        $newCycleGroups = Grupo::with('grado:grado_id,nombre')
                               ->where('ciclo_escolar_id', $newCycleId)
                               ->get();

        $allGrades = Grado::with('nivel')->get();

        return $cycle->grupos->map(function($grupo) use ($newCycleGroups, $allGrades) {
            $currentOrder = $grupo->grado->orden;
            $currentLevelName = strtoupper($grupo->grado->nivel->nombre ?? '');
            
            $nextGrade = null;
            $isGraduating = false;
            $isPreschool3 = ($currentLevelName === 'PREESCOLAR' && $currentOrder == 3);

            if ($isPreschool3) {
                $nextGrade = $allGrades->first(function($g) {
                    return strtoupper($g->nivel->nombre ?? '') === 'PRIMARIA' && $g->orden == 1;
                });
            } elseif ($currentLevelName === 'PRIMARIA' && $currentOrder == 6) {
                $isGraduating = true; 
            } else {
                $nextGrade = $allGrades->first(function($g) use ($grupo, $currentOrder) {
                    return $g->nivel_id === $grupo->grado->nivel_id && $g->orden == ($currentOrder + 1);
                });
            }

            $targetGroups = collect();
            if ($nextGrade && !$isGraduating) {
                $targetGroups = $newCycleGroups->where('grado_id', $nextGrade->grado_id)
                                             ->values()
                                             ->map(function($g) {
                    return [
                        'grupo_id' => $g->grupo_id,
                        'nombre_grupo' => $g->nombre_grupo,
                        'grado_nombre' => $g->grado->nombre ?? '',
                    ];
                });
            }

            return [
                'old_group_id' => $grupo->grupo_id,
                'group_name' => $grupo->nombre_grupo . ' (' . $grupo->grado->nombre . ')',
                'current_grade_name' => $grupo->grado->nombre,
                'is_preschool_3' => $isPreschool3, 
                'student_count' => $grupo->alumnos_count,
                
                'students' => $grupo->alumnos->map(function($alumno) {
                    return [
                        'alumno_id' => $alumno->alumno_id,
                        'nombre' => trim($alumno->nombres . ' ' . $alumno->apellido_paterno . ' ' . $alumno->apellido_materno),
                    ];
                })->values(),

                'target_groups' => $targetGroups,
                'is_graduating' => $isGraduating,
            ];
        });
    }
}