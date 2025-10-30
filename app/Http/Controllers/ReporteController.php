<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\User; // Para el maestro
use App\Models\Materia;
use App\Models\MateriaCriterio;
use App\Models\Calificacion;
use PDF;
// Importar mPDF
use Illuminate\Support\Facades\DB; // Para la asignación del maestro

class ReporteController extends Controller
{
    /**
     * Genera el PDF del concentrado de calificaciones para un grupo y periodo.
     */
    public function generarConcentradoPeriodo(Grupo $grupo, Periodo $periodo)
    {
        // 1. Cargar datos del Grupo y Periodo (Laravel ya lo hace por Route Model Binding)
        $grupo->load('grado'); // Cargar el nombre del grado
        $periodo->load('cicloEscolar'); // Cargar el ciclo escolar

        // 2. Cargar Alumnos del Grupo
        $alumnos = $grupo->alumnosActuales()
                         ->orderBy('apellido_paterno')
                         ->orderBy('apellido_materno')
                         ->orderBy('nombres')
                         ->get();

        // 3. Determinar la Materia y el Maestro
        //    (Esta lógica asume UNA materia por grupo, como en el ejemplo 'Español'.
        //     Si un grupo tiene VARIAS materias, necesitarías otro parámetro en la URL)
        $materia = $grupo->materias()->first(); // Asumimos la primera materia asignada
        $nombreMaestro = 'Sin asignar';

        if ($materia) {
            $asignacion = DB::table('grupo_materia_maestro')
                              ->where('grupo_id', $grupo->grupo_id)
                              ->where('materia_id', $materia->materia_id)
                              ->first();

            if ($asignacion && $asignacion->maestro_id) {
                $maestro = User::find($asignacion->maestro_id);
                if ($maestro) {
                    $nombreMaestro = $maestro->name . ' ' . $maestro->apellido_paterno . ' ' . $maestro->apellido_materno;
                }
            }

            // 4. Cargar Criterios de la Materia
            //    Usamos la misma lógica que en tu controlador JSON para ordenar
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
                    // Añade otras banderas si las necesitas (ej. 'es_faltas')
                ];
            });

            // Reordenar "Promedio" al final
            list($promedios, $otrosCriterios) = $criterios->partition(fn($c) => $c['es_promedio']);
            $criteriosOrdenados = $otrosCriterios->merge($promedios)->values();

        } else {
            // Manejar caso sin materia asignada
            $criteriosOrdenados = collect();
        }

        // 5. Cargar Calificaciones
        $calificaciones = Calificacion::where('periodo_id', $periodo->periodo_id)
                                      ->whereIn('alumno_id', $alumnos->pluck('alumno_id'))
                                      ->whereIn('materia_criterio_id', $criteriosOrdenados->pluck('id'))
                                      ->get()
                                      // Mapear para acceso fácil: [alumno_id][criterio_id]
                                      ->groupBy('alumno_id')
                                      ->map(fn($califs) => $califs->keyBy('materia_criterio_id'));

        // 6. Preparar datos para la vista
        $data = [
            'grupo' => $grupo,
            'periodo' => $periodo,
            'alumnos' => $alumnos,
            'materia' => $materia,
            'nombreMaestro' => $nombreMaestro,
            'criterios' => $criteriosOrdenados,
            'calificaciones' => $calificaciones,
        ];

        // 7. Generar el PDF
        $pdf = Pdf::loadView('reportes.concentrado-periodo', $data, [], [
            'format' => 'Legal', // Tamaño Oficio (Legal)
            'orientation' => 'L'  // Orientación Horizontal (Landscape)
        ]);

        return $pdf->stream('concentrado-' . $grupo->nombre_grupo . '.pdf');
    }
}