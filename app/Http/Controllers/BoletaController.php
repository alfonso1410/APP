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
use App\Services\CalificacionService;
use PDF;

class BoletaController extends Controller
{

protected CalificacionService $calificacionService;

    public function __construct(CalificacionService $calificacionService)
    {
        $this->calificacionService = $calificacionService;
    }


    private const ORDEN_CAMPOS_PREESCOLAR = [
        'Lenguajes',
        'Saberes y Pensamiento Científico',
        'Ética, Naturaleza y Sociedad',
        'De lo Humano a lo Comunitario',
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
        'Programa de Lectura' => 'PROGRAMA DE LECTURA', 
        'Programa Académico PK2' => 'PROGRAMA ACADEMICO',
        'Programa Académico PK3' => 'PROGRAMA ACADEMICO',
        'Programa Académico Primaria' => 'PROGRAMA ACADEMICO',
        'Reading Program' => 'READING PROGRAM',
        'Habits' => 'HABITS', 
        'Hábitos' => 'HÁBITOS',
    ];
    
    private const MATERIAS_PRINCETON_EXCLUIDAS_PK = [
        'Phonics/Vocabulary',
        'Programa de Lectura',
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
            case 'PREESCOLAR': 
                $ordenBase = array_filter(self::ORDEN_CAMPOS_PREESCOLAR, function($campo) {
                    return in_array($campo, self::CAMPOS_FORMATIVOS_SEP) || $campo === 'Programa Princeton';
                });
                return array_values($ordenBase);
            case 'PRIMARIA': return self::ORDEN_CAMPOS_PRIMARIA;
            default: return null;
        }
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
        $nombresMateriasBloqueCrit = array_keys(self::BLOQUES_CRITERIOS_MAPA);
        
        $estructuraCamposSEP = $estructuraCompleta->whereIn('nombre_campo', self::CAMPOS_FORMATIVOS_SEP)
                                                ->whereNotIn('nombre_materia', $nombresMateriasBloqueCrit);

        $estructuraPrinceton = $estructuraCompleta->where('nombre_campo', 'Programa Princeton')
            ->whereNotIn('nombre_materia', $nombresMateriasBloqueCrit)
            ->whereNotIn('nombre_materia', self::MATERIAS_PRINCETON_EXCLUIDAS_PK); 

        $estructuraEnglish = $estructuraCompleta->where('nombre_campo', 'English');
        $estructuraBloquesCriterios = $estructuraCompleta->whereIn('nombre_materia', $nombresMateriasBloqueCrit);


        
      $gruposExtrasDelAlumno = $alumno->grupos()
    ->where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
    ->wherePivot('es_actual', 1)
    ->where('tipo_grupo', 'EXTRA') 
    ->get();

// Extraemos las materias de esos grupos extracurriculares
$materiasExtras = $gruposExtrasDelAlumno->pluck('materias')
    ->flatten()
    ->unique('materia_id');

if ($materiasExtras->isNotEmpty()) {
    $idsMateriasEnglish = $estructuraEnglish->pluck('materia_id')->toArray();

    foreach ($materiasExtras as $extra) {
        // Filtros de seguridad
        if ($estructuraPrinceton->contains('materia_id', $extra->materia_id) || 
            in_array($extra->materia_id, $idsMateriasEnglish) ||
            in_array($extra->nombre, self::MATERIAS_PRINCETON_EXCLUIDAS_PK)) { 
                continue;
        }

        $nodoExtra = (object) [
            'campo_id' => 0, 
            'nombre_campo' => 'Programa Princeton',
            'materia_id' => $extra->materia_id,
            'nombre_materia' => $extra->nombre, 
            'ponderacion_materia' => 0
        ];
        $estructuraPrinceton->push($nodoExtra);
    }
}
        // ==================================================================================


        // 4. OBTENCIÓN DE CALIFICACIONES BASE
        $materiaEscuelaPadres = $estructuraCompleta->first(function($item) {
            return str_contains(strtoupper($item->nombre_materia), 'ESCUELA PARA PADRES');
        });

        $idsMateriasPromedio = $estructuraCamposSEP->pluck('materia_id')
                                                ->merge($estructuraPrinceton->pluck('materia_id'))
                                                ->merge($estructuraEnglish->pluck('materia_id'));

        if ($materiaEscuelaPadres) {
            $idsMateriasPromedio->push($materiaEscuelaPadres->materia_id);
        }

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
        //  PROCESAR BLOQUE ENGLISH
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

                if ($esPreescolar && !$materiaLenguaExtranjera) {
                    $campoLenguajes = $estructuraCamposSEP->where('nombre_campo', 'Lenguajes')->first();
                    if ($campoLenguajes) {
                        $materiaSimulada = (object)[
                            'campo_id' => $campoLenguajes->campo_id,
                            'nombre_campo' => 'Lenguajes',
                            'materia_id' => $fakeIdLengua,
                            'nombre_materia' => 'Lengua Extranjera',
                            'ponderacion_materia' => 0
                        ];
                        if ($estructuraCamposSEP instanceof Collection) {
                             $estructuraCamposSEP->push($materiaSimulada);
                             $camposFormativosSEP_Agrupados = $estructuraCamposSEP->groupBy('nombre_campo');
                        }
                    }
                }

                foreach ($periodos as $periodo) {
                    $promedioNum = $datosEnglish['promedios_bloque_numericos'][$periodo->periodo_id] ?? null;
                    if (is_numeric($promedioNum)) {
                        $llave = $targetMateriaId . '_' . $periodo->periodo_id;
                        $mapaCalificacionesPAS[$llave] = $promedioNum;
                    }
                }
            }
        }

        // 5. PROCESAR CAMPOS SEP
        $camposFormativosSEP_Agrupados = isset($camposFormativosSEP_Agrupados) ? $camposFormativosSEP_Agrupados : $estructuraCamposSEP->groupBy('nombre_campo');

        if ($orderList) {
            $camposFormativosSEP_Agrupados = $camposFormativosSEP_Agrupados->sortBy(function ($materias, $nombreCampo) use ($orderList) {
                $position = array_search($nombreCampo, $orderList);
                return ($position === false) ? 99 : $position;
            });
        }

        $boletaDataSEP = $this->calificacionService->procesarCamposSEP(
            $camposFormativosSEP_Agrupados,
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos,
            $esPreescolar
        );
        
        $promediosSimplesPorCampo = [];
        foreach ($boletaDataSEP['campos'] as $campo) {
            $promediosSimplesPorCampo[$campo['nombre']] = $this->calcularPromedioSimpleMateriasPorPeriodo(
                $campo['materias'] ?? [],
                $periodos,
                $esPreescolar,
                true 
            );
        }

        // 5.5. RECOLECCIÓN PARA COMBINADO
        $califsMateriasSEP_ParaCombinado = ['califs_for_promedio_final' => []];
        $califsMateriasSEP_Academico = ['califs_para_promedio_final' => []];
        foreach ($periodos as $p) {
            $califsMateriasSEP_ParaCombinado['califs_for_promedio_final'][$p->periodo_id] = [];
            $califsMateriasSEP_Academico['califs_para_promedio_final'][$p->periodo_id] = [];
        }
        
        foreach ($boletaDataSEP['califs_por_materia_numerica'] as $materiaId => $califsPorPeriodo) {
            foreach ($periodos as $p) {
                if (isset($califsPorPeriodo[$p->periodo_id]) && is_numeric($califsPorPeriodo[$p->periodo_id])) {
                    $cal = $califsPorPeriodo[$p->periodo_id];
                    $califsMateriasSEP_ParaCombinado['califs_for_promedio_final'][$p->periodo_id][] = $cal;
                    
                    if (!$esPreescolar) {
                        $materiaInfo = $estructuraCamposSEP->firstWhere('materia_id', $materiaId);
                        if ($materiaInfo && !str_contains(strtoupper($materiaInfo->nombre_campo), 'DE LO HUMANO')) {
                            $califsMateriasSEP_Academico['califs_para_promedio_final'][$p->periodo_id][] = $cal;
                        }
                    } else {
                        $califsMateriasSEP_Academico['califs_para_promedio_final'][$p->periodo_id][] = $cal;
                    }
                }
            }
        }

        $promediosGeneralesPreescolar = [];
        if ($esPreescolar) {
            $promediosGeneralesPreescolar = $this->calcularPromedioGeneralPreescolar(
                $califsMateriasSEP_ParaCombinado['califs_for_promedio_final'], 
                $periodos
            );
        }

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
            $val = ($contadoresSEP[$p->periodo_id] > 0) 
                ? round($sumasSEP[$p->periodo_id] / $contadoresSEP[$p->periodo_id], 1) 
                : null;
            // CAMBIO: Formato a 1 decimal
            $promediosGeneralesSEP[$p->periodo_id] = is_numeric($val) ? number_format($val, 1) : null;
        }
        $valFinal = ($contadorFinalSEP > 0) 
            ? round($sumaFinalSEP / $contadorFinalSEP, 1) 
            : null;
        // CAMBIO: Formato a 1 decimal
        $promediosGeneralesSEP['final'] = is_numeric($valFinal) ? number_format($valFinal, 1) : null;

        // 7. PROCESAR PRINCETON (Ya incluye Extracurriculares)
        $boletaDataPrinceton = $this->calificacionService->procesarCamposSEP(
            $estructuraPrinceton->groupBy('nombre_campo'),
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos,
            $esPreescolar
        );

        $promediosVisualesPrinceton = $this->calcularPromedioSimpleMateriasPorPeriodo(
            $boletaDataPrinceton['campos'][0]['materias'] ?? [], 
            $periodos,
            $esPreescolar,
            true 
        );

        $datosPrincetonParaCombinado = ['califs_para_promedio_final' => []]; 
        foreach ($periodos as $p) $datosPrincetonParaCombinado['califs_para_promedio_final'][$p->periodo_id] = [];
        
        foreach ($boletaDataPrinceton['califs_por_materia_numerica'] as $materiaId => $califsPorPeriodo) {
            foreach ($periodos as $p) {
                if (isset($califsPorPeriodo[$p->periodo_id]) && is_numeric($califsPorPeriodo[$p->periodo_id])) {
                    $datosPrincetonParaCombinado['califs_para_promedio_final'][$p->periodo_id][] = $califsPorPeriodo[$p->periodo_id];
                }
            }
        }

        // 8. PROCESAR BLOQUES DE CRITERIOS
        $datosBloquesCriterios = [];
        if ($datosEnglish) {
            $datosBloquesCriterios['ENGLISH'] = $datosEnglish;
        }
        
        foreach ($estructuraBloquesCriterios->unique('nombre_materia') as $materiaBloque) {
            $key = $materiaBloque->nombre_materia;
            $titulo = self::BLOQUES_CRITERIOS_MAPA[$key] ?? $key;
            
            if (isset($datosBloquesCriterios[$titulo])) {
                    continue;
            }
            
            $datosBloquesCriterios[$titulo] = $this->procesarBloqueCriterios(
                $alumno,
                $materiaBloque->materia_id,
                $periodos,
                $titulo,
                $esPreescolar
            );
        }
        
        // --- MANEJO DE ESCUELA PARA PADRES ---
        $dataEscuelaPadres = null;
        if ($materiaEscuelaPadres) {
            $rowEscuelaPadres = [];
            $rowEscuelaPadres['nombre'] = $materiaEscuelaPadres->nombre_materia; 
            $rowEscuelaPadres['calificaciones'] = [];
            $sumaEP = 0; $countEP = 0;

            foreach ($periodos as $periodo) {
                $llave = $materiaEscuelaPadres->materia_id . '_' . $periodo->periodo_id;
                $nota = $mapaCalificacionesPAS[$llave] ?? null;

                if ($esPreescolar) {
                    $notaMostrada = $this->calificacionService->getLetraCalificacion($nota);
                    $notaNum = is_numeric($nota) ? $nota : null;
                } else {
                    $notaRedondeada = is_numeric($nota) ? round($nota, 1) : null;
                    // CAMBIO: Formato a 1 decimal
                    $notaMostrada = is_numeric($notaRedondeada) ? number_format($notaRedondeada, 1) : null;
                    $notaNum = $notaRedondeada;
                }
                $rowEscuelaPadres['calificaciones'][$periodo->periodo_id] = $notaMostrada;
                if (is_numeric($notaNum)) {
                    $sumaEP += $notaNum;
                    $countEP++;
                }
            }
            $promedioEPNum = ($countEP > 0) ? round($sumaEP / $countEP, 1) : null;
            $rowEscuelaPadres['promedio'] = $esPreescolar 
                ? $this->calificacionService->getLetraCalificacion($promedioEPNum) 
                : (is_numeric($promedioEPNum) ? number_format($promedioEPNum, 1) : null); // CAMBIO: Formato a 1 decimal
            
            $dataEscuelaPadres = $rowEscuelaPadres;
        }

        // 9. ASISTENCIAS
        $datosAsistencias = $this->procesarAsistencias($alumno, $ciclo, $periodos);

        // 10. PROMEDIOS COMBINADOS ACADÉMICOS
        $bloqueAcademicoKey = $esPK1 ? 'PROGRAMA DE LECTURA' : 'PROGRAMA ACADEMICO';
        
        $componentesAcademicosACombinar = [
            $califsMateriasSEP_Academico, 
            $datosPrincetonParaCombinado
        ];

        if (isset($datosBloquesCriterios[$bloqueAcademicoKey])) {
            $componentesAcademicosACombinar[] = $datosBloquesCriterios[$bloqueAcademicoKey];
        }

        $promediosCombinadosAcademico = $this->calcularPromediosCombinados(
            $periodos,
            $componentesAcademicosACombinar,
            $esPreescolar
        );

        $promediosCombinadosHabits = $this->calcularPromediosCombinados(
            $periodos,
            $componentesAcademicosACombinar = [
                $datosBloquesCriterios['READING PROGRAM'] ?? null,
                $datosBloquesCriterios['HABITS'] ?? null, 
            ],
            false
        );

        // 12. MAESTROS
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
            'promediosSimplesPorCampo' => $promediosSimplesPorCampo, 
            'dataPrinceton' => $boletaDataPrinceton['campos'], 
            'promediosPrinceton' => $promediosVisualesPrinceton, 
            'promediosFinalesSEP' => $boletaDataSEP['promediosFinales'],
            'promediosGeneralesSEP' => $promediosGeneralesSEP,
            'promediosGeneralesPreescolar' => $promediosGeneralesPreescolar, 
            'datosBloques' => $datosBloquesCriterios,
            'dataEscuelaPadres' => $dataEscuelaPadres, 
            'datosAsistencias' => $datosAsistencias,
            'promediosCombinadosAcademico' => $promediosCombinadosAcademico,
            'promediosCombinadosHabits' => $promediosCombinadosHabits,
            'bloqueAcademicoKey' => $bloqueAcademicoKey, 
            'maestroEspanol' => $maestroEspanol,
            'maestroIngles' => $maestroIngles,
        ];

        $nombreVista = $esPreescolar 
            ? 'reportes.boleta-preescolar' 
            : 'reportes.boleta-primaria';

      $pdf = PDF::loadView($nombreVista, $data);

$mpdf = $pdf->getMpdf();


        
        // VALIDACIÓN PARA POSICIONAR LA FIRMA SEGÚN EL NIVEL
        if ($esPreescolar) {
            // Coordenadas para la boleta de PREESCOLAR
            $mpdf->Image(
                public_path('Assets/firma.png'),
                23,   // X para preescolar (ajustar)
                244,  // Y para preescolar (ajustar)
                28,   // ancho
                0,
                'png'
            );
        } else {
            // Coordenadas para la boleta de PRIMARIA
            $mpdf->Image(
                public_path('Assets/firma.png'),
                23,   // X para primaria (ajustar según tu PDF)
                248,  // Y para primaria (ajustar según tu PDF)
                28,   // ancho
                0,
                'png'
            );
        }
      
        return $pdf->stream($alumno->apellido_paterno . ' ' . $alumno->apellido_materno . ' ' . $alumno->nombres . '.pdf');
    }

    private function calcularPromedioSimpleMateriasPorPeriodo(array $materias, Collection $periodos, bool $esPreescolar, bool $retornarSoloPromedio = false)
    {
        $promedios = [];
        $sumaFinal = 0;
        $conteoFinal = 0;

        foreach ($periodos as $p) {
            $promedios[$p->periodo_id] = ['suma' => 0, 'contador' => 0];
        }

        foreach ($materias as $materia) {
            foreach ($periodos as $p) {
                $calif = $materia['calificaciones_pas_numerica'][$p->periodo_id] ?? null;
                if (is_numeric($calif)) {
                    $promedios[$p->periodo_id]['suma'] += $calif;
                    $promedios[$p->periodo_id]['contador']++;
                }
            }
        }

        $filaPromedios = [];
        foreach ($periodos as $p) {
            $suma = $promedios[$p->periodo_id]['suma'];
            $count = $promedios[$p->periodo_id]['contador'];
            $promedioPeriodoNum = ($count > 0) ? round($suma / $count, 1) : null;
            
            // CAMBIO: Formato a 1 decimal
            $filaPromedios[$p->periodo_id] = $esPreescolar 
                ? $this->calificacionService->getLetraCalificacion($promedioPeriodoNum)
                : (is_numeric($promedioPeriodoNum) ? number_format($promedioPeriodoNum, 1) : null);
            
            if (is_numeric($promedioPeriodoNum)) {
                $sumaFinal += $promedioPeriodoNum;
                $conteoFinal++;
            }
        }
        
        $promedioFinalNum = ($conteoFinal > 0) ? round($sumaFinal / $conteoFinal, 1) : null;

        // CAMBIO: Formato a 1 decimal
        $filaPromedios['promedio'] = $esPreescolar 
            ? $this->calificacionService->getLetraCalificacion($promedioFinalNum)
            : (is_numeric($promedioFinalNum) ? number_format($promedioFinalNum, 1) : null);

        return $filaPromedios;
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
                    $notaMostrada = $this->calificacionService->getLetraCalificacion($nota);
                    $notaNum = is_numeric($nota) ? $nota : null;
                } else {
                    $notaRedondeada = is_numeric($nota) ? round($nota, 1) : null;
                    // CAMBIO: Formato a 1 decimal forzado para display
                    $notaMostrada = is_numeric($notaRedondeada) ? number_format($notaRedondeada, 1) : null;
                    $notaNum = $notaRedondeada;
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
            
            // CAMBIO: Formato a 1 decimal
            $promedioMateriaShow = $esPreescolar 
                ? $this->calificacionService->getLetraCalificacion($promedioMateriaNum)
                : (is_numeric($promedioMateriaNum) ? number_format($promedioMateriaNum, 1) : null);

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

            // CAMBIO: Formato a 1 decimal
            $filaPromedios[$periodo->periodo_id] = $esPreescolar 
                ? $this->calificacionService->getLetraCalificacion($promedioPeriodo)
                : (is_numeric($promedioPeriodo) ? number_format($promedioPeriodo, 1) : null);

            if (is_numeric($promedioPeriodo)) {
                $sumaPromedioFinal += $promedioPeriodo;
                $countPromedioFinal++;
            }
        }
        
        $promFinalNum = ($countPromedioFinal > 0) ? round($sumaPromedioFinal / $countPromedioFinal, 1) : null;
        // CAMBIO: Formato a 1 decimal
        $filaPromedios['promedio'] = $esPreescolar 
            ? $this->calificacionService->getLetraCalificacion($promFinalNum)
            : (is_numeric($promFinalNum) ? number_format($promFinalNum, 1) : null);

        return [
            'titulo' => $tituloBloque,
            'criterios' => $filas,
            'promedios_bloque' => $filaPromedios,
            'promedios_bloque_numericos' => $promediosBloqueNumericos,
            'califs_para_promedio_final' => [] 
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
                    $notaMostrada = $this->calificacionService->getLetraCalificacion($nota);
                    $notaNum = is_numeric($nota) ? $nota : null;
                } else {
                    $notaRedondeada = is_numeric($nota) ? round($nota, 1) : null;
                    // CAMBIO: Formato a 1 decimal forzado
                    $notaMostrada = is_numeric($notaRedondeada) ? number_format($notaRedondeada, 1) : null;
                    $notaNum = $notaRedondeada;
                }

                $califsCriterio[$periodo->periodo_id] = $notaMostrada; 

                if (is_numeric($notaNum)) {
                    $sumaCriterio += $notaNum;
                    $countCriterio++;
                    
                    $promediosBloque[$periodo->periodo_id]['suma'] += $notaNum;
                    $promediosBloque[$periodo->periodo_id]['contador']++;
                    $califsParaPromedioFinal[$periodo->periodo_id][] = $esPreescolar ? $notaNum : $notaRedondeada; 
                }
            }

            $promedioCriterioNum = ($countCriterio > 0) ? round($sumaCriterio / $countCriterio, 1) : null;
            // CAMBIO: Formato a 1 decimal
            $promedioCriterioShow = $esPreescolar 
                ? $this->calificacionService->getLetraCalificacion($promedioCriterioNum)
                : (is_numeric($promedioCriterioNum) ? number_format($promedioCriterioNum, 1) : null);
            
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
            
            // CAMBIO: Formato a 1 decimal
            $filaPromedios[$periodo->periodo_id] = $esPreescolar 
                ? $this->calificacionService->getLetraCalificacion($promedioPeriodo)
                : (is_numeric($promedioPeriodo) ? number_format($promedioPeriodo, 1) : null);

            if (is_numeric($promedioPeriodo)) {
                $sumaPromedioFinal += $promedioPeriodo;
                $countPromedioFinal++;
            }
        }
        
        $promFinalNum = ($countPromedioFinal > 0) ? round($sumaPromedioFinal / $countPromedioFinal, 1) : null;
        // CAMBIO: Formato a 1 decimal
        $filaPromedios['promedio'] = $esPreescolar 
            ? $this->calificacionService->getLetraCalificacion($promFinalNum)
            : (is_numeric($promFinalNum) ? number_format($promFinalNum, 1) : null);

        return [
            'titulo' => $tituloBloque,
            'criterios' => $filasCriterios,
            'promedios_bloque' => $filaPromedios, 
            'califs_para_promedio_final' => $califsParaPromedioFinal,
        ];
    }

    private function calcularPromedioGeneralPreescolar(array $califsPorMateriaNumerica, Collection $periodos)
    {
        $promediosGeneral = [];
        $sumasPeriodo = [];
        $conteoPeriodo = [];
        $sumaFinal = 0;
        $conteoFinal = 0;

        foreach ($periodos as $p) {
            $sumasPeriodo[$p->periodo_id] = 0;
            $conteoPeriodo[$p->periodo_id] = 0;
        }

        foreach ($periodos as $p) {
            $califs = $califsPorMateriaNumerica[$p->periodo_id] ?? [];
            if (!empty($califs)) {
                $sumasPeriodo[$p->periodo_id] = array_sum($califs);
                $conteoPeriodo[$p->periodo_id] = count($califs);
            }
        }

        foreach ($periodos as $p) {
            $suma = $sumasPeriodo[$p->periodo_id];
            $count = $conteoPeriodo[$p->periodo_id];
            
            $promedioPeriodoNum = ($count > 0) ? round($suma / $count, 1) : null;
            
            $promediosGeneral[$p->periodo_id] = $this->calificacionService->getLetraCalificacion($promedioPeriodoNum);
            
            if (is_numeric($promedioPeriodoNum)) {
                $sumaFinal += $promedioPeriodoNum;
                $conteoFinal++;
            }
        }

        $promedioFinalNum = ($conteoFinal > 0) ? round($sumaFinal / $conteoFinal, 1) : null;
        $promediosGeneral['promedio'] = $this->calificacionService->getLetraCalificacion($promedioFinalNum);

        return $promediosGeneral;
    }

    private function calcularPromediosCombinados(Collection $periodos, array $bloquesDatos, bool $esPreescolar = false)
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
                    foreach ($califs as $calif) {
                        if (is_numeric($calif)) {
                             $califsPorPeriodo[$periodoId][] = $calif;
                        }
                    }
                }
            }
        }
        
        $sumaTotal = 0; $countTotal = 0;
        
        foreach ($periodos as $periodo) {
            $califs = $califsPorPeriodo[$periodo->periodo_id]; 
            $count = count($califs);
            $suma = array_sum($califs);
            
            $promedioNum = ($count > 0) ? round($suma / $count, 1) : null;
            
            // CAMBIO: Formato a 1 decimal
            $promediosFinales[$periodo->periodo_id] = $esPreescolar
                ? $this->calificacionService->getLetraCalificacion($promedioNum)
                : (is_numeric($promedioNum) ? number_format($promedioNum, 1) : null); 
            
            if (is_numeric($promedioNum)) {
                $sumaTotal += $promedioNum;
                $countTotal++;
            }
        }
        
        $promedioFinalNum = ($countTotal > 0) ? round($sumaTotal / $countTotal, 1) : null;
        
        // CAMBIO: Formato a 1 decimal
        $promediosFinales['promedio'] = $esPreescolar
            ? $this->calificacionService->getLetraCalificacion($promedioFinalNum)
            : (is_numeric($promedioFinalNum) ? number_format($promedioFinalNum, 1) : null);
            
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