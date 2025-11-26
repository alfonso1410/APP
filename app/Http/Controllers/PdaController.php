<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nivel;
use App\Models\Grupo;
use App\Models\CampoFormativo;
use App\Models\CicloEscolar;
use App\Models\PdaEvaluacion;
use Illuminate\Support\Facades\DB;

class PdaController extends Controller
{
    /**
     * Vista principal.
     */
    public function index()
    {
        // 1. Niveles de Preescolar
        $niveles = Nivel::where('nombre', 'LIKE', '%Preescolar%')->get();

        // 2. Todos los ciclos para el filtro
        $ciclos = CicloEscolar::orderBy('ciclo_escolar_id', 'desc')->get();

        // 3. Ciclo activo
        $cicloActivo = $ciclos->where('estado', 'ACTIVO')->first();

        // 4. Periodos del ciclo activo
        $periodos = $cicloActivo ? $cicloActivo->periodos : collect([]);

        return view('admin.pda.index', compact('niveles', 'ciclos', 'cicloActivo', 'periodos'));
    }

    /**
     * Obtener periodos por ciclo (AJAX).
     */
    public function getPeriodos($ciclo_id)
    {
        $ciclo = CicloEscolar::with('periodos')->findOrFail($ciclo_id);
        return response()->json($ciclo->periodos);
    }

    /**
     * Retorna alumnos activos, campos, materias y evaluaciones guardadas.
     */
    public function getData(Request $request)
    {
        $grupo_id = $request->grupo_id;
        $periodo_id = $request->periodo_id;

        // Cargamos el grupo y solo alumnos con es_actual = 1
        $grupo = Grupo::with([
            'grado',
            'alumnos' => function ($q) {
                $q->where('es_actual', 1); // ← FILTRO APLICADO AQUÍ
            }
        ])->findOrFail($grupo_id);

        // 1. Campos formativos (general)
        $campos = CampoFormativo::all();

        // 2. Materias específicas del grado
        $materias = $grupo->grado->materias()
            ->where(function ($q) {
                $q->where('nombre', 'LIKE', '%Artes%')
                  ->orWhere('nombre', 'LIKE', '%Educación Física%')
                  ->orWhere('nombre', 'LIKE', '%Lengua Extranjera%');
            })
            ->get();

        // 3. Evaluaciones guardadas
        $alumnoIds = $grupo->alumnos->pluck('alumno_id');

        $evaluaciones = PdaEvaluacion::where('periodo_id', $periodo_id)
            ->whereIn('alumno_id', $alumnoIds)
            ->get();

        return response()->json([
            'alumnos' => $grupo->alumnos->sortBy('apellido_paterno')->values(),
            'campos' => $campos,
            'materias' => $materias,
            'evaluaciones' => $evaluaciones
        ]);
    }

    /**
     * Guardar o actualizar evaluaciones.
     */
    public function store(Request $request)
    {
        $datos = $request->input('evaluaciones');
        $periodo_id = $request->input('periodo_id');

        DB::beginTransaction();
        try {
            foreach ($datos as $item) {
                PdaEvaluacion::updateOrCreate(
                    [
                        'alumno_id' => $item['alumno_id'],
                        'periodo_id' => $periodo_id,
                        'campo_formativo_id' => $item['campo_formativo_id'] ?? null,
                        'materia_id' => $item['materia_id'] ?? null,
                    ],
                    [
                        'observacion' => $item['texto']
                    ]
                );
            }

            DB::commit();
            return response()->json(['message' => 'Guardado correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al guardar: ' . $e->getMessage()], 500);
        }
    }
}
