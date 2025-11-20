<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\User; // Para el maestro
use App\Models\Materia; // <-- Importante: Asegurarse que esté importada
use App\Models\MateriaCriterio;
use App\Models\Calificacion;
use PDF;
use App\Models\CampoFormativo;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth; // <-- CORRECCIÓN: Importar Auth

class ReporteController extends Controller
{
    /**
     * Genera el PDF del concentrado de calificaciones para un grupo, periodo y materia.
     */
    
    public function generarConcentradoPeriodo(Grupo $grupo, Periodo $periodo, Materia $materia)
    {
        $user = Auth::user();

        // --- INICIO DE CORRECCIÓN: VALIDACIÓN DE SEGURIDAD ---
        // Se añade esta validación para prevenir el error 403
        if ($user->rol === 'MAESTRO') {
            $esAsignado = DB::table('grupo_materia_maestro')
                ->where('maestro_id', $user->id)
                ->where('grupo_id', $grupo->grupo_id)
                ->where('materia_id', $materia->materia_id)
                ->exists();
            
            if (!$esAsignado) {
                // Si no es su grupo/materia, prohibir acceso.
                abort(403, 'Usted no tiene permiso para generar este reporte.');
            }
        }
        // --- FIN DE CORRECCIÓN ---


        // 1. Cargar datos del Grupo y Periodo (Laravel ya lo hace por Route Model Binding)
        $grupo->load('grado'); // Cargar el nombre del grado
        $periodo->load('cicloEscolar'); // Cargar el ciclo escolar

        // 2. Cargar Alumnos del Grupo (Tu lógica es correcta)
        $alumnos = $grupo->alumnosActuales()
                          ->orderBy('apellido_paterno')
                          ->orderBy('apellido_materno')
                          ->orderBy('nombres')
                          ->get();

        // 3. Lógica del Maestro refactorizada
        $nombreMaestro = 'Sin asignar';

        $asignacion = $grupo->materias()
                            ->where('materias.materia_id', $materia->materia_id)
                            ->first();

        // Leemos el 'maestro_id' desde el pivote
        if ($asignacion && $asignacion->pivot->maestro_id) {
            $maestro = User::find($asignacion->pivot->maestro_id);
            if ($maestro) {
                // Se usa 'trim' para limpiar espacios extra si un apellido no existe
                $nombreMaestro = trim($maestro->name . ' ' . $maestro->apellido_paterno . ' ' . $maestro->apellido_materno);
            }
        }

        if ($materia->nombre === 'Lengua Extranjera') {
            return $this->generarReporteMetaMateria($grupo, $periodo, $materia, $alumnos, $nombreMaestro);
        }

        // 4. Cargar Criterios de la Materia
        $materiaCriterios = MateriaCriterio::where('materia_id', $materia->materia_id)
                                          ->with('catalogoCriterio')
                                          ->orderBy('materia_criterio_id')
                                          ->get();
        
        $criterios = $materiaCriterios->map(function ($mc) {
            $nombre = $mc->catalogoCriterio->nombre ?? 'Criterio s/n';
            return [
                'id' => $mc->materia_criterio_id,
                'nombre' => $nombre,
                'es_promedio' => (strcasecmp($nombre, 'Promedio') == 0),
            ];
        });

        // Reordenar "Promedio" al final
        list($promedios, $otrosCriterios) = $criterios->partition(fn($c) => $c['es_promedio']);
        $criteriosOrdenados = $otrosCriterios->merge($promedios)->values();

        // Filtramos la colección para excluir el criterio 'FALTAS'
        $criteriosOrdenados = $criteriosOrdenados->filter(function ($criterio) {
            // Usamos strcasecmp para ignorar mayúsculas/minúsculas y trim por si hay espacios.
            // Queremos mantener los criterios donde el nombre NO SEA 'FALTAS'.
            return strcasecmp(trim($criterio['nombre']), 'FALTAS') != 0;
        })->values();

        // 5. Cargar Calificaciones (Tu lógica es correcta y eficiente)
        $calificaciones = Calificacion::where('periodo_id', $periodo->periodo_id)
                                      ->whereIn('alumno_id', $alumnos->pluck('alumno_id'))
                                      ->whereIn('materia_criterio_id', $criteriosOrdenados->pluck('id'))
                                      ->get()
                                      // Mapear para acceso fácil: [alumno_id][criterio_id]
                                      ->groupBy('alumno_id')
                                      ->map(fn($califs) => $califs->keyBy('materia_criterio_id'));


                                      // Calcular el promedio del grupo
$promedioGrupo = 0;
$promediosIndividuales = [];

// Buscar el criterio de "Promedio"
$criterioPromedioId = $criteriosOrdenados->firstWhere('es_promedio', true)['id'] ?? null;

if ($criterioPromedioId) {
    foreach ($alumnos as $alumno) {
        $calif = $calificaciones->get($alumno->alumno_id)
                                ?->get($criterioPromedioId)
                                ?->calificacion_obtenida;
        
        if (is_numeric($calif)) {
            $promediosIndividuales[] = $calif;
        }
    }
    
    if (count($promediosIndividuales) > 0) {
        $promedioGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
    }
}
        // 6. Preparar datos para la vista
    $data = [
    'grupo' => $grupo,
    'periodo' => $periodo,
    'alumnos' => $alumnos,
    'materia' => $materia,
    'nombreMaestro' => $nombreMaestro,
    'criterios' => $criteriosOrdenados,
    'calificaciones' => $calificaciones,
    'promedioGrupo' => $promedioGrupo, // <-- AGREGAR ESTA LÍNEA
];

        // 7. Generar el PDF
        $pdf = Pdf::loadView('reportes.concentrado-periodo', $data, [], [
            'format' => 'A4', // Tamaño Oficio (Legal)
            'orientation' => 'L',  // Orientación Horizontal (Landscape)
            'mode' => 'utf-8',
        ]);

        // Se añade el nombre de la materia al archivo
        return $pdf->stream('concentrado-' . $grupo->nombre_grupo . '-' . $materia->nombre . '.pdf');
    }


    private function generarReporteMetaMateria($grupo, $periodo, $materia, $alumnos, $nombreMaestro)
    {
        // 1. Buscar el Campo Formativo "English Fuente"
        // (Asegúrate de que el nombre coincida con tu BD)
        $nombreCampoFuente = 'English'; 
        
        $campoFuente = CampoFormativo::where('nombre', $nombreCampoFuente)
                                     ->where('nivel_id', $grupo->grado->nivel_id)
                                     ->first();
                                     
        // 2. Obtener las Materias Fuente (que actuarán como "Criterios")
        $materiasFuente = collect();
        if ($campoFuente) {
            $materiasFuente = $grupo->grado->materias()
                                    ->wherePivot('campo_id', $campoFuente->campo_id)
                                    ->orderBy('nombre')
                                    ->get();
        }

        // 3. Buscar el Criterio "Promedio" REAL de Lengua Extranjera
        $criterioPromedioReal = MateriaCriterio::where('materia_id', $materia->materia_id)
            ->whereHas('catalogoCriterio', function ($query) {
                $query->where('nombre', 'Promedio');
            })->first();
            
        // 4. Construir la lista de "Criterios" para el PDF
        // El PDF espera una colección con propiedades ['id', 'nombre', 'es_promedio']
        $criteriosOrdenados = collect();

        foreach ($materiasFuente as $mat) {
            $criteriosOrdenados->push([
                'id' => $mat->materia_id, // Usamos ID de materia
                'nombre' => $mat->nombre,
                'es_promedio' => false
            ]);
        }

        // Añadimos el Promedio al final
        if ($criterioPromedioReal) {
            $criteriosOrdenados->push([
                'id' => $criterioPromedioReal->materia_criterio_id,
                'nombre' => 'PROMEDIO',
                'es_promedio' => true
            ]);
        }

        // 5. Construir mapa de calificaciones
        // El PDF espera: $calificaciones[alumno_id] -> Collection de objetos Calificacion
        // Como "Calificacion" es un modelo, crearemos objetos falsos (stdClass) con la propiedad 'calificacion_obtenida'
        
        $calificaciones = collect();
        $promediosIndividuales = [];

        foreach ($alumnos as $alumno) {
            $alumnoCalifs = collect();
            $suma = 0;
            $total = 0;

            // Para cada materia fuente ("criterio virtual")
            foreach ($materiasFuente as $mat) {
                // Buscamos el promedio de esa materia
                $val = $this->getPromedioDeMateria($alumno->alumno_id, $mat->materia_id, $periodo->periodo_id);
                
                // Guardamos en la colección simulada
                $alumnoCalifs->put($mat->materia_id, (object)['calificacion_obtenida' => $val]);

                if ($val > 0) {
                    $suma += $val;
                    $total++;
                }
            }

            // Calculamos promedio final
            $promedioFinal = 0;
            if ($total > 0) {
                $calculo = $suma / $total;
                $promedioFinal = floor($calculo * 100) / 100; // Truncado a 2 decimales
            }
            $promediosIndividuales[] = $promedioFinal;

            // Guardamos el promedio final
            if ($criterioPromedioReal) {
                $alumnoCalifs->put($criterioPromedioReal->materia_criterio_id, (object)['calificacion_obtenida' => $promedioFinal]);
            }

            $calificaciones->put($alumno->alumno_id, $alumnoCalifs);
        }

        // 6. Promedio del Grupo
        $promedioGrupo = 0;
        if (count($promediosIndividuales) > 0) {
            $calculoGrupo = array_sum($promediosIndividuales) / count($promediosIndividuales);
            $promedioGrupo = floor($calculoGrupo * 100) / 100;
        }

        // 7. Generar PDF
        $data = [
            'grupo' => $grupo,
            'periodo' => $periodo,
            'alumnos' => $alumnos,
            'materia' => $materia,
            'nombreMaestro' => $nombreMaestro,
            'criterios' => $criteriosOrdenados, // Lista virtual + promedio
            'calificaciones' => $calificaciones, // Mapa virtual
            'promedioGrupo' => $promedioGrupo,
        ];

        $pdf = PDF::loadView('reportes.concentrado-periodo', $data, [], [
            'format' => 'A4', 'orientation' => 'L', 'mode' => 'utf-8',
        ]);

        return $pdf->stream('concentrado-' . $grupo->nombre_grupo . '-' . $materia->nombre . '.pdf');
    }

    /**
     * Helper para obtener el promedio de una materia (Copiado de JsonController)
     */
    private function getPromedioDeMateria($alumnoId, $materiaId, $periodoId)
    {
        $criterioPromedio = MateriaCriterio::where('materia_id', $materiaId)
            ->whereHas('catalogoCriterio', function ($q) {
                $q->where('nombre', 'Promedio');
            })->first();

        if (!$criterioPromedio) return 0;

        return Calificacion::where('alumno_id', $alumnoId)
            ->where('periodo_id', $periodoId)
            ->where('materia_criterio_id', $criterioPromedio->materia_criterio_id)
            ->value('calificacion_obtenida') ?? 0;
    }
}