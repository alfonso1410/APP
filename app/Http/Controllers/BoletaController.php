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

    // MAPA PARA BLOQUES QUE SON MATERIAS CON CRITERIOS (Filas = Criterios)
    // NOTA: Quitamos 'ENGLISH' de aquí porque ahora lo procesaremos como MATERIAS
    private const BLOQUES_CRITERIOS_MAPA = [
        'Programa Académico' => 'PROGRAMA ACADEMICO',
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

        $periodos = Periodo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->orderBy('fecha_inicio')
            ->get();

        // 1. PONDERACIONES
        $ponderacionesCampos = PonderacionCampo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->where('grado_id', $grado->grado_id)
            ->pluck('ponderacion', 'campo_formativo_id');

        // 2. ESTRUCTURA CURRICULAR COMPLETA
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
        
        // A. Estructura SEP (excluyendo la materia "Lengua Extranjera" si existiera como materia simple, 
        //    aunque la usaremos como placeholder para inyectar el promedio)
        $nombresMateriaBloque = array_keys(self::BLOQUES_CRITERIOS_MAPA);
        // Agregamos 'Lengua Extranjera' a la lista de exclusión temporal para no procesarla doble si fuera un bloque
        // Pero aquí queremos que SEP la procese normal, solo que inyectaremos su nota.
        $estructuraCamposSEP = $estructuraCompleta->whereIn('nombre_campo', self::CAMPOS_FORMATIVOS_SEP)
                                                  ->whereNotIn('nombre_materia', $nombresMateriaBloque);

        // B. Estructura Princeton (Campo Formativo -> Materias)
        $estructuraPrinceton = $estructuraCompleta->where('nombre_campo', 'Programa Princeton');

        // C. Estructura English (Campo Formativo -> Materias: Spelling, Grammar, etc.)
        // Buscamos el campo formativo que se llame "English"
        $estructuraEnglish = $estructuraCompleta->where('nombre_campo', 'English');

        // D. Estructura Bloques (Materias únicas con Criterios)
        $estructuraBloquesCriterios = $estructuraCompleta->whereIn('nombre_materia', $nombresMateriaBloque);

        // 4. OBTENCIÓN DE CALIFICACIONES BASE (Promedios directos de Materias)
        // Necesitamos las calificaciones "finales" (criterio Promedio) de TODAS las materias involucradas:
        // SEP + Princeton + English (Spelling, etc.)
        $idsMateriasPromedio = $estructuraCamposSEP->pluck('materia_id')
                                ->merge($estructuraPrinceton->pluck('materia_id'))
                                ->merge($estructuraEnglish->pluck('materia_id')); // <-- AGREGADO ENGLISH

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
        //  PROCESAR BLOQUE ENGLISH (Como Materias)
        // ==================================================================================
        // Aquí procesamos Spelling, Grammar, etc. y calculamos el promedio vertical del campo.
        
        $datosEnglish = null;
        if ($estructuraEnglish->isNotEmpty()) {
            // Reutilizamos una lógica similar a Princeton: procesar como lista de materias
            // pero adaptada para devolver la estructura que la vista espera para los bloques laterales
            $datosEnglish = $this->procesarBloqueMaterias(
                $estructuraEnglish,
                $periodos,
                $mapaCalificacionesPAS,
                'ENGLISH'
            );

            // INYECTAR PROMEDIO DE INGLÉS EN LA TABLA SEP ("Lengua Extranjera")
            // Buscamos la materia "Lengua Extranjera" dentro de la estructura SEP
            $materiaLenguaExtranjera = $estructuraCamposSEP->first(function($item) {
                // Buscamos por nombre aproximado o exacto
                return str_contains(strtoupper($item->nombre_materia), 'LENGUA EXTRANJERA') 
                    || str_contains(strtoupper($item->nombre_materia), 'INGLÉS')
                    || str_contains(strtoupper($item->nombre_materia), 'ENGLISH');
            });

            if ($materiaLenguaExtranjera && isset($datosEnglish['promedios_bloque'])) {
                foreach ($periodos as $periodo) {
                    $promedio = $datosEnglish['promedios_bloque'][$periodo->periodo_id] ?? null;
                    if (is_numeric($promedio)) {
                        // Inyectamos en el mapa principal usando el ID de "Lengua Extranjera"
                        $llave = $materiaLenguaExtranjera->materia_id . '_' . $periodo->periodo_id;
                        $mapaCalificacionesPAS[$llave] = $promedio;
                    }
                }
            }
        }
        // ==================================================================================


        // 5. PROCESAR CAMPOS SEP (Ahora el mapa ya tiene la calificación de Lengua Extranjera)
        $camposFormativosSEP_Agrupados = $estructuraCamposSEP->groupBy('nombre_campo');

        if ($orderList) {
            $camposFormativosSEP_Agrupados = $camposFormativosSEP_Agrupados->sortBy(function ($materias, $nombreCampo) use ($orderList) {
                $position = array_search($nombreCampo, $orderList);
                return ($position === false) ? 99 : $position;
            });
        } else {
            $camposFormativosSEP_Agrupados = $camposFormativosSEP_Agrupados->sortKeys();
        }

        $boletaDataSEP = $this->procesarCamposSEP(
            $camposFormativosSEP_Agrupados,
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos
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
                if (is_numeric($calif)) {
                    $sumasSEP[$p->periodo_id] += $calif;
                    $contadoresSEP[$p->periodo_id]++;
                }
            }
            $califFinal = $campoData['promedio_final_sep'] ?? null;
            if (is_numeric($califFinal)) {
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


        // 7. PROCESAR PRINCETON (Campo Formativo -> Materias)
        $boletaDataPrinceton = $this->procesarCamposSEP(
            $estructuraPrinceton->groupBy('nombre_campo'),
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos
        );

        // Extraer datos de Princeton para promedio combinado
        $datosPrincetonParaCombinado = ['califs_para_promedio_final' => []];
        foreach ($periodos as $p) $datosPrincetonParaCombinado['califs_para_promedio_final'][$p->periodo_id] = [];

        foreach ($estructuraPrinceton as $matPrinc) {
            foreach ($periodos as $p) {
                $llave = $matPrinc->materia_id . '_' . $p->periodo_id;
                if (isset($mapaCalificacionesPAS[$llave]) && is_numeric($mapaCalificacionesPAS[$llave])) {
                    $datosPrincetonParaCombinado['califs_para_promedio_final'][$p->periodo_id][] = $mapaCalificacionesPAS[$llave];
                }
            }
        }

        // 8. PROCESAR BLOQUES DE CRITERIOS (RESTANTES)
        $datosBloquesCriterios = [];
        
        // Agregamos ENGLISH manualmente al array de bloques para que la vista lo pinte
        if ($datosEnglish) {
            $datosBloquesCriterios['ENGLISH'] = $datosEnglish;
        }

        foreach ($estructuraBloquesCriterios as $materiaBloque) {
            $titulo = self::BLOQUES_CRITERIOS_MAPA[$materiaBloque->nombre_materia] ?? $materiaBloque->nombre_materia;
            
            $datosBloquesCriterios[$titulo] = $this->procesarBloqueCriterios(
                $alumno,
                $materiaBloque->materia_id,
                $periodos,
                $titulo
            );
        }

        // 9. ASISTENCIAS
        $datosAsistencias = $this->procesarAsistencias($alumno, $ciclo, $periodos);

        // 10. PROMEDIOS COMBINADOS
        $promediosCombinadosAcademico = $this->calcularPromediosCombinados(
            $periodos,
            [
                $datosBloquesCriterios['PROGRAMA ACADEMICO'] ?? null,
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
        
        $maestroEspanol = 'LIC. [MAESTRO ESPAÑOL NO ASIGNADO]';
        if ($titular) {
            $maestroEspanol = 'LIC. ' . strtoupper("{$titular->name} {$titular->apellido_paterno} {$titular->apellido_materno}");
        }

        $teacher = DB::table('grupo_titular as gt') 
                    ->join('users as m', 'gt.maestro_titular_id', '=', 'm.id') 
                    ->where('gt.grupo_id', $grupo->grupo_id)
                    ->where('gt.idioma', 'INGLES') 
                    ->select('m.name', 'm.apellido_paterno', 'm.apellido_materno') 
                    ->first();

        $maestroIngles = 'LIC. [TEACHER NO ASIGNADO]';
        if ($teacher) {
            $maestroIngles = 'LIC. ' . strtoupper("{$teacher->name} {$teacher->apellido_paterno} {$teacher->apellido_materno}");
        }

        $data = [
            'alumno' => $alumno,
            'grupo' => $grupo,
            'ciclo' => $ciclo,
            'periodos' => $periodos,
            'esPreescolar' => $esPreescolar,
            
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

    /**
     * Función especial para procesar un grupo de materias (ej. English)
     * y devolverlo con la estructura que espera la vista de Bloques (criterios/filas).
     */
    private function procesarBloqueMaterias($estructuraMaterias, $periodos, $mapaCalificacionesPAS, $tituloBloque)
    {
        $filas = [];
        $promediosBloque = [];
        $califsParaPromedioFinal = []; // Aunque no se use, lo dejamos por compatibilidad

        // Inicializar promedios
        foreach ($periodos as $periodo) {
            $promediosBloque[$periodo->periodo_id] = ['suma' => 0, 'contador' => 0];
        }

        foreach ($estructuraMaterias as $materia) {
            $califsMateria = [];
            
            foreach ($periodos as $periodo) {
                $llave = $materia->materia_id . '_' . $periodo->periodo_id;
                $nota = $mapaCalificacionesPAS[$llave] ?? null;
                $notaFormateada = is_numeric($nota) ? round($nota, 1) : null;
                
                $califsMateria[$periodo->periodo_id] = $notaFormateada;

                if (is_numeric($notaFormateada)) {
                    $promediosBloque[$periodo->periodo_id]['suma'] += $notaFormateada;
                    $promediosBloque[$periodo->periodo_id]['contador']++;
                }
            }

            // Calcular promedio final de la materia
            // Nota: Aquí asumimos que el promedio horizontal de la materia es el promedio de sus trimestres
            // O podríamos buscar si ya existe un promedio final calculado en BD, pero usualmente es aritmético.
            $sumaMat = 0; 
            $countMat = 0;
            foreach($califsMateria as $c) {
                if(is_numeric($c)) { $sumaMat += $c; $countMat++; }
            }
            $promedioMateria = ($countMat > 0) ? round($sumaMat / $countMat, 1) : null;

            // Mapeamos a la estructura que espera la vista: 
            // 'nombre' (nombre materia), 'calificaciones' (array por periodo), 'promedio' (final materia)
            $filas[] = [
                'nombre' => $materia->nombre_materia,
                'calificaciones' => $califsMateria,
                'promedio' => $promedioMateria
            ];
        }

        // Calcular la fila final de promedios del bloque (verticales)
        $filaPromedios = [];
        $sumaPromedioFinal = 0;
        $countPromedioFinal = 0;

        foreach ($periodos as $periodo) {
            $suma = $promediosBloque[$periodo->periodo_id]['suma'];
            $count = $promediosBloque[$periodo->periodo_id]['contador'];
            $promedioPeriodo = ($count > 0) ? round($suma / $count, 1) : null;
            
            $filaPromedios[$periodo->periodo_id] = $promedioPeriodo;

            if (is_numeric($promedioPeriodo)) {
                $sumaPromedioFinal += $promedioPeriodo;
                $countPromedioFinal++;
            }
        }
        $filaPromedios['promedio'] = ($countPromedioFinal > 0) ? round($sumaPromedioFinal / $countPromedioFinal, 1) : null;

        return [
            'titulo' => $tituloBloque,
            'criterios' => $filas, // La vista itera sobre 'criterios', aquí ponemos las materias
            'promedios_bloque' => $filaPromedios,
            'califs_para_promedio_final' => [] 
        ];
    }

    private function procesarCamposSEP($camposFormativos, $periodos, $mapaCalificacionesPAS, $ponderacionesCampos)
    {
        $dataCampos = [];
        $promediosFinales = [];
        $promediosFinalesCalculados = [];

        foreach ($periodos as $periodo) {
            $promediosFinales[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
            $promediosFinalesCalculados[$periodo->periodo_id] = null; 
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

    private function procesarBloqueCriterios(Alumno $alumno, int $materiaId, Collection $periodos, string $tituloBloque)
    {
        $criterios = MateriaCriterio::with('catalogoCriterio')
            ->where('materia_id', $materiaId)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->whereNotIn('nombre', ['Promedio', 'Faltas']);
            })
            ->get()
            ->sortBy(function($mc) {
                return $mc->catalogoCriterio->nombre ?? 'ZZZ'; 
            });
            
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
                $notaFormateada = is_numeric($nota) ? round($nota, 1) : null;
                $califsCriterio[$periodo->periodo_id] = $notaFormateada; 

                if (is_numeric($notaFormateada)) {
                    $sumaCriterio += $notaFormateada;
                    $countCriterio++;
                    $promediosBloque[$periodo->periodo_id]['suma'] += $notaFormateada;
                    $promediosBloque[$periodo->periodo_id]['contador']++;
                    $califsParaPromedioFinal[$periodo->periodo_id][] = $notaFormateada;
                }
            }

            $promedioCriterio = ($countCriterio > 0) ? round($sumaCriterio / $countCriterio, 1) : null;
            
            $filasCriterios[] = [
                'nombre' => $criterio->catalogoCriterio->nombre ?? 'Criterio No Encontrado',
                'calificaciones' => $califsCriterio,
                'promedio' => $promedioCriterio,
            ];
        }

        $sumaPromedioFinal = 0;
        $countPromedioFinal = 0;

        foreach ($periodos as $periodo) {
            $suma = $promediosBloque[$periodo->periodo_id]['suma'];
            $count = $promediosBloque[$periodo->periodo_id]['contador'];
            $promedioPeriodo = ($count > 0) ? round($suma / $count, 1) : null;
            $filaPromedios[$periodo->periodo_id] = $promedioPeriodo; 

            if (is_numeric($promedioPeriodo)) {
                $sumaPromedioFinal += $promedioPeriodo;
                $countPromedioFinal++;
            }
        }

        $filaPromedios['promedio'] = ($countPromedioFinal > 0) ? round($sumaPromedioFinal / $countPromedioFinal, 1) : null;

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
            if (empty($bloque) || empty($bloque['califs_para_promedio_final'])) {
                continue;
            }
            foreach ($bloque['califs_para_promedio_final'] as $periodoId => $califs) {
                if (isset($califsPorPeriodo[$periodoId])) {
                    $califsPorPeriodo[$periodoId] = array_merge($califsPorPeriodo[$periodoId], $califs);
                }
            }
        }

        $sumaTotal = 0;
        $countTotal = 0;

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