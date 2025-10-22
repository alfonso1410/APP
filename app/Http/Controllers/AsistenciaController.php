<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Grupo; // Importar Grupo
use App\Models\RegistroAsistencia; // Importar RegistroAsistencia
use Carbon\Carbon; // ¡Importante para las fechas!

class AsistenciaController extends Controller
{
    /**
     * Muestra la lista de grupos (la que ya tenías).
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
     * Muestra la tabla para tomar asistencia de un grupo específico.
     */
    public function tomarAsistencia(Request $request, Grupo $grupo): View
    {
        // 1. Cargar alumnos del grupo
        $alumnos = $grupo->alumnos()->orderBy('apellido_paterno')->get();

        // 2. Generar el selector de semanas
        // (Esto crea 10 semanas pasadas y 10 futuras desde hoy)
        $semanasDisponibles = $this->generarSemanas();

        // 3. Determinar la semana seleccionada
        // Si la URL tiene un ?semana=... la usamos, si no, usamos la semana actual.
        $fechaLunesSeleccionado = $request->input('semana', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));
        $lunes = Carbon::parse($fechaLunesSeleccionado)->startOfWeek(Carbon::MONDAY);
        
        $diasDeLaSemana = [];
        for ($i = 0; $i < 5; $i++) {
            $diasDeLaSemana[] = $lunes->copy()->addDays($i)->format('Y-m-d');
        }

        // 4. Cargar la asistencia existente para esta semana
        $asistencias = RegistroAsistencia::where('grupo_id', $grupo->grupo_id)
            ->whereIn('fecha', $diasDeLaSemana)
            ->get()
            // Creamos un "mapa" para la vista: ['alumno_id']['fecha'] => 'TIPO'
            ->groupBy('alumno_id')
            ->map(fn ($registrosAlumno) => $registrosAlumno->keyBy('fecha'));
        
        return view('maestro.asistencias.tomar', [
            'grupo' => $grupo,
            'alumnos' => $alumnos,
            'semanasDisponibles' => $semanasDisponibles,
            'lunesSeleccionado' => $lunes,
            'diasDeLaSemana' => $diasDeLaSemana,
            'asistencias' => $asistencias,
        ]);
    }

    /**
     * Guarda los cambios de asistencia.
     */
    public function guardarAsistencia(Request $request, Grupo $grupo)
    {
        // 1. Obtenemos el array 'asistencia' del formulario
        $asistenciaData = $request->input('asistencia', []);

        foreach ($asistenciaData as $alumno_id => $fechas) {
            foreach ($fechas as $fecha => $tipo_asistencia) {
                // 2. Usamos updateOrCreate para actualizar o crear el registro
                RegistroAsistencia::updateOrCreate(
                    [
                        'alumno_id' => $alumno_id,
                        'grupo_id' => $grupo->grupo_id,
                        'fecha' => $fecha,
                    ],
                    [
                        'tipo_asistencia' => $tipo_asistencia,
                    ]
                );
            }
        }

        // 3. Redirigimos de vuelta a la misma página con un mensaje de éxito
        return redirect()->back()->with('status', '¡Asistencia guardada exitosamente!');
    }


    /**
     * Helper para generar el selector de semanas (Lunes - Viernes)
     */
    private function generarSemanas(): array
    {
        $semanas = [];
        $inicio = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(10); // 10 semanas atrás
        $fin = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks(10); // 10 semanas adelante

        for ($date = $inicio; $date->lte($fin); $date->addWeek()) {
            $lunes = $date->copy()->format('Y-m-d');
            $viernes = $date->copy()->endOfWeek(Carbon::FRIDAY)->format('d/m/Y');
            $texto = "Semana del " . $date->format('d/m/Y') . " al " . $viernes;
            
            $semanas[$lunes] = $texto;
        }
        return $semanas;
    }
}