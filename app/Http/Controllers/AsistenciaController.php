<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para Auth::user()
use Illuminate\View\View;
use App\Models\Grupo;
use App\Models\RegistroAsistencia;
use Carbon\Carbon;
use App\Models\CicloEscolar; // <-- 1. Importar CicloEscolar
use App\Models\Periodo;

class AsistenciaController extends Controller
{
    /**
     * Muestra la lista de grupos (Sin cambios)
     */
public function gruposIndex(): View
    {
        $maestro = Auth::user();
        // --- INICIO MODIFICACIÓN ---
        // 1. Buscar el ciclo activo
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        // 2. Cargar grupos del maestro SÓLO del ciclo activo
        $grupos = collect(); // Colección vacía por defecto
        if ($cicloActivo) {
            $grupos = $maestro->gruposTitulares()
                              ->where('grupos.ciclo_escolar_id', $cicloActivo->ciclo_escolar_id) // Filtro clave
                              ->with('grado')
                              ->withCount('alumnos')
                              ->get();
        }
        // --- FIN MODIFICACIÓN ---

        return view('maestro.asistencias.index', [
            'maestro' => $maestro,
            'gruposAsignados' => $grupos,
            'cicloActivo' => $cicloActivo // Pasar el ciclo por si lo necesitas
        ]);
    }

    /**
     * Muestra la tabla para tomar asistencia.
     * --- LÓGICA DE DETECCIÓN AUTOMÁTICA ---
     */
    public function tomarAsistencia(Request $request, Grupo $grupo): View
    {
        // 1. OBTENER EL IDIOMA DEL MAESTRO LOGUEADO PARA ESTE GRUPO
        $maestroLogueado = Auth::user();
        // Buscamos la relación pivote específica para este grupo
        $pivote = $maestroLogueado->gruposTitulares()->find($grupo->grupo_id);

        // Si no está asignado, no debería estar aquí
        if (!$pivote) {
            abort(403, 'No estás asignado como titular a este grupo.');
        }
        
        // ¡Automáticamente sabemos el idioma! (Gracias al withPivot() del Modelo)
        $idiomaDelMaestro = $pivote->pivot->idioma; 

  // 2. Cargar alumnos (sin cambios)
        $alumnos = $grupo->alumnosActuales() // Usar alumnosActuales es mejor
                         ->orderBy('apellido_paterno')->get();

     // --- INICIO NUEVA LÓGICA DE PERIODOS Y SEMANAS ---

        // 3. Obtener Ciclo Activo y sus Periodos
        //    (Podrías obtener ciclo_escolar_id directamente de $grupo->ciclo_escolar_id)
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();
        if (!$cicloActivo) {
             abort(500, 'No hay un ciclo escolar activo definido.'); // O manejar de otra forma
        }

        $periodosDisponibles = Periodo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                                      ->orderBy('fecha_inicio')
                                      ->get();

        // 4. Determinar Periodo Seleccionado
        $periodoSeleccionadoId = $request->input('periodo_id', $periodosDisponibles->first()->periodo_id ?? null);
        $periodoSeleccionado = $periodosDisponibles->find($periodoSeleccionadoId);

        if (!$periodoSeleccionado) {
            abort(500, 'Periodo no válido o no encontrado.'); // O manejar de otra forma
        }

        // 5. Generar Semanas SÓLO del Periodo Seleccionado
        $semanasDelPeriodo = $this->generarSemanasDelPeriodo(
            Carbon::parse($periodoSeleccionado->fecha_inicio),
            Carbon::parse($periodoSeleccionado->fecha_fin)
        );

        // 6. Determinar Semana Seleccionada (Lunes)
        //    Default: El lunes de la semana actual si cae dentro del periodo, si no, el primer lunes del periodo
        $defaultLunes = Carbon::now()->startOfWeek(Carbon::MONDAY);
        if ($defaultLunes->lt($periodoSeleccionado->fecha_inicio) || $defaultLunes->gt($periodoSeleccionado->fecha_fin)) {
            $defaultLunes = Carbon::parse($periodoSeleccionado->fecha_inicio)->startOfWeek(Carbon::MONDAY);
        }
        $fechaLunesSeleccionado = $request->input('semana', $defaultLunes->format('Y-m-d'));

        // Validar que el lunes seleccionado esté en las semanas generadas
        if (!isset($semanasDelPeriodo[$fechaLunesSeleccionado])) {
             // Si no es válido, forzar al primer lunes del periodo
            $fechaLunesSeleccionado = Carbon::parse($periodoSeleccionado->fecha_inicio)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
             // Asegurarse de que incluso el primero exista (caso raro de periodo corto)
            if (!isset($semanasDelPeriodo[$fechaLunesSeleccionado])) {
                $fechaLunesSeleccionado = array_key_first($semanasDelPeriodo) ?? $defaultLunes->format('Y-m-d');
            }
        }
        $lunesCarbon = Carbon::parse($fechaLunesSeleccionado)->startOfWeek(Carbon::MONDAY);


        // 7. Generar Días de la Semana (Lunes a Viernes)
        $diasDeLaSemana = [];
        for ($i = 0; $i < 5; $i++) {
            $diasDeLaSemana[] = $lunesCarbon->copy()->addDays($i)->format('Y-m-d');
        }

        // 8. Cargar Asistencias
        $asistencias = RegistroAsistencia::where('grupo_id', $grupo->grupo_id)
            ->where('periodo_id', $periodoSeleccionado->periodo_id) // <-- Filtro por periodo
            ->whereIn('fecha', $diasDeLaSemana)
            ->where('idioma', $idiomaDelMaestro)
            ->get()
            ->groupBy('alumno_id')
            ->map(fn ($registrosAlumno) => $registrosAlumno->keyBy('fecha'));

        // --- FIN NUEVA LÓGICA ---

        return view('maestro.asistencias.tomar', [
            'grupo' => $grupo,
            'alumnos' => $alumnos,
            'periodosDisponibles' => $periodosDisponibles,      // <-- Nuevo
            'periodoSeleccionadoId' => $periodoSeleccionadoId, // <-- Nuevo
            'semanasDisponibles' => $semanasDelPeriodo,         // <-- Modificado (nombre y contenido)
            'lunesSeleccionado' => $lunesCarbon,                // <-- Modificado (objeto Carbon)
            'diasDeLaSemana' => $diasDeLaSemana,
            'asistencias' => $asistencias,
            'idiomaDelMaestro' => $idiomaDelMaestro,
            'cicloActivo' => $cicloActivo ?? null              // <-- Opcional: Pasar ciclo
        ]);
    }
    /**
     * Guarda los cambios de asistencia.
     * --- LÓGICA DE DETECCIÓN AUTOMÁTICA ---
     */
    public function guardarAsistencia(Request $request, Grupo $grupo)
    {
        $asistenciaData = $request->input('asistencia', []);

        // 1. OBTENER EL IDIOMA (igual que en el método 'tomar')
        $maestroLogueado = Auth::user();
        $pivote = $maestroLogueado->gruposTitulares()->find($grupo->grupo_id);

        if (!$pivote) {
            abort(403, 'No puedes guardar asistencia para un grupo al que no perteneces.');
        }
        $idiomaDelMaestro = $pivote->pivot->idioma;

       // --- INICIO MODIFICACIÓN ---
        // 2. Cargar los periodos del ciclo para buscar rápido
        $cicloId = $grupo->ciclo_escolar_id; // Asumimos que el grupo ya tiene el ID correcto
        $periodosDelCiclo = Periodo::where('ciclo_escolar_id', $cicloId)->get();
        // --- FIN MODIFICACIÓN ---

        // 3. Iterar y guardar
        foreach ($asistenciaData as $alumno_id => $fechas) {
            foreach ($fechas as $fecha => $tipo_asistencia) {

                // --- INICIO MODIFICACIÓN ---
                // 4. Encontrar el periodo_id para esta fecha
                $fechaCarbon = Carbon::parse($fecha);
                $periodoId = null;
                foreach ($periodosDelCiclo as $p) {
                    if ($fechaCarbon->betweenIncluded($p->fecha_inicio, $p->fecha_fin)) {
                        $periodoId = $p->periodo_id;
                        break;
                    }
                }

                // Si no encontramos periodo para la fecha, saltamos este registro (o lanzamos error)
                if (!$periodoId) {
                    \Log::warning("No se encontró periodo para la fecha {$fecha} al guardar asistencia.");
                    continue; // Saltar este registro
                }
                // --- FIN MODIFICACIÓN ---

                RegistroAsistencia::updateOrCreate(
                    [
                        'alumno_id' => $alumno_id,
                        'grupo_id' => $grupo->grupo_id,
                        'fecha' => $fecha,
                        'idioma' => $idiomaDelMaestro,
                        'periodo_id' => $periodoId, // <-- Añadido al array de búsqueda
                    ],
                    [
                        'tipo_asistencia' => $tipo_asistencia,
                        'periodo_id' => $periodoId, // <-- Añadido al array de valores
                    ]
                );
            }
        }

        // --- INICIO MODIFICACIÓN ---
        // 5. Redirigir de vuelta manteniendo el periodo y semana seleccionados
        $redirectUrl = route('maestro.asistencias.tomar', [
            'grupo' => $grupo->grupo_id,
            'periodo_id' => $request->input('periodo_id'), // Asegúrate que la vista envíe esto
            'semana' => $request->input('semana') // Asegúrate que la vista envíe esto
        ]);
        return redirect($redirectUrl)->with('status', '¡Asistencia guardada exitosamente!');
        // --- FIN MODIFICACIÓN ---
    }

    /**
     * Helper para generar semanas (Sin cambios)
     */
   private function generarSemanasDelPeriodo(Carbon $inicioPeriodo, Carbon $finPeriodo): array
    {
        $semanas = [];
        // Empezamos desde el Lunes de la semana en que inicia el periodo
        $lunesActual = $inicioPeriodo->copy()->startOfWeek(Carbon::MONDAY);

        // Iteramos mientras el Lunes esté antes o sea igual al fin del periodo
        while ($lunesActual->lte($finPeriodo)) {
            // Asegurarse de que al menos un día de la semana (L-V) cae dentro del periodo
            $viernesActual = $lunesActual->copy()->addDays(4); // Viernes de esa semana
            if ($lunesActual->betweenIncluded($inicioPeriodo, $finPeriodo) || $viernesActual->betweenIncluded($inicioPeriodo, $finPeriodo) || ($lunesActual->lt($inicioPeriodo) && $viernesActual->gt($finPeriodo)) ) {

                 $lunes = $lunesActual->copy()->format('Y-m-d');
                 // Usamos min() para no pasarnos de la fecha fin del periodo
                 $viernesTexto = $viernesActual->min($finPeriodo)->format('d/m/Y');
                 // Usamos max() para no empezar antes de la fecha inicio del periodo
                 $lunesTexto = $lunesActual->max($inicioPeriodo)->format('d/m/Y');

                 $texto = "Semana del " . $lunesTexto . " al " . $viernesTexto;
                 $semanas[$lunes] = $texto;
            }
            // Avanzar a la siguiente semana
            $lunesActual->addWeek();
        }
        return $semanas;
    }
}