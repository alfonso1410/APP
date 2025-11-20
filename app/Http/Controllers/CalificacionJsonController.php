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
use App\Models\CampoFormativo; // <--- ¡AGREGA ESTO!

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

private function getPromedioMateria($alumno_id, $materia_id, $periodo_id)
    {
        // 1. Encontrar el criterio "Promedio" para la materia fuente.
        //    Usamos whereHas para usar la relación de Eloquent 'catalogoCriterio'
        //    en lugar de un join manual.
        $materiaCriterioPromedio = MateriaCriterio::where('materia_id', $materia_id)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Promedio');
            })
            ->select('materia_criterio_id') // Solo necesitamos el ID
            ->first();

        // Si la materia no tiene un criterio "Promedio", no podemos obtener su calificación.
        if (!$materiaCriterioPromedio) {
            return 0;
        }

        // 2. Buscar la calificación guardada para ESE criterio "Promedio".
        $calificacion = Calificacion::where('alumno_id', $alumno_id)
            ->where('periodo_id', $periodo_id)
            ->where('materia_criterio_id', $materiaCriterioPromedio->materia_criterio_id)
            ->value('calificacion_obtenida');

        // Devolver la calificación (o 0 si no se encontró).
        return $calificacion ?? 0;
    }

   private function buildMetaMateriaTabla(Request $request, Materia $materia, Periodo $periodo, Grupo $grupo, $nombreMaestro)
    {

        $idiomaParaFaltas = null;

        $asignacion = DB::table('grupo_materia_maestro')
            ->where('grupo_id', $grupo->grupo_id)
            ->where('materia_id', $materia->materia_id)
            ->first();

            if ($asignacion) {
            $maestroAsignado = User::find($asignacion->maestro_id);
            if ($maestroAsignado) {
                // A. Verificamos si es TITULAR (Español/Ingles)
                $pivote = $maestroAsignado->gruposTitulares()->find($grupo->grupo_id);
                
                if ($pivote && isset($pivote->pivot->idioma)) {
                    $idiomaParaFaltas = $pivote->pivot->idioma;
                } 
                // B. Verificamos si es COMPLEMENTARIO
                elseif ($grupo->maestrosComplementarios()->where('users.id', $maestroAsignado->id)->exists()) {
                    $idiomaParaFaltas = 'ESPAÑOL';
                }
            }
        }

        if (!$idiomaParaFaltas) {
            $idiomaParaFaltas = 'INGLES'; 
        }
        // 1. Obtener Alumnos (sin cambios)
        $alumnos = $grupo->alumnosActuales()
            ->where('estado_alumno', 'ACTIVO')
            ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')
            ->get(['alumnos.alumno_id as id', 'nombres', 'apellido_paterno', 'apellido_materno']);

        $nombreCampoFuente = 'English';
        // 3. Encontrar el Campo Formativo "Fuente"
        $campoFuente = CampoFormativo::where('nombre', $nombreCampoFuente)
                                     ->where('nivel_id', $grupo->grado->nivel_id) // Asegura que sea del mismo nivel
                                     ->first();
            
     // 4. Encontrar el Criterio "Promedio" de la materia "Lengua Extranjera"
        $criterioPromedioReal = MateriaCriterio::where('materia_id', $materia->materia_id)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Promedio');
            })
            ->first();

            $criterioFaltasReal = MateriaCriterio::where('materia_id', $materia->materia_id)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Faltas');
            })
            ->first();

            // VALIDACIÓN DE SETUP
        if (!$campoFuente || !$criterioPromedioReal) {
            $warning = 'Error de Configuración: ';
            if (!$campoFuente) {
                $warning .= "No se encontró el campo formativo '$nombreCampoFuente'. ";
            }
            if (!$criterioPromedioReal) {
                $warning .= "La materia 'Lengua Extranjera' no tiene asignado el criterio 'Promedio'.";
            }
            return response()->json([
                'alumnos' => $alumnos, 'criterios' => [], 'calificaciones' => [], 'promedioGrupo' => 0,
                'nombreMaestro' => $nombreMaestro, 'setup_warning' => $warning
            ]);
        }
            
        
     // 5. Encontrar las MATERIAS FUENTE para ESTE GRADO
        // Buscamos en la estructura curricular del grado al que pertenece el grupo
        $materiasFuente = $grupo->grado->materias()
                                 ->wherePivot('campo_id', $campoFuente->campo_id)
                                 ->orderBy('nombre')
                                 ->get();
       if ($materiasFuente->isEmpty()) {
             $warning = "No se encontraron materias fuente asignadas al campo '$nombreCampoFuente' para este grado.";
             return response()->json([
                'alumnos' => $alumnos, 'criterios' => [], 'calificaciones' => [], 'promedioGrupo' => 0,
                'nombreMaestro' => $nombreMaestro, 'setup_warning' => $warning
            ]);
        }

      // 6. Preparar los criterios para el JSON
        $criteriosJson = [];
        // Usamos las materias fuente como "criterios virtuales"
        foreach ($materiasFuente as $materiaFuente) {
            $criteriosJson[] = [
                // Usamos el ID de la materia como ID del "criterio virtual"
                'id' => $materiaFuente->materia_id, 
                'nombre_criterio' => $materiaFuente->nombre, // "Spelling", "Colors", etc.
                'es_promedio' => false,
                'es_faltas' => false,
                'es_calculado' => true // Siempre será de solo lectura
            ];
        }

        if ($criterioFaltasReal) {
            $criteriosJson[] = [
                'id' => $criterioFaltasReal->materia_criterio_id, 
                'nombre_criterio' => 'Faltas', // O $criterioFaltasReal->catalogoCriterio->nombre
                'es_promedio' => false,
                'es_faltas' => true,    // <--- Importante para que el front sepa qué icono poner
                'es_calculado' => true  // Read-only
            ];
        }

        // Añadimos el criterio "Promedio" REAL al final de la lista
        $criteriosJson[] = [
            'id' => $criterioPromedioReal->materia_criterio_id, 
            'nombre_criterio' => 'Promedio',
            'es_promedio' => true,
            'es_faltas' => false,
            'es_calculado' => true // El promedio final también es de solo lectura
        ];

      // 7. Construir mapa de calificaciones
        $mapaCalificaciones = [];
        $promediosIndividuales = []; // Promedios finales de cada alumno

        foreach ($alumnos as $alumno) {
            $mapaCalificaciones[$alumno->id] = [];
            $sumaCalificaciones = 0;
            $totalCalificaciones = 0;

            // Iteramos sobre las MATERIAS FUENTE (no los criterios JSON)
            foreach ($materiasFuente as $materiaFuente) {
                
                // ¡Usamos la función helper!
                $calificacion = $this->getPromedioMateria(
                    $alumno->id, 
                    $materiaFuente->materia_id, 
                    $request->periodo_id
                );
                
                // Asignamos la calificación al "criterio virtual" (que tiene el materia_id)
                $mapaCalificaciones[$alumno->id][$materiaFuente->materia_id] = $calificacion;

                // Acumulamos para el promedio de "Lengua Extranjera"
                // (Asumimos un promedio simple de las materias fuente)
                if ($calificacion > 0) { // O la lógica que prefieras
                    $sumaCalificaciones += $calificacion;
                    $totalCalificaciones++;
                }
            }
            // --- MODIFICACIÓN 3: Obtener y asignar las FALTAS ---
            if ($criterioFaltasReal) {
                // Buscamos las faltas DIRECTAS de registro_asistencia filtrando por idioma INGLES
                // (Asumimos que todas las materias de English comparten la asistencia de 'INGLES')
                $totalFaltas = RegistroAsistencia::where('alumno_id', $alumno->id)
                    ->where('periodo_id', $request->periodo_id)
                    ->where('tipo_asistencia', 'FALTA')
                    ->where('idioma', $idiomaParaFaltas) // <--- CLAVE: Usamos el idioma determinado dinámicamente
                    ->count();

                // Asignamos el valor al mapa
                $mapaCalificaciones[$alumno->id][$criterioFaltasReal->materia_criterio_id] = $totalFaltas;
            }
            // 8. Calcular y asignar el "Promedio" final de "Lengua Extranjera"
            $promedioFinal = 0;
            if ($totalCalificaciones > 0) {
                $calculo = $sumaCalificaciones / $totalCalificaciones;
                 
                 // Paso 2: TRUNCAMOS a 2 decimales (sin redondear hacia arriba)
                 $promedioFinal = floor($calculo * 100) / 100;
            }
            
            $mapaCalificaciones[$alumno->id][$criterioPromedioReal->materia_criterio_id] = $promedioFinal;
            $promediosIndividuales[] = $promedioFinal;
        }
        
        // 9. Calcular Promedio del Grupo
        $promedioGrupo = 0;
        if (count($promediosIndividuales) > 0) {
            $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
        }

        // 10. Enviar la respuesta JSON
        return response()->json([
            'alumnos' => $alumnos,
            'criterios' => $criteriosJson, // La nueva lista de criterios
            'calificaciones' => $mapaCalificaciones,
            'promedioGrupo' => round($promedioGrupo, 2),
            'nombreMaestro' => trim($nombreMaestro),
            'setup_warning' => '' // Sin advertencia si todo salió bien
        ]);
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

        $materia = Materia::find($request->materia_id);
        
         $nombreMaestro = 'Sin asignar'; // Valor por defecto
        $idiomaDeLaMateria = null;
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

            try {
                    $pivote = $maestro->gruposTitulares()->find($grupo->grupo_id);
                    
                    if ($pivote && isset($pivote->pivot->idioma)) {
                         $idiomaDeLaMateria = $pivote->pivot->idioma; // ej: 'ingles' o 'español'
                    }
                    else {
                        // 2. Si no es titular, verificamos si es COMPLEMENTARIO
                        // Usamos la relación que creamos en el Modelo Grupo
                        $esComplementario = $grupo->maestrosComplementarios()
                                                  ->where('users.id', $maestro->id)
                                                  ->exists();

                        if ($esComplementario) {
                            // ¡AQUÍ ESTÁ EL TRUCO!
                            // Si es complementario, le asignamos ESPAÑOL para que lea las faltas que guardaste
                            $idiomaDeLaMateria = 'ESPAÑOL'; 
                        }
                    }
                } catch (\Exception $e) {
                    // Manejar error si la relación no existe o falla
                    \Log::error("Error al buscar idioma del maestro: " . $e->getMessage());
                }
        }
    }

    // --- INICIO DE MODIFICACIÓN: META-MATERIA ---
        // Si la materia es "Lengua Extranjera", usamos la lógica nueva y salimos
        if ($materia && $materia->nombre === 'Lengua Extranjera') {
            return $this->buildMetaMateriaTabla($request, $materia, $periodo, $grupo, $nombreMaestro);
        }
        // --- FIN DE MODIFICACIÓN ---

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
                'es_faltas' => $esFaltas, // <-- CORRECCIÓN 2: Agregar bandera al JSON
                'es_calculado' => ($esPromedio || $esFaltas)
            ];
        });

        // 1. Particionamos la colección en TRES partes
        list($faltas, $criteriosSinFaltas) = $criterios->partition(fn ($c) => $c['es_faltas']);
        list($promedios, $otrosCriterios) = $criteriosSinFaltas->partition(fn ($c) => $c['es_promedio']);

        // 2. Unimos las colecciones en el orden deseado:
        //    Otros Criterios (Examen, Tareas, etc.)
        //    -> Faltas
        //    -> Promedio
        $criteriosOrdenados = $otrosCriterios->merge($faltas)->merge($promedios)->values();
        
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
                $totalFaltas = 0;
                  if ($idiomaDeLaMateria) { 
                    
                    $totalFaltas = RegistroAsistencia::where('alumno_id', $alumno->id)
                        ->where('periodo_id', $periodo->periodo_id) 
                        ->where('tipo_asistencia', 'FALTA')
                        ->where('idioma', $idiomaDeLaMateria) // <-- ¡LA LÍNEA CLAVE!
                        ->count();
                }
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