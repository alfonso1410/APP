<?php

namespace App\Http\Controllers;

// --- Modelos ---
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\Materia;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\MateriaCriterio;
use App\Models\PonderacionCampo;
use App\Models\CicloEscolar;
use App\Models\Nivel;
use App\Models\User;

// --- Clases de Laravel ---
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF; // Fachada de PDF

class BoletaController extends Controller
{
    /**
     * Muestra la página de selectores para generar boletas.
     * (El nuevo método para la página 'admin/boletas')
     */
    public function index()
    {
        // 1. Cargar el ciclo escolar ACTIVO
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        // 2. Cargar niveles (para el primer dropdown)
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

    /**
     * Devuelve los alumnos de un grupo para un selector dinámico (JSON).
     */
    public function getAlumnosPorGrupo(Grupo $grupo)
    {
        // Cargamos solo los alumnos activos de este grupo
        $alumnos = $grupo->alumnosActuales()
                        ->orderBy('apellido_paterno')
                        ->orderBy('apellido_materno')
                        ->orderBy('nombres')
                        ->get(['alumnos.alumno_id as id', 'nombres', 'apellido_paterno', 'apellido_materno']);
        
        // Mapeamos para que el nombre esté completo
        $alumnos = $alumnos->map(function ($alumno) {
            return [
                'id' => $alumno->id,
                'nombre_completo' => "{$alumno->apellido_paterno} {$alumno->apellido_materno} {$alumno->nombres}"
            ];
        });

        return response()->json($alumnos);
    }

    /**
     * Genera la Boleta de Calificaciones final (PDF) para un alumno específico.
     * (Esta es la lógica compleja que ya definimos)
     */
    public function generarBoletaAlumno(Grupo $grupo, Alumno $alumno)
    {
        // --- 1. OBTENER CONTEXTO ---
        $grupo->load('grado.nivel', 'cicloEscolar');
        $grado = $grupo->grado;
        $ciclo = $grupo->cicloEscolar;

        // --- 2. VALIDAR PERMISOS (Seguridad) ---
        // (Ya no es necesario, movimos la ruta al middleware de Admin)

        // --- 3. OBTENER REGLAS DE CÁLCULO (PONDERACIONES) ---
        
        // A) Ponderaciones de Campos (ej. Lenguajes = 30%)
        $ponderacionesCampos = PonderacionCampo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
            ->where('grado_id', $grado->grado_id)
            ->pluck('ponderacion', 'campo_formativo_id');

        // B) Ponderaciones de Materias (ej. Español = 60% de Lenguajes)
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
            ->orderBy('cf.nombre')->orderBy('m.nombre')
            ->get();

        $camposFormativos = $estructura->groupBy('nombre_campo');

        // --- 4. OBTENER DATOS DEL ALUMNO ---
        $periodos = Periodo::where('ciclo_escolar_id', $ciclo->ciclo_escolar_id)
                           ->orderBy('fecha_inicio')
                           ->get();
        
        $materiaIds = $estructura->pluck('materia_id');

        // IDs de los criterios "Promedio" de esas materias
        $criteriosPromedioIds = MateriaCriterio::whereIn('materia_id', $materiaIds)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Promedio');
            })
             ->pluck('materia_criterio_id');

        // Calificaciones "PAS" del alumno
        $calificacionesPAS = Calificacion::where('alumno_id', $alumno->alumno_id)
            ->whereIn('periodo_id', $periodos->pluck('periodo_id'))
            ->whereIn('materia_criterio_id', $criteriosPromedioIds)
            ->get();
            
        // Mapeo de calificaciones PAS [materia_id_periodo_id => calificacion]
        $mapaCalificacionesPAS = [];
        $mapaMateriaCriterio = MateriaCriterio::whereIn('materia_criterio_id', $criteriosPromedioIds)
                                            ->pluck('materia_id', 'materia_criterio_id');
        
        foreach($calificacionesPAS as $cal) {
            $materiaId = $mapaMateriaCriterio->get($cal->materia_criterio_id);
            if ($materiaId) {
                $llave = $materiaId . '_' . $cal->periodo_id;
                $mapaCalificacionesPAS[$llave] = $cal->calificacion_obtenida;
            }
        }

        // --- 5. PROCESAR DATOS (Calcular "SEP") ---
        $boletaData = $this->procesarDatosBoleta(
            $camposFormativos, 
            $periodos, 
            $mapaCalificacionesPAS, 
            $ponderacionesCampos
        );

        // --- 6. PREPARAR DATOS FINALES PARA LA VISTA ---
        $data = [
            'alumno' => $alumno,
            'grupo' => $grupo,
            'ciclo' => $ciclo,
            'periodos' => $periodos,
            'dataCampos' => $boletaData['campos'],
            'promediosFinales' => $boletaData['promediosFinales']
        ];
        
        // --- 7. GENERAR PDF ---
        $pdf = PDF::loadView('reportes.boleta-alumno', $data, [], [
            'format' => 'Letter',
            'orientation' => 'P' // Portrait (Vertical)
        ]);

        return $pdf->stream('boleta-' . $alumno->apellido_paterno . '-' . $alumno->nombres . '.pdf');
    }

    /**
     * Función privada para procesar y calcular todos los promedios (PAS y SEP).
     * (Esta es la lógica de negocio principal)
     */
    
    /**
     * Función privada para procesar y calcular todos los promedios (PAS y SEP).
     */
    private function procesarDatosBoleta($camposFormativos, $periodos, $mapaCalificacionesPAS, $ponderacionesCampos)
    {
        $dataCampos = [];
        
        // Prepara el array para la fila final "PROMEDIO"
        $promediosFinales = [];
        foreach($periodos as $periodo) {
            $promediosFinales[$periodo->periodo_id] = ['suma_ponderada' => 0, 'total_ponderacion' => 0];
        }
        // ESTA LÓGICA DE 'promedio_final_sep' VA A CAMBIAR
        // $promediosFinales['promedio_final_sep'] = ['suma' => 0, 'contador' => 0];


        foreach ($camposFormativos as $nombreCampo => $materias) {
            $campoId = $materias->first()->campo_id;
            $ponderacionCampo = $ponderacionesCampos->get($campoId, 0) / 100.0;
            $dataMaterias = [];
            $promediosSEP_Campo = [];
            foreach($periodos as $periodo) {
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
                if(is_numeric($promedioPAS_Materia)) {
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
            
            // (Se elimina la acumulación para el promedio final SEP de la columna)
        }

        // 5. CALCULAR "PROMEDIO FINAL" (Fila "PROMEDIO")
        $promediosFinalesCalculados = [];
        $sumaPromedioFinal = 0;
        $contadorPromedioFinal = 0;
        
        foreach ($periodos as $periodo) {
            $totalPond = $promediosFinales[$periodo->periodo_id]['total_ponderacion'];
            $sumaPond = $promediosFinales[$periodo->periodo_id]['suma_ponderada'];
            
            $promedioFinalPond = ($totalPond > 0) ? round($sumaPond / $totalPond, 2) : null;
            $promediosFinalesCalculados[$periodo->periodo_id] = $promedioFinalPond;
            
            // --- INICIO DE CORRECCIÓN ---
            // Acumulamos este promedio de la fila para el cálculo final
            if (is_numeric($promedioFinalPond)) {
                $sumaPromedioFinal += $promedioFinalPond;
                $contadorPromedioFinal++;
            }
            // --- FIN DE CORRECCIÓN ---
        }
        
        // CORREGIDO: El promedio final SEP es el promedio de la fila "PROMEDIO"
        $promediosFinalesCalculados['promedio_final_sep'] = ($contadorPromedioFinal > 0)
            ? round($sumaPromedioFinal / $contadorPromedioFinal, 2)
            : null;

        return [
            'campos' => $dataCampos,
            'promediosFinales' => $promediosFinalesCalculados
        ];
    }
}