<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Materia;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\MateriaCriterio;
use App\Models\PonderacionCampo;
use App\Models\CicloEscolar;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class BoletaController extends Controller
{
    private const ORDEN_CAMPOS_PREESCOLAR = [
        'Lenguajes',
        'Saberes y Pensamiento Científico',
        'Ética, Naturaleza y Sociedad',
        'De lo Humano a lo Comunitario',
        'Programa de Lectura',
        'Programa Princeton',
        'Hábitos',
        'English'
    ];

    private const ORDEN_CAMPOS_PRIMARIA = [
        'Lenguajes',
        'Saberes y Pensamiento Científico',
        'Ética, Naturaleza y Sociedad',
        'De lo Humano a lo Comunitario',
        'Programa Académico',
        'Programa Princeton',
        'Hábitos',
        'English',
        'Reading Program'
    ];

    private function getCampoOrderList(string $nivelNombre): ?array
    {
        switch (strtoupper($nivelNombre)) {
            case 'PREESCOLAR':
                return self::ORDEN_CAMPOS_PREESCOLAR;
            case 'PRIMARIA':
                return self::ORDEN_CAMPOS_PRIMARIA;
            default:
                return null;
        }
    }

    public function index()
    {
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        $niveles = Nivel::orderBy('nivel_id')->get(['nivel_id as id', 'nombre']);
        $niveles->push((object)[
            'id' => 'extra',
            'nombre' => 'Extracurricular'
        ]);

        return view('admin.boletas.index', [
            'niveles' => $niveles,
            'cicloActivo' => $cicloActivo
        ]);
    }

    public function getAlumnosPorGrupo(Grupo $grupo)
    {
        $alumnos = $grupo->alumnosActuales()
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get(['alumnos.alumno_id as id', 'nombres', 'apellido_paterno', 'apellido_materno']);

        $alumnos = $alumnos->map(function ($alumno) {
            return [
                'id' => $alumno->id,
                'nombre_completo' => "{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombres}"
            ];
        });

        return response()->json($alumnos);
    }

    public function generarBoletaAlumno(Grupo $grupo, Alumno $alumno)
    {
        $grupo->load('grado.nivel', 'cicloEscolar');
        $grado = $grupo->grado;
        $ciclo = $grupo->cicloEscolar;

        $nivelNombre = $grado->nivel->nombre;
        $orderList = $this->getCampoOrderList($nivelNombre);
        $esPreescolar = (strtoupper($nivelNombre) === 'PREESCOLAR');

        $ponderacionesCampos = PonderacionCampo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->where('grado_id', $grado->grado_id)
            ->pluck('ponderacion', 'campo_formativo_id');

        $estructura = DB::table('estructura_curricular as ec')
            ->join('campos_formativos as cf', 'ec.campo_id', '=', 'cf.campo_id')
            ->join('materias as m', 'ec.materia_id', '=', 'm.materia_id')
            ->where('ec.grado_id', $grado->grado_id)
            ->select(
                'ec.campo_id',
                'cf.nombre as nombre_campo',
                'ec.materia_id',
                'm.nombre as nombre_materia',
                'ec.ponderacion_materia'
            )
            ->orderBy('m.nombre')
            ->get();

        $camposFormativos = $estructura->groupBy('nombre_campo');

        if ($orderList) {
            $camposFormativos = $camposFormativos->sortBy(function ($materias, $nombreCampo) use ($orderList) {
                $position = array_search($nombreCampo, $orderList);
                return ($position === false) ? 99 : $position;
            });
        } else {
            $camposFormativos = $camposFormativos->sortKeys();
        }

        $periodos = Periodo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->orderBy('fecha_inicio')
            ->get();

        $materiaIds = $estructura->pluck('materia_id');

        $criteriosPromedioIds = MateriaCriterio::whereIn('materia_id', $materiaIds)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Promedio');
            })
            ->pluck('materia_criterio_id');

        $calificacionesPAS = Calificacion::where('alumno_id', $alumno->alumno_id)
            ->whereIn('periodo_id', $periodos->pluck('periodo_id'))
            ->whereIn('materia_criterio_id', $criteriosPromedioIds)
            ->get();

        $mapaCalificacionesPAS = [];
        $mapaMateriaCriterio = MateriaCriterio::whereIn('materia_criterio_id', $criteriosPromedioIds)
            ->pluck('materia_id', 'materia_criterio_id');

        foreach ($calificacionesPAS as $cal) {
            $materiaId = $mapaMateriaCriterio->get($cal->materia_criterio_id);
            if ($materiaId) {
                $llave = $materiaId . '_' . $cal->periodo_id;
                $mapaCalificacionesPAS[$llave] = $cal->calificacion_obtenida;
            }
        }

        $boletaData = $this->procesarDatosBoleta(
            $camposFormativos,
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos
        );

        $data = [
            'alumno' => $alumno,
            'grupo' => $grupo,
            'ciclo' => $ciclo,
            'periodos' => $periodos,
            'dataCampos' => $boletaData['campos'],
            'promediosFinales' => $boletaData['promediosFinales'],
            'esPreescolar' => $esPreescolar
        ];

        $pdf = PDF::loadView('reportes.boleta-alumno', $data, [], [
            'format' => 'Letter',
            'orientation' => 'P'
        ]);

        return $pdf->stream('boleta-' . $alumno->apellido_paterno . '-' . $alumno->nombres . '.pdf');
    }

    private function procesarDatosBoleta($camposFormativos, $periodos, $mapaCalificacionesPAS, $ponderacionesCampos)
    {
        $dataCampos = [];
        $promediosFinales = [];

        foreach ($periodos as $periodo) {
            $promediosFinales[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
        }

        foreach ($camposFormativos as $nombreCampo => $materias) {
            $campoId = $materias->first()->campo_id;
            $ponderacionCampo = $ponderacionesCampos->get($campoId, 0) / 100.0;
            $dataMaterias = [];
            $promediosSEP_Campo = [];

            foreach ($periodos as $periodo) {
                $promediosSEP_Campo[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
            }

            $promediosSEP_Campo['promedio_pas'] = ['suma' => 0, 'contador' => 0];
            $promediosSEP_Campo['promedio_sep'] = ['suma' => 0, 'contador' => 0];

            foreach ($materias as $materia) {
                $califsMateria_PAS = [];
                $sumaMateriaPAS = 0;
                $countMateriaPAS = 0;
                $ponderacionMateria = $materia->ponderacion_materia / 100.0;

                foreach ($periodos as $periodo) {
                    $llave = $materia->materia_id . '_' . $periodo->periodo_id;
                    $notaPAS = $mapaCalificacionesPAS[$llave] ?? null;
                    $califsMateria_PAS[$periodo->periodo_id] = $notaPAS;

                    if (is_numeric($notaPAS)) {
                        $sumaMateriaPAS += $notaPAS;
                        $countMateriaPAS++;
                        $promediosSEP_Campo[$periodo->periodo_id]['suma_ponderada'] += ($notaPAS * $ponderacionMateria);
                        $promediosSEP_Campo[$periodo->periodo_id]['total_ponderacion'] += $ponderacionMateria;
                    }
                }

                $promedioPAS_Materia = ($countMateriaPAS > 0) ? round($sumaMateriaPAS / $countMateriaPAS, 2) : null;

                if (is_numeric($promedioPAS_Materia)) {
                    $promediosSEP_Campo['promedio_pas']['suma'] += $promedioPAS_Materia;
                    $promediosSEP_Campo['promedio_pas']['contador']++;
                }

                $dataMaterias[] = [
                    'nombre' => $materia->nombre_materia,
                    'calificaciones_pas' => $califsMateria_PAS,
                    'promedio_pas' => $promedioPAS_Materia
                ];
            }

            $califsMateria_SEP = [];
            foreach ($periodos as $periodo) {
                $totalPond = $promediosSEP_Campo[$periodo->periodo_id]['total_ponderacion'];
                $sumaPond = $promediosSEP_Campo[$periodo->periodo_id]['suma_ponderada'];
                $promedioSEP = ($totalPond > 0) ? round($sumaPond / $totalPond, 2) : null;
                $califsMateria_SEP[$periodo->periodo_id] = $promedioSEP;

                if (is_numeric($promedioSEP)) {
                    $promediosSEP_Campo['promedio_sep']['suma'] += $promedioSEP;
                    $promediosSEP_Campo['promedio_sep']['contador']++;
                    $promediosFinales[$periodo->periodo_id]['suma_ponderada'] += ($promedioSEP * $ponderacionCampo);
                    $promediosFinales[$periodo->periodo_id]['total_ponderacion'] += $ponderacionCampo;
                }
            }

            $promedioSEP_Materia = ($promediosSEP_Campo['promedio_sep']['contador'] > 0)
                ? round($promediosSEP_Campo['promedio_sep']['suma'] / $promediosSEP_Campo['promedio_sep']['contador'], 2)
                : null;

            $dataCampos[] = [
                'nombre' => $nombreCampo,
                'materias' => $dataMaterias,
                'calificaciones_sep' => $califsMateria_SEP,
                'promedio_final_pas' => ($promediosSEP_Campo['promedio_pas']['contador'] > 0)
                    ? round($promediosSEP_Campo['promedio_pas']['suma'] / $promediosSEP_Campo['promedio_pas']['contador'], 2)
                    : null,
                'promedio_final_sep' => $promedioSEP_Materia
            ];
        }

        $promediosFinalesCalculados = [];
        $sumaPromedioFinal = 0;
        $contadorPromedioFinal = 0;

        foreach ($periodos as $periodo) {
            $totalPond = $promediosFinales[$periodo->periodo_id]['total_ponderacion'];
            $sumaPond = $promediosFinales[$periodo->periodo_id]['suma_ponderada'];

            $promedioFinalPond = ($totalPond > 0) ? round($sumaPond / $totalPond, 2) : null;
            $promediosFinalesCalculados[$periodo->periodo_id] = $promedioFinalPond;

            if (is_numeric($promedioFinalPond)) {
                $sumaPromedioFinal += $promedioFinalPond;
                $contadorPromedioFinal++;
            }
        }

        $promediosFinalesCalculados['promedio_final_sep'] = ($contadorPromedioFinal > 0)
            ? round($sumaPromedioFinal / $contadorPromedioFinal, 2)
            : null;

        return [
            'campos' => $dataCampos,
            'promediosFinales' => $promediosFinalesCalculados
        ];
    }
}