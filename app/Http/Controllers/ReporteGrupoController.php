<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\MateriaCriterio;
use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReporteGrupoController extends Controller
{

public function index()
{
    // 1. Buscamos el ciclo escolar activo
    $cicloActivo = \App\Models\CicloEscolar::where('estado', 'ACTIVO')->first();

    // 2. Cargamos niveles con sus grados filtrados (Solo REGULAR) y sus grupos del ciclo activo
    $niveles = \App\Models\Nivel::with(['grados' => function ($query) {
        $query->where('tipo_grado', 'REGULAR') // <-- Filtro clave
              ->orderBy('orden');
    }, 'grados.grupos' => function ($query) use ($cicloActivo) {
        $query->where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
              ->where('estado', 'ACTIVO');
    }])->get();

    return view('admin.reportes.index-seleccion', compact('niveles'));
}

  private const CAMPOS_FORMATIVOS_SEP = [
        'Lenguajes',
        'Saberes y Pensamiento Científico',
        'Ética, Naturaleza y Sociedad',
        'De lo Humano a lo Comunitario',
    ];

    public function resumen(Request $request, Grupo $grupo)
    {
        $periodos = Periodo::where('ciclo_escolar_id', $grupo->ciclo_escolar_id)
            ->orderBy('fecha_inicio')->get();

        $periodoId = $request->input('periodo_id', $periodos->first()->periodo_id ?? null);

        $data = $this->prepararDatosResumen($grupo, $periodoId);
        return view('admin.reportes.resumen-grupal', array_merge($data, [
            'periodos' => $periodos,
            'periodo_id' => $periodoId
        ]));
    }

    private function prepararDatosResumen(Grupo $grupo, $periodoId)
    {
        $grupo->load('grado.nivel', 'cicloEscolar');

        // 1. Obtener estructura curricular (materias normales)[cite: 3]
        $estructura = DB::table('estructura_curricular as ec')
            ->join('campos_formativos as cf', 'ec.campo_id', '=', 'cf.campo_id')
            ->where('ec.grado_id', $grupo->grado_id)
            ->select('ec.materia_id', 'cf.nombre as nombre_campo')
            ->get()
            ->groupBy('nombre_campo');

        // 2. Obtener materias del bloque "English" para inyectar en Lenguajes
        $materiasInglesIds = DB::table('estructura_curricular as ec')
            ->join('campos_formativos as cf', 'ec.campo_id', '=', 'cf.campo_id')
            ->where('ec.grado_id', $grupo->grado_id)
            ->where('cf.nombre', 'English')
            ->pluck('materia_id');

        $alumnos = $grupo->alumnosActuales()->orderBy('apellido_paterno')->get();

        // 3. Pre-cargar TODAS las calificaciones del periodo para este grupo para evitar el 0
        // Buscamos criterios que se llamen exactamente "Promedio" (sensible a mayúsculas)
        $calificacionesMapa = Calificacion::where('periodo_id', $periodoId)
            ->whereIn('alumno_id', $alumnos->pluck('alumno_id'))
            ->whereHas('materiaCriterio.catalogoCriterio', function($q) {
                $q->where('nombre', 'Promedio');
            })
            ->get()
            ->groupBy('alumno_id');

        $resumenAlumnos = $alumnos->map(function ($alumno) use ($estructura, $periodoId, $materiasInglesIds, $calificacionesMapa) {
            $promediosCampos = [];
            $sumaGeneral = 0;
            $camposConNota = 0;
            
            // Calificaciones del alumno actual (usando alumno_id)[cite: 1]
            $misCalificaciones = $calificacionesMapa->get($alumno->alumno_id) ?? collect();

          foreach (self::CAMPOS_FORMATIVOS_SEP as $campo) {
                $materiasIds = $estructura->get($campo)?->pluck('materia_id') ?? collect();
                
                $sumaNotasCampo = 0;
                $contadorNotasCampo = 0;

                foreach ($materiasIds as $mId) {
                    $califObj = $misCalificaciones->first(function($c) use ($mId) {
                        return $c->materiaCriterio->materia_id == $mId;
                    });
                    
                    if ($califObj) {
                        $sumaNotasCampo += $califObj->calificacion_obtenida;
                        $contadorNotasCampo++;
                    }
                }

                if ($campo === 'Lenguajes' && $materiasInglesIds->isNotEmpty()) {
                    $califsIngles = $misCalificaciones->filter(function($c) use ($materiasInglesIds) {
                        return in_array($c->materiaCriterio->materia_id, $materiasInglesIds->toArray());
                    });

                    if ($califsIngles->isNotEmpty()) {
                        $sumaNotasCampo += $califsIngles->avg('calificacion_obtenida');
                        $contadorNotasCampo++;
                    }
                }

                $promedioCampo = $contadorNotasCampo > 0 ? ($sumaNotasCampo / $contadorNotasCampo) : 0;
                $notaTruncada = floor($promedioCampo * 1000) / 1000;

                $promediosCampos[$campo] = [
                    'valor' => $notaTruncada > 0 ? number_format($notaTruncada, 3) : '0.000',
                    'num' => $notaTruncada
                ];

                // Sumamos la nota truncada del campo a la suma general
                $sumaGeneral += $notaTruncada;
            }

            // === CAMBIO CRÍTICO AQUÍ ===
            // Dividimos siempre entre 4 (total de campos SEP) para el promedio real
            $totalCamposOficiales = count(self::CAMPOS_FORMATIVOS_SEP); 
            $promFinal = $sumaGeneral / $totalCamposOficiales;
            
            $promFinalTruncado = floor($promFinal * 1000) / 1000;

            return [
                'nombre' => "{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombres}",
                'promedio_final_num' => $promFinalTruncado,
                'promedio_final' => number_format($promFinalTruncado, 3),
                'campos' => $promediosCampos
            ];
        });

        return [
            'grupo' => $grupo,
            'camposSep' => self::CAMPOS_FORMATIVOS_SEP,
            'alumnos' => $resumenAlumnos->sortByDesc('promedio_final_num')->values()
        ];
    }
    // Busca la nota de una materia individual (Criterio "Promedio")[cite: 1]
    private function getNotaMateria($alumnoId, $materiaId, $periodoId) {
        $criterioId = MateriaCriterio::where('materia_id', $materiaId)
            ->whereHas('catalogoCriterio', function ($q) { $q->where('nombre', 'Promedio'); })
            ->value('materia_criterio_id');

        return Calificacion::where('alumno_id', $alumnoId)
            ->where('periodo_id', $periodoId)
            ->where('materia_criterio_id', $criterioId)
            ->value('calificacion_obtenida') ?? 0;
    }

    // Calcula el promedio de un bloque de materias (para Inglés/Lengua Extranjera)[cite: 1]
    private function calcularPromedioBloque($alumnoId, $materiasIds, $periodoId) {
        $criteriosIds = MateriaCriterio::whereIn('materia_id', $materiasIds)
            ->whereHas('catalogoCriterio', function ($q) { $q->where('nombre', 'Promedio'); })
            ->pluck('materia_criterio_id');

        return Calificacion::where('alumno_id', $alumnoId)
            ->where('periodo_id', $periodoId)
            ->whereIn('materia_criterio_id', $criteriosIds)
            ->avg('calificacion_obtenida') ?? 0;
    }

    public function descargarPdf(Request $request, Grupo $grupo) {
        $periodoId = $request->input('periodo_id');
        $data = $this->prepararDatosResumen($grupo, $periodoId);
        $data['periodoNombre'] = Periodo::find($periodoId)->nombre ?? '';

        $pdf = PDF::loadView('admin.reportes.pdf-resumen-grupal', $data, [], [
            'format' => 'A4', 'orientation' => 'L', 'mode' => 'utf-8',
        ]);
        return $pdf->stream('Promedios-' . $grupo->nombre_grupo . '.pdf');
    }
}