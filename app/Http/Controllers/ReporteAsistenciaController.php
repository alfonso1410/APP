<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\RegistroAsistencia;
use App\Models\CicloEscolar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF; // 

class ReporteAsistenciaController extends Controller
{
    /**
     * Muestra un formulario para seleccionar grupo, periodo y tipo de reporte.
     */
   public function index()
    {
        $user = Auth::user();
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        $grupos = collect();
        $periodos = collect();

        if ($cicloActivo) {
            // ... (Tu lógica existente para obtener grupos y periodos se queda igual) ...
            if ($user->rol === 'MAESTRO') {
                $gruposTitulares = $user->gruposTitulares()
                    ->where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                    ->get();
                $gruposComplementarios = Grupo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                    ->whereHas('maestrosComplementarios', fn($q) => $q->where('users.id', $user->id))
                    ->get();
                $grupos = $gruposTitulares->merge($gruposComplementarios)->unique('grupo_id');
            } else {
                $grupos = Grupo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                    ->with('grado')
                    ->orderBy('nombre_grupo')
                    ->get();
            }

            $periodos = Periodo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                ->orderBy('fecha_inicio')
                ->get();
        }

        // --- AGREGADO: Listas para el Select ---
        $opcionesIdiomas = ['ESPAÑOL', 'INGLES'];
        
        // Aquí agregas manualmente las demás materias cuando quieras
        $opcionesMaterias = ['COMPUTACION', 'EDUCACION EN LA FE', 'SOCIOEMOCIONAL', 'EDUCACIÓN FÍSICA', 'ARTES']; 

        return view('reportes.asistencia.index', compact('grupos', 'periodos', 'opcionesIdiomas', 'opcionesMaterias'));
    }

    /**
     * Genera el PDF de asistencia según los parámetros.
     */
   public function generar(Request $request)
{
    $request->validate([
        'grupo_id' => 'required|exists:grupos,grupo_id',
        'mes' => 'required|integer|min:1|max:12',
        'anio' => 'required|integer|min:2020|max:2030',
        'tipo' => 'required|in:idioma,materia',
        'valor' => 'required|string',
    ]);

    $grupo = Grupo::findOrFail($request->grupo_id);
    $mes = $request->mes;
    $anio = $request->anio;
    $tipo = $request->tipo;
    $valor = $tipo === 'idioma' ? strtoupper($request->valor) : strtoupper($request->valor);

    // Obtener el periodo activo
    $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();
    
    if (!$cicloActivo) {
        abort(400, 'No hay un ciclo escolar activo.');
    }

    $periodo = Periodo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
        ->whereRaw('? BETWEEN fecha_inicio AND fecha_fin', ["{$anio}-".str_pad($mes, 2, '0', STR_PAD_LEFT)."-01"])
        ->first();

    if (!$periodo) {
        $periodo = Periodo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
            ->orderBy('fecha_inicio')
            ->first();
            
        if (!$periodo) {
            abort(400, 'No hay periodos configurados en el ciclo activo.');
        }
    }

    // Generar días hábiles del mes
    $fechaInicio = Carbon::create($anio, $mes, 1);
    $fechaFin = $fechaInicio->copy()->endOfMonth();
    
    $diasHabiles = [];
    $diasFormato = [];
    $diasSemana = []; // NUEVO: Array para los días de la semana

    $fecha = $fechaInicio->copy();
    while ($fecha->lte($fechaFin)) {
        if ($fecha->isWeekday()) {
            $diasHabiles[] = $fecha->format('Y-m-d');
            $diasFormato[] = $fecha->format('d');
            
            // NUEVO: Agregar letra del día de la semana
            $diaSemana = $fecha->dayOfWeek;
            $letrasDias = [
                0 => 'D',  // Domingo (no debería aparecer)
                1 => 'L',  // Lunes
                2 => 'M',  // Martes
                3 => 'MI', // Miércoles
                4 => 'J',  // Jueves
                5 => 'V',  // Viernes
                6 => 'S',  // Sábado (no debería aparecer)
            ];
            $diasSemana[] = $letrasDias[$diaSemana];
        }
        $fecha->addDay();
    }

    // Obtener alumnos
    $alumnos = $grupo->alumnosActuales()
        ->orderBy('apellido_paterno')
        ->orderBy('apellido_materno')
        ->orderBy('nombres')
        ->get();

    // Obtener asistencias del mes
    $asistencias = RegistroAsistencia::where('grupo_id', $grupo->grupo_id)
        ->where('idioma', $valor)
        ->whereIn('fecha', $diasHabiles)
        ->get()
        ->groupBy('alumno_id')
        ->map(fn($regs) => $regs->pluck('tipo_asistencia', 'fecha')->toArray());

    // Nombre del mes en español
    $mesesNombres = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
        5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
    ];
    $mesNombre = $mesesNombres[$mes];

    $data = compact(
        'grupo',
        'periodo',
        'tipo',
        'valor',
        'alumnos',
        'diasHabiles',
        'diasFormato',
        'diasSemana', // NUEVO: Pasar los días de la semana
        'asistencias',
        'mesNombre',
        'anio'
    );

    // IMPORTANTE: Cambiar el nombre de la vista
    $pdf = PDF::loadView('reportes.asistencia.reporte_asistencia', $data, [], [
        'format' => 'Letter',
        'orientation' => 'L',
        'mode' => 'utf-8',
    ]);

   $grado = $grupo->grado ? trim($grupo->grado->nombre) : '';
    
    // 2. Obtenemos el nombre del grupo (ej: "A") y limpiamos espacios
    $grupoNombre = trim($grupo->nombre_grupo);
    
    // 3. Construimos el nombre final: "Asistencias_1A_AGOSTO.pdf"
    $nombreArchivo = "Asistencias_{$grado}{$grupoNombre}_{$mesNombre}.pdf";
    return $pdf->stream($nombreArchivo);
}
}