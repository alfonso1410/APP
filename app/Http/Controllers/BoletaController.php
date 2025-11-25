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
use App\Models\CatalogoCriterio;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        'Programa Académico',
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
        'Reading Program',
        'Habits'
    ];

    private const BLOQUES_CRITERIOS_MAPA = [
        'Programa Académico' => 'PROGRAMA ACADEMICO',
        'Programa de Lectura' => 'PROGRAMA DE LECTURA',
        'Reading Program' => 'READING PROGRAM',
        'Habits' => 'HABITS', 
        'Hábitos' => 'HÁBITOS', 
    ];

    private const CAMPOS_FORMATIVOS_SEP = [
        'Lenguajes',
        'Saberes y Pensamiento Científico',
        'Ética, Naturaleza y Sociedad',
        'De lo Humano a lo Comunitario',
    ];

    private function getCampoOrderList(string $nivelNombre): ?array
    {
        switch (strtoupper($nivelNombre)) {
            case 'PREESCOLAR': return self::ORDEN_CAMPOS_PREESCOLAR;
            case 'PRIMARIA': return self::ORDEN_CAMPOS_PRIMARIA;
            default: return null;
        }
    }

    /**
     * Helper para convertir calificación numérica a letra (Preescolar)
     */
    private function getLetraCalificacion($valor)
    {
        if (!is_numeric($valor)) return '';

        $val = round($valor);

        if ($val == 10) return 'E';
        if ($val == 9)  return 'MB';
        if ($val == 8)  return 'B';
        if ($val >= 6 && $val <= 7) return 'R';
        if ($val < 6)   return 'NA'; 

        return 'NP'; 
    }

    public function index()
    {
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();
        $niveles = Nivel::orderBy('nivel_id')->get(['nivel_id as id', 'nombre']);
        $niveles->push((object)['id' => 'extra', 'nombre' => 'Extracurricular']);

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
        $esPK1 = str_contains(strtoupper($grado->nombre), '1') && $esPreescolar; 

        $periodos = Periodo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->orderBy('fecha_inicio')
            ->get();

        // 1. PONDERACIONES
        $ponderacionesCampos = PonderacionCampo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->where('grado_id', $grado->grado_id)
            ->pluck('ponderacion', 'campo_formativo_id');

        // 2. ESTRUCTURA CURRICULAR COMPLETA (OFICIAL DEL GRADO)
        $estructuraCompleta = DB::table('estructura_curricular as ec')
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

        // 3. CLASIFICACIÓN DE ESTRUCTURAS
        $nombresMateriaBloque = array_keys(self::BLOQUES_CRITERIOS_MAPA);
        
        // A. SEP
        $estructuraCamposSEP = $estructuraCompleta->whereIn('nombre_campo', self::CAMPOS_FORMATIVOS_SEP)
                                                  ->whereNotIn('nombre_materia', $nombresMateriaBloque);

        // B. PRINCETON
        $estructuraPrinceton = $estructuraCompleta->where('nombre_campo', 'Programa Princeton');

        // C. English y Bloques
        $estructuraEnglish = $estructuraCompleta->where('nombre_campo', 'English');
        $estructuraBloquesCriterios = $estructuraCompleta->whereIn('nombre_materia', $nombresMateriaBloque);

        // ==================================================================================
        // 3.5. DETECCIÓN DE EXTRACURRICULARES (POR CALIFICACIONES EXISTENTES)
        // ==================================================================================
        // Buscamos materias que tengan calificación para este alumno en este ciclo
        // pero que NO estén en la estructura curricular oficial.
        if (!$esPK1) {
            $idsMateriasOficiales = $estructuraCompleta->pluck('materia_id')->toArray();
            
            // Obtenemos IDs de materias donde el alumno tiene calificación registrada
            $idsMateriasConCalif = DB::table('calificaciones as c')
                ->join('materia_criterios as mc', 'c.materia_criterio_id', '=', 'mc.materia_criterio_id')
                ->join('periodos as p', 'c.periodo_id', '=', 'p.periodo_id')
                ->where('c.alumno_id', $alumno->alumno_id)
                ->where('p.ciclo_escolar_id', $ciclo->ciclo_escolar_id)
                ->distinct()
                ->pluck('mc.materia_id')
                ->toArray();

            // Filtramos las que son "Extras" (tienen calif pero no están en la malla oficial)
            $idsExtras = array_diff($idsMateriasConCalif, $idsMateriasOficiales);

            if (!empty($idsExtras)) {
                $materiasExtras = Materia::whereIn('materia_id', $idsExtras)->get();

                foreach ($materiasExtras as $extra) {
                    // Filtro de seguridad: Evitar meter Inglés duplicado si por error no estaba en la malla
                    $nombreUpper = strtoupper($extra->nombre);
                    if (str_contains($nombreUpper, 'INGLÉS') || str_contains($nombreUpper, 'ENGLISH') || str_contains($nombreUpper, 'LENGUA EXTRA')) {
                        continue;
                    }

                    // Agregamos a la estructura Princeton simulada
                    // Verificamos que no esté ya agregada
                    if (!$estructuraPrinceton->contains('materia_id', $extra->materia_id)) {
                        $nodoExtra = (object) [
                            'campo_id' => 0, // Dummy
                            'nombre_campo' => 'Programa Princeton',
                            'materia_id' => $extra->materia_id,
                            'nombre_materia' => $extra->nombre, // Ej: Deportes
                            'ponderacion_materia' => 0
                        ];
                        $estructuraPrinceton->push($nodoExtra);
                    }
                }
            }
        }
        // ==================================================================================


        // 4. OBTENCIÓN DE CALIFICACIONES BASE
        // Ahora $estructuraPrinceton incluye las extras, así que pedimos sus calificaciones
        $idsMateriasPromedio = $estructuraCamposSEP->pluck('materia_id')
                                ->merge($estructuraPrinceton->pluck('materia_id'))
                                ->merge($estructuraEnglish->pluck('materia_id'));

        $criteriosPromedioIds = MateriaCriterio::whereIn('materia_id', $idsMateriasPromedio)
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

        // ==================================================================================
        //  PROCESAR BLOQUE ENGLISH E INYECCIÓN EN LENGUA EXTRANJERA
        // ==================================================================================
        $datosEnglish = null;
        if ($estructuraEnglish->isNotEmpty()) {
            $datosEnglish = $this->procesarBloqueMaterias(
                $estructuraEnglish,
                $periodos,
                $mapaCalificacionesPAS,
                'ENGLISH',
                $esPreescolar
            );

            if (isset($datosEnglish['promedios_bloque_numericos'])) {
                $fakeIdLengua = 999999; 

                $materiaLenguaExtranjera = $estructuraCamposSEP->first(function($item) {
                    return str_contains(strtoupper($item->nombre_materia), 'LENGUA EXTRANJERA') 
                        || str_contains(strtoupper($item->nombre_materia), 'INGLÉS')
                        || str_contains(strtoupper($item->nombre_materia), 'ENGLISH');
                });

                $targetMateriaId = $materiaLenguaExtranjera ? $materiaLenguaExtranjera->materia_id : $fakeIdLengua;

                // Si es Preescolar y no existe, la creamos
                if ($esPreescolar && !$materiaLenguaExtranjera) {
                    $campoLenguajes = $estructuraCamposSEP->where('nombre_campo', 'Lenguajes')->first();
                    // Asegurarse que existe el campo padre
                    if ($campoLenguajes) {
                        $materiaSimulada = (object)[
                            'campo_id' => $campoLenguajes->campo_id,
                            'nombre_campo' => 'Lenguajes',
                            'materia_id' => $fakeIdLengua,
                            'nombre_materia' => 'Lengua Extranjera',
                            'ponderacion_materia' => 0
                        ];
                        $estructuraCamposSEP->push($materiaSimulada);
                    }
                }

                // Inyectamos el promedio numérico
                foreach ($periodos as $periodo) {
                    $promedioNum = $datosEnglish['promedios_bloque_numericos'][$periodo->periodo_id] ?? null;
                    if (is_numeric($promedioNum)) {
                        $llave = $targetMateriaId . '_' . $periodo->periodo_id;
                        $mapaCalificacionesPAS[$llave] = $promedioNum;
                    }
                }
            }
        }
        // ==================================================================================

        // 5. PROCESAR CAMPOS SEP
        $camposFormativosSEP_Agrupados = $estructuraCamposSEP->groupBy('nombre_campo');

        if ($orderList) {
            $camposFormativosSEP_Agrupados = $camposFormativosSEP_Agrupados->sortBy(function ($materias, $nombreCampo) use ($orderList) {
                $position = array_search($nombreCampo, $orderList);
                return ($position === false) ? 99 : $position;
            });
        }

        $boletaDataSEP = $this->procesarCamposSEP(
            $camposFormativosSEP_Agrupados,
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos,
            $esPreescolar
        );

        // 6. CALCULAR PROMEDIO GENERAL SEP
        $promediosGeneralesSEP = [];
        $sumasSEP = [];
        $contadoresSEP = [];
        foreach ($periodos as $p) {
            $sumasSEP[$p->periodo_id] = 0;
            $contadoresSEP[$p->periodo_id] = 0;
        }
        $sumaFinalSEP = 0;
        $contadorFinalSEP = 0;

        foreach ($boletaDataSEP['campos'] as $campoData) {
            foreach ($periodos as $p) {
                $calif = $campoData['calificaciones_sep'][$p->periodo_id] ?? null;
                if (is_numeric($calif) && !$esPreescolar) {
                    $sumasSEP[$p->periodo_id] += $calif;
                    $contadoresSEP[$p->periodo_id]++;
                }
            }
            $califFinal = $campoData['promedio_final_sep'] ?? null;
            if (is_numeric($califFinal) && !$esPreescolar) {
                $sumaFinalSEP += $califFinal;
                $contadorFinalSEP++;
            }
        }

        foreach ($periodos as $p) {
            $promediosGeneralesSEP[$p->periodo_id] = ($contadoresSEP[$p->periodo_id] > 0) 
                ? round($sumasSEP[$p->periodo_id] / $contadoresSEP[$p->periodo_id], 1) 
                : null;
        }
        $promediosGeneralesSEP['final'] = ($contadorFinalSEP > 0) 
            ? round($sumaFinalSEP / $contadorFinalSEP, 1) 
            : null;

        // 7. PROCESAR PRINCETON (Ya incluye Extracurriculares)
        $boletaDataPrinceton = $this->procesarCamposSEP(
            $estructuraPrinceton->groupBy('nombre_campo'),
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos,
            $esPreescolar
        );

        // Extraer datos Princeton para combinado
        $datosPrincetonParaCombinado = ['califs_para_promedio_final' => []];
        foreach ($periodos as $p) $datosPrincetonParaCombinado['califs_para_promedio_final'][$p->periodo_id] = [];
        foreach ($estructuraPrinceton as $matPrinc) {
            foreach ($periodos as $p) {
                $llave = $matPrinc->materia_id . '_' . $p->periodo_id;
                // Usamos el valor numérico crudo del mapa
                if (isset($mapaCalificacionesPAS[$llave]) && is_numeric($mapaCalificacionesPAS[$llave])) {
                    $datosPrincetonParaCombinado['califs_para_promedio_final'][$p->periodo_id][] = $mapaCalificacionesPAS[$llave];
                }
            }
        }

        // 8. PROCESAR BLOQUES DE CRITERIOS
        $datosBloquesCriterios = [];
        if ($datosEnglish) {
            $datosBloquesCriterios['ENGLISH'] = $datosEnglish;
        }

        foreach ($estructuraBloquesCriterios as $materiaBloque) {
            $key = $materiaBloque->nombre_materia;
            $titulo = self::BLOQUES_CRITERIOS_MAPA[$key] ?? $key;
            
            $datosBloquesCriterios[$titulo] = $this->procesarBloqueCriterios(
                $alumno,
                $materiaBloque->materia_id,
                $periodos,
                $titulo,
                $esPreescolar
            );
        }

        // 9. ASISTENCIAS
        $datosAsistencias = $this->procesarAsistencias($alumno, $ciclo, $periodos);

        // 10. PROMEDIOS COMBINADOS
        $bloqueAcademicoKey = ($esPreescolar && $esPK1) ? 'PROGRAMA DE LECTURA' : 'PROGRAMA ACADEMICO';
        
        $promediosCombinadosAcademico = $this->calcularPromediosCombinados(
            $periodos,
            [
                $datosBloquesCriterios[$bloqueAcademicoKey] ?? null,
                $datosPrincetonParaCombinado 
            ]
        );

        $promediosCombinadosHabits = $this->calcularPromediosCombinados(
            $periodos,
            [
                $datosBloquesCriterios['READING PROGRAM'] ?? null,
                $datosBloquesCriterios['HABITS'] ?? null, 
            ]
        );

        // 11. MAESTROS
        $titular = DB::table('grupo_titular as gt')
                    ->join('users as m', 'gt.maestro_titular_id', '=', 'm.id') 
                    ->where('gt.grupo_id', $grupo->grupo_id)
                    ->where('gt.idioma', 'ESPAÑOL') 
                    ->select('m.name', 'm.apellido_paterno', 'm.apellido_materno')
                    ->first();
        
        $maestroEspanol = $titular ? 'LIC. ' . strtoupper("{$titular->name} {$titular->apellido_paterno} {$titular->apellido_materno}") : 'LIC. [MAESTRO ESPAÑOL NO ASIGNADO]';

        $teacher = DB::table('grupo_titular as gt') 
                    ->join('users as m', 'gt.maestro_titular_id', '=', 'm.id') 
                    ->where('gt.grupo_id', $grupo->grupo_id)
                    ->where('gt.idioma', 'INGLES') 
                    ->select('m.name', 'm.apellido_paterno', 'm.apellido_materno') 
                    ->first();

        $maestroIngles = $teacher ? 'LIC. ' . strtoupper("{$teacher->name} {$teacher->apellido_paterno} {$teacher->apellido_materno}") : 'LIC. [TEACHER NO ASIGNADO]';

        $data = [
            'alumno' => $alumno,
            'grupo' => $grupo,
            'ciclo' => $ciclo,
            'periodos' => $periodos,
            'esPreescolar' => $esPreescolar,
            'esPK1' => $esPK1,
            
            'dataCamposSEP' => $boletaDataSEP['campos'],
            'dataPrinceton' => $boletaDataPrinceton['campos'], 
            'promediosFinalesSEP' => $boletaDataSEP['promediosFinales'],
            'promediosGeneralesSEP' => $promediosGeneralesSEP,
            'datosBloques' => $datosBloquesCriterios,
            'datosAsistencias' => $datosAsistencias,

            'promediosCombinadosAcademico' => $promediosCombinadosAcademico,
            'promediosCombinadosHabits' => $promediosCombinadosHabits,

            'maestroEspanol' => $maestroEspanol,
            'maestroIngles' => $maestroIngles,
        ];

        $pdf = PDF::loadView('reportes.boleta-FINAL-PORFAVOR', $data, [
            'format' => 'Legal-L', 
            'orientation' => 'L',
            'mode' => 'utf-8'
        ]);

        return $pdf->stream('boleta-' . $alumno->apellido_paterno . '-' . $alumno->nombres . '.pdf');
    }

    private function procesarBloqueMaterias($estructuraMaterias, $periodos, $mapaCalificacionesPAS, $tituloBloque, $esPreescolar = false)
    {
        $filas = [];
        $promediosBloque = [];
        $promediosBloqueNumericos = [];

        foreach ($periodos as $periodo) {
            $promediosBloque[$periodo->periodo_id] = ['suma' => 0, 'contador' => 0];
        }

        foreach ($estructuraMaterias as $materia) {
            $califsMateria = [];
            
            foreach ($periodos as $periodo) {
                $llave = $materia->materia_id . '_' . $periodo->periodo_id;
                $nota = $mapaCalificacionesPAS[$llave] ?? null;
                
                if ($esPreescolar) {
                    $notaMostrada = $this->getLetraCalificacion($nota);
                    $notaNum = is_numeric($nota) ? $nota : null;
                } else {
                    $notaMostrada = is_numeric($nota) ? round($nota, 1) : null;
                    $notaNum = $notaMostrada;
                }
                
                $califsMateria[$periodo->periodo_id] = $notaMostrada;

                if (is_numeric($notaNum)) {
                    $promediosBloque[$periodo->periodo_id]['suma'] += $notaNum;
                    $promediosBloque[$periodo->periodo_id]['contador']++;
                }
            }

            $sumaMat = 0; $countMat = 0;
            foreach ($periodos as $p) {
                $llave = $materia->materia_id . '_' . $p->periodo_id;
                $val = $mapaCalificacionesPAS[$llave] ?? null;
                if(is_numeric($val)) { $sumaMat += $val; $countMat++; }
            }
            $promedioMateriaNum = ($countMat > 0) ? round($sumaMat / $countMat, 1) : null;
            
            $promedioMateriaShow = $esPreescolar 
                ? $this->getLetraCalificacion($promedioMateriaNum)
                : $promedioMateriaNum;

            $filas[] = [
                'nombre' => $materia->nombre_materia,
                'calificaciones' => $califsMateria,
                'promedio' => $promedioMateriaShow
            ];
        }

        $filaPromedios = [];
        $sumaPromedioFinal = 0;
        $countPromedioFinal = 0;

        foreach ($periodos as $periodo) {
            $suma = $promediosBloque[$periodo->periodo_id]['suma'];
            $count = $promediosBloque[$periodo->periodo_id]['contador'];
            $promedioPeriodo = ($count > 0) ? round($suma / $count, 1) : null;
            
            $promediosBloqueNumericos[$periodo->periodo_id] = $promedioPeriodo;

            $filaPromedios[$periodo->periodo_id] = $esPreescolar 
                ? $this->getLetraCalificacion($promedioPeriodo)
                : $promedioPeriodo;

            if (is_numeric($promedioPeriodo)) {
                $sumaPromedioFinal += $promedioPeriodo;
                $countPromedioFinal++;
            }
        }
        
        $promFinalNum = ($countPromedioFinal > 0) ? round($sumaPromedioFinal / $countPromedioFinal, 1) : null;
        $filaPromedios['promedio'] = $esPreescolar 
            ? $this->getLetraCalificacion($promFinalNum)
            : $promFinalNum;

        return [
            'titulo' => $tituloBloque,
            'criterios' => $filas,
            'promedios_bloque' => $filaPromedios,
            'promedios_bloque_numericos' => $promediosBloqueNumericos,
            'califs_para_promedio_final' => [] 
        ];
    }

    private function procesarCamposSEP($camposFormativos, $periodos, $mapaCalificacionesPAS, $ponderacionesCampos, $esPreescolar = false)
    {
        $dataCampos = [];
        $promediosFinales = [];
        $promediosFinalesCalculados = [];

        foreach ($periodos as $periodo) {
            $promediosFinales[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
            $promediosFinalesCalculados[$periodo->periodo_id] = null; 
        }

        foreach ($camposFormativos as $nombreCampo => $materias) {
            // Evitar error si materias viene vacía por alguna razón
            if ($materias->isEmpty()) continue;

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
                $sumaMateriaPAS = 0; $countMateriaPAS = 0;
                $ponderacionMateria = $materia->ponderacion_materia / 100.0;

                foreach ($periodos as $periodo) {
                    $llave = $materia->materia_id . '_' . $periodo->periodo_id;
                    $notaPAS = $mapaCalificacionesPAS[$llave] ?? null;
                    
                    if ($esPreescolar) {
                        // Convertimos el número a Letra
                        $califsMateria_PAS[$periodo->periodo_id] = $this->getLetraCalificacion($notaPAS);
                    } else {
                        $califsMateria_PAS[$periodo->periodo_id] = $notaPAS; 
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

                $promedioPAS_MateriaNum = ($countMateriaPAS > 0) ? round($sumaMateriaPAS / $countMateriaPAS, 2) : null;
                
                $promedioPAS_Mostrado = $esPreescolar 
                    ? $this->getLetraCalificacion($promedioPAS_MateriaNum)
                    : $promedioPAS_MateriaNum;

                if (is_numeric($promedioPAS_MateriaNum) && !$esPreescolar) {
                    $promediosSEP_Campo['promedio_pas']['suma'] += $promedioPAS_MateriaNum;
                    $promediosSEP_Campo['promedio_pas']['contador']++;
                }

                $dataMaterias[] = [
                    'nombre' => $materia->nombre_materia,
                    'calificaciones_pas' => $califsMateria_PAS,
                    'promedio_pas' => $promedioPAS_Mostrado
                ];
            }

            $califsMateria_SEP = []; 
            if (!$esPreescolar) {
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
            }

            $promedioSEP_Materia = null;
            if (!$esPreescolar && $promediosSEP_Campo['promedio_sep']['contador'] > 0) {
                $promedioSEP_Materia = round($promediosSEP_Campo['promedio_sep']['suma'] / $promediosSEP_Campo['promedio_sep']['contador'], 2);
            }

            $promedioFinalPAS = null;
            if (!$esPreescolar && $promediosSEP_Campo['promedio_pas']['contador'] > 0) {
                $promedioFinalPAS = round($promediosSEP_Campo['promedio_pas']['suma'] / $promediosSEP_Campo['promedio_pas']['contador'], 2);
            }

            $dataCampos[] = [
                'nombre' => $nombreCampo,
                'materias' => $dataMaterias,
                'calificaciones_sep' => $califsMateria_SEP,
                'promedio_final_pas' => $promedioFinalPAS,
                'promedio_final_sep' => $promedioSEP_Materia
            ];
        }

        if (!$esPreescolar) {
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
        }

        return [
            'campos' => $dataCampos,
            'promediosFinales' => $promediosFinalesCalculados
        ];
    }

    private function procesarBloqueCriterios(Alumno $alumno, int $materiaId, Collection $periodos, string $tituloBloque, $esPreescolar = false)
    {
        $criterios = MateriaCriterio::with('catalogoCriterio')
            ->where('materia_id', $materiaId)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->whereNotIn('nombre', ['Promedio', 'Faltas']);
            })
            ->get()
            ->sortBy(function($mc) { return $mc->catalogoCriterio->nombre ?? 'ZZZ'; });
            
        $criterioIds = $criterios->pluck('materia_criterio_id');

        $calificaciones = Calificacion::where('alumno_id', $alumno->alumno_id)
            ->whereIn('periodo_id', $periodos->pluck('periodo_id'))
            ->whereIn('materia_criterio_id', $criterioIds)
            ->get();

        $mapaCalificaciones = [];
        foreach ($calificaciones as $cal) {
            $llave = $cal->materia_criterio_id . '_' . $cal->periodo_id;
            $mapaCalificaciones[$llave] = $cal->calificacion_obtenida;
        }

        $filasCriterios = [];
        $promediosBloque = []; 
        $califsParaPromedioFinal = [];
        $filaPromedios = []; 

        foreach ($periodos as $periodo) {
            $promediosBloque[$periodo->periodo_id] = ['suma' => 0, 'contador' => 0];
            $califsParaPromedioFinal[$periodo->periodo_id] = []; 
            $filaPromedios[$periodo->periodo_id] = null; 
        }

        foreach ($criterios as $criterio) {
            $califsCriterio = []; 
            $sumaCriterio = 0;
            $countCriterio = 0;

            foreach ($periodos as $periodo) {
                $llave = $criterio->materia_criterio_id . '_' . $periodo->periodo_id;
                $nota = $mapaCalificaciones[$llave] ?? null;
                
                if ($esPreescolar) {
                    $notaMostrada = $this->getLetraCalificacion($nota);
                    $notaNum = is_numeric($nota) ? $nota : null;
                } else {
                    $notaMostrada = is_numeric($nota) ? round($nota, 1) : null;
                    $notaNum = $notaMostrada;
                }

                $califsCriterio[$periodo->periodo_id] = $notaMostrada; 

                if (is_numeric($notaNum)) {
                    $sumaCriterio += $notaNum;
                    $countCriterio++;
                    $promediosBloque[$periodo->periodo_id]['suma'] += $notaNum;
                    $promediosBloque[$periodo->periodo_id]['contador']++;
                    $califsParaPromedioFinal[$periodo->periodo_id][] = $notaNum;
                }
            }

            $promedioCriterioNum = ($countCriterio > 0) ? round($sumaCriterio / $countCriterio, 1) : null;
            $promedioCriterioShow = $esPreescolar 
                ? $this->getLetraCalificacion($promedioCriterioNum)
                : $promedioCriterioNum;
            
            $filasCriterios[] = [
                'nombre' => $criterio->catalogoCriterio->nombre ?? 'Criterio No Encontrado',
                'calificaciones' => $califsCriterio,
                'promedio' => $promedioCriterioShow,
            ];
        }

        $sumaPromedioFinal = 0;
        $countPromedioFinal = 0;

        foreach ($periodos as $periodo) {
            $suma = $promediosBloque[$periodo->periodo_id]['suma'];
            $count = $promediosBloque[$periodo->periodo_id]['contador'];
            $promedioPeriodo = ($count > 0) ? round($suma / $count, 1) : null;
            
            $filaPromedios[$periodo->periodo_id] = $esPreescolar 
                ? $this->getLetraCalificacion($promedioPeriodo)
                : $promedioPeriodo;

            if (is_numeric($promedioPeriodo)) {
                $sumaPromedioFinal += $promedioPeriodo;
                $countPromedioFinal++;
            }
        }
        
        $promFinalNum = ($countPromedioFinal > 0) ? round($sumaPromedioFinal / $countPromedioFinal, 1) : null;
        $filaPromedios['promedio'] = $esPreescolar 
            ? $this->getLetraCalificacion($promFinalNum)
            : $promFinalNum;

        return [
            'titulo' => $tituloBloque,
            'criterios' => $filasCriterios,
            'promedios_bloque' => $filaPromedios,
            'califs_para_promedio_final' => $califsParaPromedioFinal,
        ];
    }

    private function calcularPromediosCombinados(Collection $periodos, array $bloquesDatos)
    {
        $califsPorPeriodo = [];
        $promediosFinales = []; 
        foreach ($periodos as $periodo) {
            $califsPorPeriodo[$periodo->periodo_id] = [];
            $promediosFinales[$periodo->periodo_id] = null; 
        }
        foreach ($bloquesDatos as $bloque) {
            if (empty($bloque) || empty($bloque['califs_para_promedio_final'])) { continue; }
            foreach ($bloque['califs_para_promedio_final'] as $periodoId => $califs) {
                if (isset($califsPorPeriodo[$periodoId])) {
                    $califsPorPeriodo[$periodoId] = array_merge($califsPorPeriodo[$periodoId], $califs);
                }
            }
        }
        $sumaTotal = 0; $countTotal = 0;
        foreach ($periodos as $periodo) {
            $califs = $califsPorPeriodo[$periodo->periodo_id]; 
            $count = count($califs);
            $suma = array_sum($califs);
            $promedio = ($count > 0) ? round($suma / $count, 1) : null;
            $promediosFinales[$periodo->periodo_id] = $promedio; 
            if (is_numeric($promedio)) {
                $sumaTotal += $promedio;
                $countTotal++;
            }
        }
        $promediosFinales['promedio'] = ($countTotal > 0) ? round($sumaTotal / $countTotal, 1) : null;
        return $promediosFinales;
    }

    private function procesarAsistencias(Alumno $alumno, CicloEscolar $ciclo, Collection $periodos)
    {
        $registros = DB::table('registro_asistencia')
            ->where('alumno_id', $alumno->alumno_id)
            ->whereIn('periodo_id', $periodos->pluck('periodo_id'))
            ->select('periodo_id', 'idioma', 'tipo_asistencia')
            ->get();
        $datos = []; 
        $totales = [
            'ESP_asistencias' => 0, 'ENG_asistencias' => 0, 'TOTAL_asistencias' => 0,
            'ESP_retardos' => 0, 'ENG_retardos' => 0, 'TOTAL_retardos' => 0,
            'ESP_inasistencias' => 0, 'ENG_inasistencias' => 0, 'TOTAL_inasistencias' => 0,
        ];
        foreach ($periodos as $periodo) {
            $datos[$periodo->periodo_id] = [
                'ESP_asistencias' => 0, 'ENG_asistencias' => 0, 'TOTAL_asistencias' => 0,
                'ESP_retardos' => 0, 'ENG_retardos' => 0, 'TOTAL_retardos' => 0,
                'ESP_inasistencias' => 0, 'ENG_inasistencias' => 0, 'TOTAL_inasistencias' => 0,
            ];
        }
        foreach ($periodos as $periodo) {
            $registrosPeriodo = $registros->where('periodo_id', $periodo->periodo_id);
            $espAsist = $registrosPeriodo->where('idioma', 'ESPAÑOL')->where('tipo_asistencia', 'PRESENTE')->count(); 
            $engAsist = $registrosPeriodo->where('idioma', 'INGLES')->where('tipo_asistencia', 'PRESENTE')->count(); 
            $espRetardo = $registrosPeriodo->where('idioma', 'ESPAÑOL')->where('tipo_asistencia', 'RETARDO')->count(); 
            $engRetardo = $registrosPeriodo->where('idioma', 'INGLES')->where('tipo_asistencia', 'RETARDO')->count(); 
            $espFalta = $registrosPeriodo->where('idioma', 'ESPAÑOL')->where('tipo_asistencia', 'FALTA')->count(); 
            $engFalta = $registrosPeriodo->where('idioma', 'INGLES')->where('tipo_asistencia', 'FALTA')->count(); 

            $datos[$periodo->periodo_id]['ESP_asistencias'] = $espAsist;
            $datos[$periodo->periodo_id]['ENG_asistencias'] = $engAsist;
            $datos[$periodo->periodo_id]['TOTAL_asistencias'] = $espAsist + $engAsist;

            $datos[$periodo->periodo_id]['ESP_retardos'] = $espRetardo;
            $datos[$periodo->periodo_id]['ENG_retardos'] = $engRetardo;
            $datos[$periodo->periodo_id]['TOTAL_retardos'] = $espRetardo + $engRetardo;
            
            $datos[$periodo->periodo_id]['ESP_inasistencias'] = $espFalta;
            $datos[$periodo->periodo_id]['ENG_inasistencias'] = $engFalta;
            $datos[$periodo->periodo_id]['TOTAL_inasistencias'] = $espFalta + $engFalta;

            $totales['ESP_asistencias'] += $espAsist;
            $totales['ENG_asistencias'] += $engAsist;
            $totales['TOTAL_asistencias'] += ($espAsist + $engAsist);
            
            $totales['ESP_retardos'] += $espRetardo;
            $totales['ENG_retardos'] += $engRetardo;
            $totales['TOTAL_retardos'] += ($espRetardo + $engRetardo);

            $totales['ESP_inasistencias'] += $espFalta;
            $totales['ENG_inasistencias'] += $engFalta;
            $totales['TOTAL_inasistencias'] += ($espFalta + $engFalta);
        }
        return ['periodos' => $datos, 'totales' => $totales];
    }
}