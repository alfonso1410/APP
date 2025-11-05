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


        // 5. Cargar Calificaciones (Tu lógica es correcta y eficiente)
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
            'materia' => $materia, // $materia ahora es el objeto inyectado
            'nombreMaestro' => $nombreMaestro,
            'criterios' => $criteriosOrdenados,
            'calificaciones' => $calificaciones,
        ];

        // 7. Generar el PDF
        $pdf = Pdf::loadView('reportes.concentrado-periodo', $data, [], [
            'format' => 'Legal', // Tamaño Oficio (Legal)
            'orientation' => 'L'  // Orientación Horizontal (Landscape)
        ]);

        // Se añade el nombre de la materia al archivo
        return $pdf->stream('concentrado-' . $grupo->nombre_grupo . '-' . $materia->nombre . '.pdf');
    }
}