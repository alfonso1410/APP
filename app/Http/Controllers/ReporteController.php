<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\User;
use App\Models\Materia;
use App\Models\MateriaCriterio;
use App\Models\Calificacion;
use PDF;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    public function generarConcentradoPeriodo(Grupo $grupo, Periodo $periodo, Materia $materia)
    {
        $user = Auth::user();

        if ($user->rol === 'MAESTRO') {
            $esAsignado = DB::table('grupo_materia_maestro')
                ->where('maestro_id', $user->id)
                ->where('grupo_id', $grupo->grupo_id)
                ->where('materia_id', $materia->materia_id)
                ->exists();
            
            if (!$esAsignado) {
                abort(403, 'Usted no tiene permiso para generar este reporte.');
            }
        }

        $grupo->load('grado');
        $periodo->load('cicloEscolar');

        $alumnos = $grupo->alumnosActuales()
                          ->orderBy('apellido_paterno')
                          ->orderBy('apellido_materno')
                          ->orderBy('nombres')
                          ->get();

        $nombreMaestro = 'Sin asignar';

        $asignacion = $grupo->materias()
                            ->where('materias.materia_id', $materia->materia_id)
                            ->first();

        if ($asignacion && $asignacion->pivot->maestro_id) {
            $maestro = User::find($asignacion->pivot->maestro_id);
            if ($maestro) {
                $nombreMaestro = trim($maestro->name . ' ' . $maestro->apellido_paterno . ' ' . $maestro->apellido_materno);
            }
        }

        $materiaCriterios = MateriaCriterio::where('materia_id', $materia->materia_id)
                                          ->with('catalogoCriterio')
                                          ->orderBy('materia_criterio_id')
                                          ->get();
        
        $criterios = $materiaCriterios->map(function ($mc) {
            $nombre = $mc->catalogoCriterio->nombre ?? 'Criterio s/n';
            return [
                'id' => $mc->materia_criterio_id,
                'nombre' => $nombre,
                'es_promedio' => (strcasecmp($nombre, 'Promedio') == 0),
            ];
        });

        list($promedios, $otrosCriterios) = $criterios->partition(fn($c) => $c['es_promedio']);
        $criteriosOrdenados = $otrosCriterios->merge($promedios)->values();

        $criteriosOrdenados = $criteriosOrdenados->filter(function ($criterio) {
            return strcasecmp(trim($criterio['nombre']), 'FALTAS') != 0;
        })->values();

        $calificaciones = Calificacion::where('periodo_id', $periodo->periodo_id)
                                      ->whereIn('alumno_id', $alumnos->pluck('alumno_id'))
                                      ->whereIn('materia_criterio_id', $criteriosOrdenados->pluck('id'))
                                      ->get()
                                      ->groupBy('alumno_id')
                                      ->map(fn($califs) => $califs->keyBy('materia_criterio_id'));

        // Calcular el promedio del grupo
        $promedioGrupo = 0;
        $promediosIndividuales = [];

        $criterioPromedioId = $criteriosOrdenados->firstWhere('es_promedio', true)['id'] ?? null;

        if ($criterioPromedioId) {
            foreach ($alumnos as $alumno) {
                $calif = $calificaciones->get($alumno->alumno_id)
                                        ?->get($criterioPromedioId)
                                        ?->calificacion_obtenida;
                
                if (is_numeric($calif)) {
                    $promediosIndividuales[] = $calif;
                }
            }
            
            if (count($promediosIndividuales) > 0) {
                $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
            }
        }

        // NUEVO: Calcular promedios por criterio
        $promediosPorCriterio = [];
        
        foreach ($criteriosOrdenados as $criterio) {
            $calificacionesCriterio = [];
            
            foreach ($alumnos as $alumno) {
                $calif = $calificaciones->get($alumno->alumno_id)
                                        ?->get($criterio['id'])
                                        ?->calificacion_obtenida;
                
                if (is_numeric($calif)) {
                    $calificacionesCriterio[] = $calif;
                }
            }
            
            if (count($calificacionesCriterio) > 0) {
                $promediosPorCriterio[$criterio['id']] = array_sum($calificacionesCriterio) / count($calificacionesCriterio);
            } else {
                $promediosPorCriterio[$criterio['id']] = null;
            }
        }

        $data = [
            'grupo' => $grupo,
            'periodo' => $periodo,
            'alumnos' => $alumnos,
            'materia' => $materia,
            'nombreMaestro' => $nombreMaestro,
            'criterios' => $criteriosOrdenados,
            'calificaciones' => $calificaciones,
            'promedioGrupo' => $promedioGrupo,
            'promediosPorCriterio' => $promediosPorCriterio, // NUEVO
        ];

        $pdf = Pdf::loadView('reportes.concentrado-periodo', $data, [], [
            'format' => 'A4',
            'orientation' => 'L',
            'mode' => 'utf-8',
        ]);

        return $pdf->stream('concentrado-' . $grupo->nombre_grupo . '-' . $materia->nombre . '.pdf');
    }
}