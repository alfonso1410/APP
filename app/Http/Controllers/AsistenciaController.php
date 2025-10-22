<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Necesario para Auth::user()
use Illuminate\View\View;
use App\Models\Grupo;
use App\Models\RegistroAsistencia;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Muestra la lista de grupos (Sin cambios)
     */
    public function gruposIndex(): View
    {
        $maestro = Auth::user();
        $grupos = $maestro->gruposTitulares()
                         ->with('grado')
                         ->withCount('alumnos')
                         ->get();

        return view('maestro.asistencias.index', [
            'maestro' => $maestro,
            'gruposAsignados' => $grupos,
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

        // 2. Cargar alumnos (Sin cambios)
        $alumnos = $grupo->alumnos()->orderBy('apellido_paterno')->get();

        // 3. Generar semanas (Sin cambios)
        $semanasDisponibles = $this->generarSemanas();
        $fechaLunesSeleccionado = $request->input('semana', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        $lunes = Carbon::parse($fechaLunesSeleccionado)->startOfWeek(Carbon::MONDAY);
        
        $diasDeLaSemana = [];
        for ($i = 0; $i < 5; $i++) {
            $diasDeLaSemana[] = $lunes->copy()->addDays($i)->format('Y-m-d');
        }

        // 4. Cargar asistencias filtrando por el idioma DETECTADO
        $asistencias = RegistroAsistencia::where('grupo_id', $grupo->grupo_id)
            ->whereIn('fecha', $diasDeLaSemana)
            ->where('idioma', $idiomaDelMaestro) // <-- LÓGICA AUTOMÁTICA
            ->get()
            ->groupBy('alumno_id')
            ->map(fn ($registrosAlumno) => $registrosAlumno->keyBy('fecha'));
        
        return view('maestro.asistencias.tomar', [
            'grupo' => $grupo,
            'alumnos' => $alumnos,
            'semanasDisponibles' => $semanasDisponibles,
            'lunesSeleccionado' => $lunes,
            'diasDeLaSemana' => $diasDeLaSemana,
            'asistencias' => $asistencias,
            // Opcional: pasar el idioma a la vista para mostrarlo
            'idiomaDelMaestro' => $idiomaDelMaestro 
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

        // 2. Iterar y guardar
        foreach ($asistenciaData as $alumno_id => $fechas) {
            foreach ($fechas as $fecha => $tipo_asistencia) {
                
                RegistroAsistencia::updateOrCreate(
                    [
                        // Columnas para BUSCAR
                        'alumno_id' => $alumno_id,
                        'grupo_id' => $grupo->grupo_id,
                        'fecha' => $fecha,
                        'idioma' => $idiomaDelMaestro, // <-- LÓGICA AUTOMÁTICA
                    ],
                    [
                        // Columnas para ACTUALIZAR
                        'tipo_asistencia' => $tipo_asistencia,
                    ]
                );
            }
        }

        return redirect()->back()->with('status', '¡Asistencia guardada exitosamente!');
    }


    /**
     * Helper para generar semanas (Sin cambios)
     */
    private function generarSemanas(): array
    {
        $semanas = [];
        $inicio = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(10);
        $fin = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks(10);

        for ($date = $inicio; $date->lte($fin); $date->addWeek()) {
            $lunes = $date->copy()->format('Y-m-d');
            $viernes = $date->copy()->endOfWeek(Carbon::FRIDAY)->format('d/m/Y');
            $texto = "Semana del " . $date->format('d/m/Y') . " al " . $viernes;
            $semanas[$lunes] = $texto;
        }
        return $semanas;
    }
}