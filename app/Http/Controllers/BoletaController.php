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
use App\Models\CatalogoCriterio; // Asegúrate de que este modelo exista o se importe correctamente
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
        'Hábitos', // <--- Este es el de Español
        'English',
        'Reading Program',
        'Habits' // <--- Este es el de Inglés
    ];

    /*
    |--------------------------------------------------------------------------
    | Nombres de Materias que definen Bloques de Criterios
    |--------------------------------------------------------------------------
    | Estos nombres de MATERIAS (no campos) se usarán para generar
    | bloques especiales donde las FILAS son CRITERIOS.
    */
    private const BLOQUES_CRITERIOS_MAPA = [
        // 'Nombre de la Materia en BD' => 'Título del Bloque en PDF'
        'Programa Académico' => 'PROGRAMA ACADEMICO',
        'Programa Princeton' => 'PROGRAMA PRINCETON',
        'Lengua Extranjera (English Primaria)' => 'ENGLISH', // <- CONFIRMAR ESTE NOMBRE
        'Reading Program' => 'READING PROGRAM',
        'Habits' => 'HABITS', // <- Este es el de Inglés (barra verde)
        'Hábitos' => 'HÁBITOS', // <-- ¡¡AÑADIDO!! Este es el de Español
    ];

    /*
    |--------------------------------------------------------------------------
    | Nombres de Campos Formativos Estándar (Lógica SEP)
    |--------------------------------------------------------------------------
    */
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

    /**
     * Generador principal de la Boleta de Alumno (Refactorizado)
     * (Lógica 'Both/And' para English implementada)
     */
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

        // 1. OBTENER PONDERACIONES DE CAMPOS (Lógica SEP)
        $ponderacionesCampos = PonderacionCampo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->where('grado_id', $grado->grado_id)
            ->pluck('ponderacion', 'campo_formativo_id');

        // 2. OBTENER ESTRUCTURA CURRICULAR COMPLETA
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

        // 3. SEPARAR ESTRUCTURA: Campos SEP vs. Bloques de Criterios
        
        // Materias que se procesarán como bloques de criterios
        $nombresMateriaBloque = array_keys(self::BLOQUES_CRITERIOS_MAPA);
        
        // --- INICIO DE LA NUEVA LÓGICA ---
        // Queremos excluir todos los bloques (Programa Académico, Habits, Hábitos) de los campos SEP,
        // EXCEPTO 'Lengua Extranjera', que debe procesarse en AMBOS lados.
        $nombresMateriaBloqueExcluirDeSEP = array_diff(
            $nombresMateriaBloque, 
            ['Lengua Extranjera (English Primaria)'] // Asegúrate de que este nombre sea IDÉNTICO al de la constante
        );
        
        $estructuraCamposSEP = $estructuraCompleta->whereIn('nombre_campo', self::CAMPOS_FORMATIVOS_SEP)
                                                  ->whereNotIn('nombre_materia', $nombresMateriaBloqueExcluirDeSEP);
        // --- FIN DE LA NUEVA LÓGICA ---

        $estructuraBloquesCriterios = $estructuraCompleta->whereIn('nombre_materia', $nombresMateriaBloque);

        // 4. PROCESAR CAMPOS FORMATIVOS SEP (Lógica antigua PAS/SEP)
        $camposFormativosSEP_Agrupados = $estructuraCamposSEP->groupBy('nombre_campo');

        if ($orderList) {
            $camposFormativosSEP_Agrupados = $camposFormativosSEP_Agrupados->sortBy(function ($materias, $nombreCampo) use ($orderList) {
                $position = array_search($nombreCampo, $orderList);
                return ($position === false) ? 99 : $position;
            });
        } else {
            $camposFormativosSEP_Agrupados = $camposFormativosSEP_Agrupados->sortKeys();
        }

        // Obtenemos el mapa de calificaciones PAS (Promedio)
        $criteriosPromedioIds = MateriaCriterio::whereIn('materia_id', $estructuraCamposSEP->pluck('materia_id')) // Ahora incluirá English
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
        
        // Procesamos los datos de los campos SEP
        $boletaDataSEP = $this->procesarCamposSEP(
            $camposFormativosSEP_Agrupados,
            $periodos,
            $mapaCalificacionesPAS,
            $ponderacionesCampos
        );

        // 5. PROCESAR BLOQUES DE CRITERIOS (Lógica nueva)
        $datosBloquesCriterios = [];
        foreach ($estructuraBloquesCriterios as $materiaBloque) {
            $titulo = self::BLOQUES_CRITERIOS_MAPA[$materiaBloque->nombre_materia] ?? $materiaBloque->nombre_materia;
            $datosBloquesCriterios[$titulo] = $this->procesarBloqueCriterios(
                $alumno,
                $materiaBloque->materia_id,
                $periodos,
                $titulo
            );
        }

        // 6. PROCESAR ASISTENCIAS
        $datosAsistencias = $this->procesarAsistencias($alumno, $ciclo, $periodos);

        // 7. CALCULAR PROMEDIOS COMBINADOS
        // Ejemplo para Programa Académico + Programa Princeton
        $promediosCombinadosAcademico = $this->calcularPromediosCombinados(
            $periodos,
            [
                $datosBloquesCriterios['PROGRAMA ACADEMICO'] ?? null,
                $datosBloquesCriterios['PROGRAMA PRINCETON'] ?? null,
            ]
        );

        // Ejemplo para Reading Program + Habits (Inglés)
        $promediosCombinadosHabits = $this->calcularPromediosCombinados(
            $periodos,
            [
                $datosBloquesCriterios['READING PROGRAM'] ?? null,
                $datosBloquesCriterios['HABITS'] ?? null, // 'HABITS' (Inglés)
            ]
        );

        // --- ¡¡INICIO DE LA CORRECCIÓN!! OBTENER NOMBRES DE MAESTROS ---
        
        // Tabla 'users', FK 'maestro_titular_id', PK 'id'
        // Columnas de nombre: 'name', 'apellido_paterno', 'apellido_materno'

        // Get Spanish Teacher (Titular de Español)
        $titular = DB::table('grupo_titular as gt')
                    ->join('users as m', 'gt.maestro_titular_id', '=', 'm.id') // <-- Corregido
                    ->where('gt.grupo_id', $grupo->grupo_id)
                    ->where('gt.idioma', 'ESPAÑOL') 
                    ->select('m.name', 'm.apellido_paterno', 'm.apellido_materno') // <-- Corregido
                    ->first();
        
        $maestroEspanol = 'LIC. [MAESTRO ESPAÑOL NO ASIGNADO]';
        if ($titular) {
            $maestroEspanol = 'LIC. ' . strtoupper("{$titular->name} {$titular->apellido_paterno} {$titular->apellido_materno}"); // <-- Corregido
        }

        // Get English Teacher (Titular de Inglés)
        $teacher = DB::table('grupo_titular as gt') 
                    ->join('users as m', 'gt.maestro_titular_id', '=', 'm.id') // <-- Corregido
                    ->where('gt.grupo_id', $grupo->grupo_id)
                    ->where('gt.idioma', 'INGLES') 
                    ->select('m.name', 'm.apellido_paterno', 'm.apellido_materno') // <-- Corregido
                    ->first();

        $maestroIngles = 'LIC. [TEACHER NO ASIGNADO]';
        if ($teacher) {
            $maestroIngles = 'LIC. ' . strtoupper("{$teacher->name} {$teacher->apellido_paterno} {$teacher->apellido_materno}"); // <-- Corregido
        }
        // --- FIN OBTENER NOMBRES ---

        /*dd(
            $boletaDataSEP, 
            $datosBloquesCriterios, 
            $promediosCombinadosAcademico
        );*/
        // 8. ENSAMBLAR DATOS PARA LA VISTA
        $data = [
            'alumno' => $alumno,
            'grupo' => $grupo,
            'ciclo' => $ciclo,
            'periodos' => $periodos,
            'esPreescolar' => $esPreescolar,
            
            // Bloques de datos separados
            'dataCamposSEP' => $boletaDataSEP['campos'],
            'promediosFinalesSEP' => $boletaDataSEP['promediosFinales'],
            'datosBloques' => $datosBloquesCriterios, // Contiene 'PROGRAMA ACADEMICO', 'ENGLISH', 'HABITS', 'HÁBITOS'
            'datosAsistencias' => $datosAsistencias,

            // Promedios combinados
            'promediosCombinadosAcademico' => $promediosCombinadosAcademico,
            'promediosCombinadosHabits' => $promediosCombinadosHabits, // Promedio de 'HABITS' (Inglés)

            // Nombres de maestros
            'maestroEspanol' => $maestroEspanol,
            'maestroIngles' => $maestroIngles,
        ];

        // --- MODO DEPURACIÓN HTML ---
        // Renderiza la vista como una página web normal
        //return view('reportes.boleta-FINAL-PORFAVOR', $data);


        
        // --- MODO PDF ---
        $pdf = PDF::loadView('reportes.boleta-FINAL-PORFAVOR', $data, [
            'format' => 'Legal-L', // <--- Legal Size, Landscape Orientation
            'orientation' => 'L'
        ]);

        return $pdf->stream('boleta-' . $alumno->apellido_paterno . '-' . $alumno->nombres . '.pdf');
    }

    /**
     * Procesa los campos formativos estándar (lógica PAS/SEP).
     */
    /**
     * Procesa los campos formativos estándar (lógica PAS/SEP).
     * (Corregido el typo $periodo_id)
     */
    private function procesarCamposSEP($camposFormativos, $periodos, $mapaCalificacionesPAS, $ponderacionesCampos)
    {
        $dataCampos = [];
        $promediosFinales = [];
        
        $promediosFinalesCalculados = [];
        foreach ($periodos as $periodo) {
            $promediosFinales[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
            $promediosFinalesCalculados[$periodo->periodo_id] = null; // Inicializar
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
                $califsMateria_PAS = []; // <-- INICIALIZACIÓN
                $sumaMateriaPAS = 0;
                $countMateriaPAS = 0;
                $ponderacionMateria = $materia->ponderacion_materia / 100.0;

                foreach ($periodos as $periodo) {
                    $llave = $materia->materia_id . '_' . $periodo->periodo_id;
                    $notaPAS = $mapaCalificacionesPAS[$llave] ?? null;
                    $califsMateria_PAS[$periodo->periodo_id] = $notaPAS; // <-- OK

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

            $califsMateria_SEP = []; // <-- INICIALIZACIÓN
            foreach ($periodos as $periodo) {
                $totalPond = $promediosSEP_Campo[$periodo->periodo_id]['total_ponderacion'];
                
                // --- ¡¡INICIO DE LA CORRECCIÓN!! ---
                $sumaPond = $promediosSEP_Campo[$periodo->periodo_id]['suma_ponderada']; // <-- Corregido de $periodo_id a $periodo->periodo_id
                // --- FIN DE LA CORRECCIÓN ---

                $promedioSEP = ($totalPond > 0) ? round($sumaPond / $totalPond, 2) : null;
                $califsMateria_SEP[$periodo->periodo_id] = $promedioSEP; // <-- OK

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

        // $promediosFinalesCalculados ya está inicializado arriba
        $sumaPromedioFinal = 0;
        $contadorPromedioFinal = 0;

        foreach ($periodos as $periodo) {
            $totalPond = $promediosFinales[$periodo->periodo_id]['total_ponderacion'];
            $sumaPond = $promediosFinales[$periodo->periodo_id]['suma_ponderada'];

            $promedioFinalPond = ($totalPond > 0) ? round($sumaPond / $totalPond, 2) : null;
            $promediosFinalesCalculados[$periodo->periodo_id] = $promedioFinalPond; // <-- OK

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

    /**
     * [NUEVO] Procesa un bloque donde las filas son criterios de una materia.
     * (Corregido para excluir "Faltas" del promedio)
     */
    private function procesarBloqueCriterios(Alumno $alumno, int $materiaId, Collection $periodos, string $tituloBloque)
    {
        // 1. Obtener los criterios de esta materia, excluyendo "Promedio" y "Faltas"
        $criterios = MateriaCriterio::with('catalogoCriterio')
            ->where('materia_id', $materiaId)
            ->whereHas('catalogoCriterio', function ($query) {
                // --- ¡CORRECCIÓN AQUÍ! ---
                // Excluimos 'Promedio' (que es un cálculo) y 'Faltas' (que es informativo)
                $query->whereNotIn('nombre', ['Promedio', 'Faltas']);
            })
            ->get()
            ->sortBy(function($mc) {
                return $mc->catalogoCriterio->nombre ?? 'ZZZ'; 
            });
            
        $criterioIds = $criterios->pluck('materia_criterio_id');

        // 2. Obtener calificaciones...
        $calificaciones = Calificacion::where('alumno_id', $alumno->alumno_id)
            ->whereIn('periodo_id', $periodos->pluck('periodo_id'))
            ->whereIn('materia_criterio_id', $criterioIds)
            ->get();

        // 3. Mapear calificaciones...
        $mapaCalificaciones = [];
        foreach ($calificaciones as $cal) {
            $llave = $cal->materia_criterio_id . '_' . $cal->periodo_id;
            $mapaCalificaciones[$llave] = $cal->calificacion_obtenida;
        }

        // 4. Procesar datos para la vista
        $filasCriterios = [];
        $promediosBloque = []; 
        $califsParaPromedioFinal = [];
        $filaPromedios = []; 

        // Inicializar arrays para cada periodo
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

        // 5. Calcular la fila de "PROMEDIO" del bloque
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

    /**
     * [NUEVO] Calcula los promedios finales combinados de varios bloques.
     */
    private function calcularPromediosCombinados(Collection $periodos, array $bloquesDatos)
    {
        $califsPorPeriodo = [];
        $promediosFinales = []; 

        // *** INICIO DE LA CORRECCIÓN ***
        foreach ($periodos as $periodo) {
            $califsPorPeriodo[$periodo->periodo_id] = [];
            $promediosFinales[$periodo->periodo_id] = null; // <-- AÑADIDO
        }
        // *** FIN DE LA CORRECCIÓN ***
        
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
            
            $promediosFinales[$periodo->periodo_id] = $promedio; // <-- OK
            
            if (is_numeric($promedio)) {
                $sumaTotal += $promedio;
                $countTotal++;
            }
        }

        $promediosFinales['promedio'] = ($countTotal > 0) ? round($sumaTotal / $countTotal, 1) : null;

        return $promediosFinales;
    }


    /**
     * [NUEVO] Procesa las asistencias del alumno.
     * (Corregido para 'ESPAÑOL' con tilde y 'INGLES' sin tilde)
     */
    private function procesarAsistencias(Alumno $alumno, CicloEscolar $ciclo, Collection $periodos)
    {
        $registros = DB::table('registro_asistencia')
            ->where('alumno_id', $alumno->alumno_id)
            ->whereIn('periodo_id', $periodos->pluck('periodo_id'))
            ->select('periodo_id', 'idioma', 'tipo_asistencia')
            ->get();

        $datos = []; // <-- Array de periodos
        $totales = [
            'ESP_asistencias' => 0, 'ENG_asistencias' => 0, 'TOTAL_asistencias' => 0,
            'ESP_retardos' => 0, 'ENG_retardos' => 0, 'TOTAL_retardos' => 0,
            'ESP_inasistencias' => 0, 'ENG_inasistencias' => 0, 'TOTAL_inasistencias' => 0,
        ];

        // Aseguramos que CADA periodo exista en el array de datos
        foreach ($periodos as $periodo) {
            $datos[$periodo->periodo_id] = [
                'ESP_asistencias' => 0, 'ENG_asistencias' => 0, 'TOTAL_asistencias' => 0,
                'ESP_retardos' => 0, 'ENG_retardos' => 0, 'TOTAL_retardos' => 0,
                'ESP_inasistencias' => 0, 'ENG_inasistencias' => 0, 'TOTAL_inasistencias' => 0,
            ];
        }

        // Este bucle ahora llena los valores
        foreach ($periodos as $periodo) {
            $registrosPeriodo = $registros->where('periodo_id', $periodo->periodo_id);

            // --- ¡INICIO DE LA CORRECCIÓN! ---
            // Contar registros por tipo (coincidiendo con la BD)
            $espAsist = $registrosPeriodo->where('idioma', 'ESPAÑOL')->where('tipo_asistencia', 'PRESENTE')->count(); // CON tilde
            $engAsist = $registrosPeriodo->where('idioma', 'INGLES')->where('tipo_asistencia', 'PRESENTE')->count(); // SIN tilde
            
            $espRetardo = $registrosPeriodo->where('idioma', 'ESPAÑOL')->where('tipo_asistencia', 'RETARDO')->count(); // CON tilde
            $engRetardo = $registrosPeriodo->where('idioma', 'INGLES')->where('tipo_asistencia', 'RETARDO')->count(); // SIN tilde
            
            $espFalta = $registrosPeriodo->where('idioma', 'ESPAÑOL')->where('tipo_asistencia', 'FALTA')->count(); // CON tilde
            $engFalta = $registrosPeriodo->where('idioma', 'INGLES')->where('tipo_asistencia', 'FALTA')->count(); // SIN tilde
            // --- FIN DE LA CORRECCIÓN ---

            // Llenar los datos
            $datos[$periodo->periodo_id]['ESP_asistencias'] = $espAsist;
            $datos[$periodo->periodo_id]['ENG_asistencias'] = $engAsist;
            $datos[$periodo->periodo_id]['TOTAL_asistencias'] = $espAsist + $engAsist;

            $datos[$periodo->periodo_id]['ESP_retardos'] = $espRetardo;
            $datos[$periodo->periodo_id]['ENG_retardos'] = $engRetardo;
            $datos[$periodo->periodo_id]['TOTAL_retardos'] = $espRetardo + $engRetardo;
            
            $datos[$periodo->periodo_id]['ESP_inasistencias'] = $espFalta;
            $datos[$periodo->periodo_id]['ENG_inasistencias'] = $engFalta;
            $datos[$periodo->periodo_id]['TOTAL_inasistencias'] = $espFalta + $engFalta;

            // Sumar a totales
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