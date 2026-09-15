<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Calificacion;
use App\Models\MateriaCriterio;
use App\Models\PonderacionCampo;
use Illuminate\Support\Collection;

class CalificacionService {
public const CAMPOS_FORMATIVOS_SEP = [
        'Lenguajes',
        'Saberes y Pensamiento Científico',
        'Ética, Naturaleza y Sociedad',
        'De lo Humano a lo Comunitario',
    ];

    /**
     * Convierte una calificación numérica a la escala cualitativa de Preescolar.
     */
   public function getLetraCalificacion($valor)
    {
        if (!is_numeric($valor)) return '';
        
        // NUEVO CAMBIO: .5 baja al entero actual, .6 sube al siguiente entero.
        // PHP_ROUND_HALF_DOWN hace exactamente esto: 9.5 -> 9, pero 9.6 -> 10.
        $val = round($valor, 0, PHP_ROUND_HALF_DOWN);
        
        if ($val == 10) return 'E';
        if ($val == 9)  return 'MB';
        if ($val == 8)  return 'B';
        if ($val >= 6 && $val <= 7) return 'R';
        if ($val < 6)   return 'NA'; 
        return 'NP'; 
    }
    /**
     * Motor principal de cálculo de campos formativos SEP (Soporta letras o formato numérico).
     */
    public function procesarCamposSEP(
        Collection $camposFormativos, 
        Collection $periodos, 
        array $mapaCalificacionesPAS, 
        Collection $ponderacionesCampos, 
        bool $esPreescolar = false
    ): array {
        $dataCampos = [];
        $promediosFinales = [];
        $promediosFinalesCalculados = [];
        $califsPorMateriaNumerica = []; 

        foreach ($periodos as $periodo) {
            $promediosFinales[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
            $promediosFinalesCalculados[$periodo->periodo_id] = null; 
        }

        foreach ($camposFormativos as $nombreCampo => $materias) {
            if ($materias->isEmpty()) continue;

            $campoId = $materias->first()->campo_id;
            $ponderacionCampo = ($ponderacionesCampos->get($campoId, 0) ?? 0) / 100.0;
            $dataMaterias = [];
            $promediosSEP_Campo = [];

            foreach ($periodos as $periodo) {
                $promediosSEP_Campo[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
            }
            $promediosSEP_Campo['promedio_pas'] = ['suma' => 0, 'contador' => 0];
            $promediosSEP_Campo['promedio_sep'] = ['suma' => 0, 'contador' => 0];

            foreach ($materias as $materia) {
                $califsMateria_PAS = []; 
                $califsMateria_PAS_Numerica = []; 
                $sumaMateriaPAS = 0; $countMateriaPAS = 0;
                $ponderacionMateria = ($materia->ponderacion_materia ?? 0) / 100.0;

                foreach ($periodos as $periodo) {
                    $llave = $materia->materia_id . '_' . $periodo->periodo_id;
                    $notaPAS = $mapaCalificacionesPAS[$llave] ?? null;
                    
                    if ($esPreescolar) {
                        $califsMateria_PAS[$periodo->periodo_id] = $this->getLetraCalificacion($notaPAS);
                        $califsMateria_PAS_Numerica[$periodo->periodo_id] = is_numeric($notaPAS) ? $notaPAS : null; 
                    } else {
                        $notaRedondeada = is_numeric($notaPAS) ? round($notaPAS, 1) : null;
                        $califsMateria_PAS[$periodo->periodo_id] = is_numeric($notaRedondeada) ? number_format($notaRedondeada, 1) : null; 
                        $califsMateria_PAS_Numerica[$periodo->periodo_id] = $notaRedondeada; 
                    }

                    if (is_numeric($notaPAS)) {
                        $sumaMateriaPAS += $notaPAS;
                        $countMateriaPAS++;
                        
                        if (!$esPreescolar) {
                            $promediosSEP_Campo[$periodo->periodo_id]['suma_ponderada'] += ($notaPAS * $ponderacionMateria);
                            $promediosSEP_Campo[$periodo->periodo_id]['total_ponderacion'] += $ponderacionMateria;
                        }
                    }
                }

                $promedioPAS_MateriaNum = ($countMateriaPAS > 0) ? round($sumaMateriaPAS / $countMateriaPAS, 1) : null;
                $promedioPAS_Mostrado = $esPreescolar 
                    ? $this->getLetraCalificacion($promedioPAS_MateriaNum)
                    : (is_numeric($promedioPAS_MateriaNum) ? number_format($promedioPAS_MateriaNum, 1) : null);

                if (is_numeric($promedioPAS_MateriaNum) && !$esPreescolar) {
                    $promediosSEP_Campo['promedio_pas']['suma'] += $promedioPAS_MateriaNum;
                    $promediosSEP_Campo['promedio_pas']['contador']++;
                }

                $dataMaterias[] = [
                    'nombre' => $materia->nombre_materia,
                    'calificaciones_pas' => $califsMateria_PAS,
                    'calificaciones_pas_numerica' => $califsMateria_PAS_Numerica,
                    'promedio_pas' => $promedioPAS_Mostrado
                ];

                $califsPorMateriaNumerica[$materia->materia_id] = $califsMateria_PAS_Numerica; 
            }

            $califsMateria_SEP = []; 
            if (!$esPreescolar) {
                foreach ($periodos as $periodo) {
                    $totalPond = $promediosSEP_Campo[$periodo->periodo_id]['total_ponderacion'];
                    $sumaPond = $promediosSEP_Campo[$periodo->periodo_id]['suma_ponderada'];

                    $promedioSEP = ($totalPond > 0) ? round($sumaPond / $totalPond, 1) : null;
                    $califsMateria_SEP[$periodo->periodo_id] = is_numeric($promedioSEP) ? number_format($promedioSEP, 1) : null; 

                    if (is_numeric($promedioSEP)) {
                        $promediosSEP_Campo['promedio_sep']['suma'] += $promedioSEP;
                        $promediosSEP_Campo['promedio_sep']['contador']++;
                        $promedioSinRedondear = ($totalPond > 0) ? $sumaPond / $totalPond : null;
                        if (is_numeric($promedioSinRedondear)) {
                             $promediosFinales[$periodo->periodo_id]['suma_ponderada'] += ($promedioSinRedondear * $ponderacionCampo);
                             $promediosFinales[$periodo->periodo_id]['total_ponderacion'] += $ponderacionCampo;
                        }
                    }
                }
            }
            
            $promedioSEP_Materia = null;
            if (!$esPreescolar && $promediosSEP_Campo['promedio_sep']['contador'] > 0) {
                $promedioSEP_Materia = round($promediosSEP_Campo['promedio_sep']['suma'] / $promediosSEP_Campo['promedio_sep']['contador'], 1);
            }

            $promedioFinalPAS = null;
            if (!$esPreescolar && $promediosSEP_Campo['promedio_pas']['contador'] > 0) {
                $promedioFinalPAS = round($promediosSEP_Campo['promedio_pas']['suma'] / $promediosSEP_Campo['promedio_pas']['contador'], 1);
            }

            $dataCampos[] = [
                'campo_id' => $campoId,
                'nombre' => $nombreCampo,
                'materias' => $dataMaterias,
                'calificaciones_sep' => $califsMateria_SEP,
                'promedio_final_pas' => is_numeric($promedioFinalPAS) ? number_format($promedioFinalPAS, 1) : null,
                'promedio_final_sep' => is_numeric($promedioSEP_Materia) ? number_format($promedioSEP_Materia, 1) : null
            ];

            $califsPorMateriaNumerica[$materia->materia_id] = $califsMateria_PAS_Numerica; 
        }

        if (!$esPreescolar) {
            $sumaPromedioFinal = 0;
            $contadorPromedioFinal = 0;

            foreach ($periodos as $periodo) {
                $totalPond = $promediosFinales[$periodo->periodo_id]['total_ponderacion'];
                $sumaPond = $promediosFinales[$periodo->periodo_id]['suma_ponderada'];

                $promedioFinalPond = ($totalPond > 0) ? round($sumaPond / $totalPond, 1) : null;
                $promediosFinalesCalculados[$periodo->periodo_id] = is_numeric($promedioFinalPond) ? number_format($promedioFinalPond, 1) : null; 

                if (is_numeric($promedioFinalPond)) {
                    $sumaPromedioFinal += $promedioFinalPond;
                    $contadorPromedioFinal++;
                }
            }

            $valFinal = ($contadorPromedioFinal > 0)
                ? round($sumaPromedioFinal / $contadorPromedioFinal, 1)
                : null;
            $promediosFinalesCalculados['promedio_final_sep'] = is_numeric($valFinal) ? number_format($valFinal, 1) : null;
        }

        return [
            'campos' => $dataCampos,
            'promediosFinales' => $promediosFinalesCalculados,
            'califs_por_materia_numerica' => $califsPorMateriaNumerica 
        ];
    }

    /**
     * Método de alto nivel pensado para el nuevo módulo de seguimiento por trimestre.
     * Retorna las notas numéricas puras de los campos formativos SEP para un alumno.
     */
    public function obtenerResumenCamposNumericosSEP(Grupo $grupo, Alumno $alumno, Collection $periodos): array
    {
        $grado = $grupo->grado;
        $ciclo = $grupo->cicloEscolar;

        $ponderacionesCampos = PonderacionCampo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->where('grado_id', $grado->grado_id)
            ->pluck('ponderacion', 'campo_formativo_id');

        $estructuraCamposSEP = DB::table('estructura_curricular as ec')
            ->join('campos_formativos as cf', 'ec.campo_id', '=', 'cf.campo_id')
            ->join('materias as m', 'ec.materia_id', '=', 'm.materia_id')
            ->where('ec.grado_id', $grado->grado_id)
            ->whereIn('cf.nombre', self::CAMPOS_FORMATIVOS_SEP)
            ->select('ec.campo_id', 'cf.nombre as nombre_campo', 'ec.materia_id', 'm.nombre as nombre_materia', 'ec.ponderacion_materia')
            ->get();

        $idsMateriasSEP = $estructuraCamposSEP->pluck('materia_id')->unique();

        $criteriosPromedio = MateriaCriterio::whereIn('materia_id', $idsMateriasSEP)
            ->whereHas('catalogoCriterio', fn($q) => $q->where('nombre', 'Promedio'))
            ->pluck('materia_id', 'materia_criterio_id');

        $calificaciones = Calificacion::where('alumno_id', $alumno->alumno_id)
            ->whereIn('periodo_id', $periodos->pluck('periodo_id'))
            ->whereIn('materia_criterio_id', $criteriosPromedio->keys())
            ->get();

        $mapaNotas = [];
        foreach ($calificaciones as $cal) {
            $materiaId = $criteriosPromedio->get($cal->materia_criterio_id);
            if ($materiaId) {
                $mapaNotas[$materiaId . '_' . $cal->periodo_id] = $cal->calificacion_obtenida;
            }
        }

        return $this->procesarCamposSEP(
            $estructuraCamposSEP->groupBy('nombre_campo'),
            $periodos,
            $mapaNotas,
            $ponderacionesCampos,
            false // Forzado a numérico para la tabla de seguimiento
        );
    }
}