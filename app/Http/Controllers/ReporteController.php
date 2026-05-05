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

        // 1. VALIDACIÓN DE PERMISOS
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

        // 2. OBTENER ALUMNOS
        $alumnos = $grupo->alumnosActuales()
                          ->orderBy('apellido_paterno')
                          ->orderBy('apellido_materno')
                          ->orderBy('nombres')
                          ->get();

        // 3. DETECTAR SI ES LA MATERIA SIMULADA (LENGUA EXTRANJERA)
        $esSimulada = str_contains(strtoupper($materia->nombre), 'LENGUA EXTRANJERA') || $materia->materia_id == 999999;

        $nombreMaestro = 'Sin asignar';
        $criteriosOrdenados = collect();
        $calificaciones = collect();

        if ($esSimulada) {
            // --- LÓGICA PARA MATERIA SIMULADA ---
            
            // A. Buscamos las materias de Inglés del grado para usarlas como "Criterios"
            $materiasIngles = DB::table('estructura_curricular as ec')
                ->join('materias as m', 'ec.materia_id', '=', 'm.materia_id')
                ->join('campos_formativos as cf', 'ec.campo_id', '=', 'cf.campo_id')
                ->where('ec.grado_id', $grupo->grado_id)
                ->where('cf.nombre', 'English')
                ->select('m.*')
                ->get();

            foreach ($materiasIngles as $mi) {
                // Buscamos el criterio ID de "Promedio" de esa materia real
                $idCritReal = MateriaCriterio::where('materia_id', $mi->materia_id)
                    ->whereHas('catalogoCriterio', function($q) { $q->where('nombre', 'Promedio'); })
                    ->value('materia_criterio_id');

                if($idCritReal) {
                    $criteriosOrdenados->push([
                        'id' => $idCritReal,
                        'nombre' => mb_strtoupper($mi->nombre),
                        'es_promedio' => false,
                    ]);
                }
            }

            // B. Añadimos el criterio de "Promedio Final" simulado
            $idPseudoPromedio = 888888; 
            $criteriosOrdenados->push([
                'id' => $idPseudoPromedio,
                'nombre' => 'PROMEDIO FINAL',
                'es_promedio' => true,
            ]);

            // C. Inyectamos calificaciones
            foreach ($alumnos as $alumno) {
                $filaAlumno = collect();
                $sumaParaFinal = 0;
                $contParaFinal = 0;

                foreach ($criteriosOrdenados as $crit) {
                    if ($crit['id'] == $idPseudoPromedio) continue;

                    $nota = Calificacion::where('alumno_id', $alumno->alumno_id)
                        ->where('periodo_id', $periodo->periodo_id)
                        ->where('materia_criterio_id', $crit['id'])
                        ->value('calificacion_obtenida');

                    $filaAlumno->put($crit['id'], (object)['calificacion_obtenida' => $nota]);
                    
                    if (is_numeric($nota)) {
                        $sumaParaFinal += $nota;
                        $contParaFinal++;
                    }
                }

                $promedioFinalAlumno = ($contParaFinal > 0) ? ($sumaParaFinal / $contParaFinal) : 0;
                $filaAlumno->put($idPseudoPromedio, (object)['calificacion_obtenida' => $promedioFinalAlumno]);
                
                $calificaciones->put($alumno->alumno_id, $filaAlumno);
            }

        } else {
            // --- LÓGICA PARA MATERIAS NORMALES ---
            
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
            $criteriosOrdenados = $criteriosOrdenados->filter(fn($c) => strcasecmp(trim($c['nombre']), 'FALTAS') != 0)->values();

            $calificaciones = Calificacion::where('periodo_id', $periodo->periodo_id)
                                          ->whereIn('alumno_id', $alumnos->pluck('alumno_id'))
                                          ->whereIn('materia_criterio_id', $criteriosOrdenados->pluck('id'))
                                          ->get()
                                          ->groupBy('alumno_id')
                                          ->map(fn($califs) => $califs->keyBy('materia_criterio_id'));
        }

        // 4. CÁLCULO DE PROMEDIOS GENERALES (Funciona para ambos casos)
        $promedioGrupo = 0;
        $promediosIndividuales = [];
        $promediosPorCriterio = [];
        
        $criterioPromedioId = $criteriosOrdenados->firstWhere('es_promedio', true)['id'] ?? null;

        if ($criterioPromedioId) {
            foreach ($alumnos as $alumno) {
                $calif = $calificaciones->get($alumno->alumno_id)?->get($criterioPromedioId)?->calificacion_obtenida;
                if (is_numeric($calif)) { $promediosIndividuales[] = $calif; }
            }
            if (count($promediosIndividuales) > 0) {
                $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
            }
        }

        foreach ($criteriosOrdenados as $criterio) {
            $calificacionesCriterio = [];
            foreach ($alumnos as $alumno) {
                $calif = $calificaciones->get($alumno->alumno_id)?->get($criterio['id'])?->calificacion_obtenida;
                if (is_numeric($calif)) { $calificacionesCriterio[] = $calif; }
            }
            $promediosPorCriterio[$criterio['id']] = (count($calificacionesCriterio) > 0) 
                ? array_sum($calificacionesCriterio) / count($calificacionesCriterio) 
                : null;
        }

        // 5. GENERAR PDF
        $data = [
            'grupo' => $grupo,
            'periodo' => $periodo,
            'alumnos' => $alumnos,
            'materia' => $materia,
            'nombreMaestro' => $nombreMaestro,
            'criterios' => $criteriosOrdenados,
            'calificaciones' => $calificaciones,
            'promedioGrupo' => $promedioGrupo,
            'promediosPorCriterio' => $promediosPorCriterio,
        ];

        $pdf = Pdf::loadView('reportes.concentrado-periodo', $data, [], [
            'format' => 'A4',
            'orientation' => 'L',
            'mode' => 'utf-8',
        ]);

        return $pdf->stream('concentrado-' . $grupo->nombre_grupo . '-' . $materia->nombre . '.pdf');
    }
}