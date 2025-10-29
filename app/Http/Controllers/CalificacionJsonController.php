<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\MateriaCriterio;
use App\Models\CatalogoCriterio;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Periodo; // Necesario para las fechas
use App\Models\RegistroAsistencia; // <-- ASEGÚRATE DE TENER ESTA LÍNEA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CalificacionJsonController extends Controller
{
    /**
     * Devuelve los grupos activos de un grado.
     * (Versión explícita anti-errores)
     */
      public function getGradosPorNivel(Nivel $nivel)
    {
        // Asumo que Nivel tiene 'nivel_id' como PK
        $grados = Grado::where('nivel_id', $nivel->nivel_id)
                       ->where('tipo_grado', 'REGULAR') // Solo grados académicos
                       ->orderBy('orden') // Ordenar por 1ro, 2do, 3ro
                       ->get(['grado_id as id', 'nombre']);
        
        // Mapear para garantizar que cada objeto tenga 'id' y 'nombre'
        $grados = $grados->map(function ($grado) {
            return [
                'id' => $grado->id ?? null, // Asegura que siempre haya un valor para 'id'
                'nombre' => $grado->nombre ?? 'Nombre no disponible',
            ];
        });

        return response()->json($grados);
    }

  public function getGradosExtracurriculares()
{
    $grados = Grado::where('tipo_grado', 'EXTRA')
                   ->orderBy('nombre')
                   ->get(['grado_id as id', 'nombre']);
    
    // Mapear para garantizar que cada objeto tenga 'id' y 'nombre'
    $grados = $grados->map(function ($grado) {
        return [
            'id' => $grado->id ?? null, // Asegura que siempre haya un valor para 'id'
            'nombre' => $grado->nombre ?? 'Nombre no disponible',
        ];
    });

    return response()->json($grados);
}

    public function getGrupos(Grado $grado)
    {
        $grupos = Grupo::where('grado_id', $grado->grado_id)
                       ->where('estado', 'ACTIVO')
                       ->orderBy('nombre_grupo')
                       ->get(['grupo_id as id', 'nombre_grupo']);
        
        // Mapear para garantizar que cada objeto tenga 'id' y 'nombre_grupo'
        $grupos = $grupos->map(function ($grupo) {
            return [
                'id' => $grupo->id ?? null, // Asegura que siempre haya un valor para 'id'
                'nombre_grupo' => $grupo->nombre_grupo ?? 'Nombre no disponible',
            ];
        });

        return response()->json($grupos);
    }

    /**
     * Devuelve las materias de un grado.
     * (Versión explícita anti-errores)
     */
    
   public function getMaterias(Grado $grado)
{
    // --- INICIO DE LA MODIFICACIÓN ---
    if ($grado->tipo_grado == 'REGULAR') {
        
        // Lógica actual para grados regulares
        $materia_ids = DB::table('estructura_curricular')
                         ->where('grado_id', $grado->grado_id)
                         ->distinct()
                         ->pluck('materia_id');

        $materias = Materia::whereIn('materia_id', $materia_ids)
                           ->orderBy('nombre')
                           ->get(['materia_id as id', 'nombre']);

        // Mapear para grados regulares
        $materias = $materias->map(function ($materia) {
            return [
                'id' => $materia->id ?? null,
                'nombre' => $materia->nombre ?? 'Nombre no disponible',
            ];
        });
    
    } else { // Si es 'EXTRA'
        
        // Lógica NUEVA para grados extracurriculares
        $materias = Materia::where('tipo', 'EXTRA')
                           ->orderBy('nombre')
                           ->get(['materia_id as id', 'nombre']);

        // Mapear para grados extracurriculares (¡ESTO ES LO NUEVO!)
        $materias = $materias->map(function ($materia) {
            return [
                'id' => $materia->id ?? null,
                'nombre' => $materia->nombre ?? 'Nombre no disponible',
            ];
        });
    }
    
    // --- FIN DE LA MODIFICACIÓN ---

    return response()->json($materias);
}
    /**
     * Devuelve la tabla de alumnos y criterios para la captura.
     * (Esta es la versión que ya corregimos)
     */
  public function getTablaCalificaciones(Request $request)
    {
        $request->validate([
            'grupo_id' => 'required|integer|exists:grupos,grupo_id', 
            'materia_id' => 'required|integer|exists:materias,materia_id',
            'periodo_id' => 'required|integer|exists:periodos,periodo_id',
        ]);

        // --- CORRECCIÓN 1: Obtener el modelo Periodo ---
        $periodo = Periodo::find($request->periodo_id);
        if (!$periodo) {
            return response()->json(['error' => 'Periodo no encontrado'], 404);
        }

        $grupo = Grupo::find($request->grupo_id);
        if (!$grupo) {
            return response()->json(['error' => 'Grupo no encontrado'], 404);
        }

        $alumnos = $grupo->alumnosActuales() 
                         ->where('estado_alumno', 'ACTIVO') 
                         ->orderBy('apellido_paterno')
                         ->orderBy('apellido_materno')
                         ->orderBy('nombres')
                         ->get(['alumnos.alumno_id as id', 'nombres', 'apellido_paterno', 'apellido_materno']);
        
        // 1. Obtener criterios CON sus datos de ponderación
        $materiaCriterios = MateriaCriterio::where('materia_id', $request->materia_id)
                                            ->with('catalogoCriterio') 
                                            ->orderBy('materia_criterio_id')
                                            ->get(); // Traemos la colección completa

        // 2. Preparar los criterios
        $criterioPromedioId = null;
        $criterioFaltasId = null; // <-- CORRECCIÓN 2: Inicializar variable
        $criteriosParaPromediar = []; // [criterio_id => ponderacion]
        
        $criterios = $materiaCriterios->map(function ($mc) use (&$criterioPromedioId, &$criterioFaltasId, &$criteriosParaPromediar) { // <-- CORRECCIÓN 2: Agregar &$criterioFaltasId
            
            // Asumo que tu modelo CatalogoCriterio tiene 'nombre'
            $nombreCriterio = $mc->catalogoCriterio->nombre ?? 'Criterio s/n';
            $esPromedio = (strcasecmp($nombreCriterio, 'Promedio') == 0); // Compara ignorando mayúsculas
            $esFaltas = (strcasecmp($nombreCriterio, 'Faltas') == 0); // <-- CORRECCIÓN 2: Detectar "Faltas"

            // Guardamos los datos para el cálculo
            if ($mc->incluido_en_promedio) {
                // Si la ponderación es 0 o nula, trátala como 1 (promedio simple)
                $criteriosParaPromediar[$mc->materia_criterio_id] = $mc->ponderacion > 0 ? $mc->ponderacion : 1;
            }
            
            if ($esPromedio) {
                $criterioPromedioId = $mc->materia_criterio_id;
            }
             // <-- CORRECCIÓN 2: Asignar ID de "Faltas"
             if ($esFaltas) {
                 $criterioFaltasId = $mc->materia_criterio_id;
             }

            return [
                'id' => $mc->materia_criterio_id, 
                'nombre_criterio' => $nombreCriterio,
                // Le pasamos esta info al frontend para que pueda deshabilitar el input
                'es_promedio' => $esPromedio,
                'es_faltas' => $esFaltas // <-- CORRECCIÓN 2: Agregar bandera al JSON
            ];
        });
        // ==========================================================
        // == INICIO DE CORRECCIÓN: Reordenar "Promedio" al final ==
        // ==========================================================

        // 1. Particionamos la colección.
        // $promedios tendrá los que 'es_promedio' = true
        // $otrosCriterios tendrá los que 'es_promedio' = false
        list($promedios, $otrosCriterios) = $criterios->partition(function ($criterio) {
            return $criterio['es_promedio'];
        });
        // 2. Unimos las dos colecciones, poniendo los 'otros' primero
        // y el 'promedio' al final.
        // Usamos values() para resetear las keys y asegurar que sea un array JSON.
        $criteriosOrdenados = $otrosCriterios->merge($promedios)->values();
        // 3. Obtener calificaciones existentes
        // --- CORRECCIÓN 3: Filtrar por $request->periodo_id y excluir 'Faltas' ---
        $idsACalcular = [];
        if ($criterioPromedioId) $idsACalcular[] = $criterioPromedioId;
        if ($criterioFaltasId) $idsACalcular[] = $criterioFaltasId;

        $calificacionesExistentes = Calificacion::where('periodo_id', $request->periodo_id) // <-- CORRECCIÓN 3: Usar $request->periodo_id
            ->whereIn('alumno_id', $alumnos->pluck('id'))
            ->whereIn('materia_criterio_id', $materiaCriterios->pluck('materia_criterio_id'))
            // Excluimos los criterios calculados si existen
            ->when(!empty($idsACalcular), function ($query) use ($idsACalcular) {
                 return $query->whereNotIn('materia_criterio_id', $idsACalcular); // <-- CORRECCIÓN 3: Ahora $idsACalcular está definido
            })
            ->get();
        // 4. Mapear calificaciones Y CALCULAR PROMEDIOS
       $mapaCalificaciones = [];
        $califsPorAlumno = $calificacionesExistentes->groupBy('alumno_id');
        $promediosIndividuales = [];

        foreach ($alumnos as $alumno) {
            $mapaCalificaciones[$alumno->id] = []; // Inicializa el mapa para el alumno
            $sumaPonderada = 0;
            $sumaPonderaciones = 0;
            
            // --- CORRECCIÓN 4: Calcular Faltas ---
            if ($criterioFaltasId) { // <-- CORRECCIÓN 4: Si existe el criterio "Faltas"
                // Asumiendo que agregaste 'periodo_id' a 'registro_asistencia'
                $totalFaltas = RegistroAsistencia::where('alumno_id', $alumno->id)
                    // ->where('grupo_id', $grupo->grupo_id) // Opcional: Filtrar por grupo si es necesario
                    ->where('periodo_id', $periodo->periodo_id) // <-- CORRECCIÓN 4: Filtrar por periodo_id
                    ->where('tipo_asistencia', 'FALTA')
                    // ->whereBetween('fecha', [$periodo->fecha_inicio, $periodo->fecha_fin]) // <-- No necesario si ya tienes periodo_id
                    ->count();
                $mapaCalificaciones[$alumno->id][$criterioFaltasId] = $totalFaltas;

                // Si Faltas SÍ se incluye en el promedio, lo añadimos aquí
                 // NOTA: Si 'Faltas' NO debe influir en el promedio ponderado, COMENTA este bloque.
                 // if (isset($criteriosParaPromediar[$criterioFaltasId])) {
                 //     $ponderacionFaltas = $criteriosParaPromediar[$criterioFaltasId];
                 //     // NOTA: Aquí asumo que la "calificación" de Faltas es el número contado.
                 //     // Si necesitas convertirlo (ej. 0 faltas=10, 1 falta=9...), hazlo aquí.
                 //     $calificacionFaltas = $totalFaltas; // O la conversión que necesites
                 //     $sumaPonderada += $calificacionFaltas * $ponderacionFaltas;
                 //     $sumaPonderaciones += $ponderacionFaltas;
                 // }
            }

            // Luego, procesamos las calificaciones GUARDADAS
            if ($califsPorAlumno->has($alumno->id)) {
                foreach ($califsPorAlumno[$alumno->id] as $cal) {
                    $criterioId = $cal->materia_criterio_id;
                    $mapaCalificaciones[$alumno->id][$criterioId] = $cal->calificacion_obtenida;

                    // Acumular para el promedio (solo si no es Faltas, ya lo contamos o se ignora)
                    if (isset($criteriosParaPromediar[$criterioId]) && $criterioId != $criterioFaltasId) { // <-- CORRECCIÓN 4: Excluir Faltas del promedio
                        $ponderacion = $criteriosParaPromediar[$criterioId];
                        $sumaPonderada += $cal->calificacion_obtenida * $ponderacion;
                        $sumaPonderaciones += $ponderacion;
                    }
                }
            }

            // Finalmente, calculamos y asignamos el PROMEDIO
            if ($criterioPromedioId && $sumaPonderaciones > 0) {
                $promedioCalculado = $sumaPonderada / $sumaPonderaciones;
                $mapaCalificaciones[$alumno->id][$criterioPromedioId] = round($promedioCalculado, 2);
                $promediosIndividuales[] = $promedioCalculado;
            } else if ($criterioPromedioId) {
                 // Si no hay calificaciones para promediar, ponemos 0 en el promedio
                 $mapaCalificaciones[$alumno->id][$criterioPromedioId] = 0;
            }
        }
        // 3. Calcular el promedio del GRUPO
        $promedioGrupo = 0;
        if (count($promediosIndividuales) > 0) {
            $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
        }

        $nombreMaestro = 'Sin asignar'; // Valor por defecto
    $asignacion = DB::table('grupo_materia_maestro') // Tu nombre de tabla pivote
                      ->where('grupo_id', $request->grupo_id)
                      ->where('materia_id', $request->materia_id)
                      ->first();

    // Verifica si se encontró la asignación y si la columna 'maestro_id' existe y no es nula
    if ($asignacion && isset($asignacion->maestro_id)) {
        // Busca al usuario usando el ID de la tabla pivote (que es la PK 'id' de users)
        $maestro = User::find($asignacion->maestro_id); // find() busca por la PK 'id' del modelo User

        // Verifica si se encontró el usuario
        if ($maestro) {
            // Construye el nombre completo
            $nombreMaestro = $maestro->name . ' ' . $maestro->apellido_paterno . ' ' . $maestro->apellido_materno;
        }
    }

    // 6. Añadir el nombre del maestro al JSON de respuesta
    return response()->json([
        'alumnos' => $alumnos,
        'criterios' => $criteriosOrdenados,
        'calificaciones' => $mapaCalificaciones,
        'promedioGrupo' => round($promedioGrupo, 2),
        'nombreMaestro' => trim($nombreMaestro) // Usamos trim por si apellido_materno es null
    ]);
    }
}