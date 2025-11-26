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
    public function index()
    {
        // 1. Obtenemos niveles de Preescolar usando su PK correcta 'nivel_id'
        $niveles = Nivel::where('nombre', 'LIKE', '%Preescolar%')->get();

        // 2. Buscamos el Ciclo Activo usando la columna 'estado' (CORREGIDO)
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();
        
        // Obtenemos periodos si hay ciclo activo
        $periodos = $cicloActivo ? $cicloActivo->periodos : collect([]);

        return view('admin.pda.index', compact('niveles', 'periodos'));
    }

    // Retorna alumnos, materias y evaluaciones guardadas (JSON)
    public function getData(Request $request)
    {
        $grupo_id = $request->grupo_id;
        $periodo_id = $request->periodo_id;

        // Cargar el Grupo con sus alumnos y su grado asociado
        // Usamos 'grupo_id' implícitamente al buscar con findOrFail
        $grupo = Grupo::with(['alumnos', 'grado'])->findOrFail($grupo_id);
        
        // 1. Obtener Campos Formativos (General)
        // Asumimos que quieres todos los campos disponibles
        $campos = CampoFormativo::all();

        // 2. Obtener Materias Específicas (Inglés, Artes, Ed Física)
        // Accedemos a las materias a través del Grado del grupo (Relación corregida según tus modelos)
        $materias = $grupo->grado->materias()
                    ->where(function($q) {
                        $q->Where('nombre', 'LIKE', '%Artes%')
                          ->orWhere('nombre', 'LIKE', '%Educación Física%')
                          ->orWhere('nombre', 'LIKE', '%Lengua Extranjera%');
                    })
                    ->get();

        // 3. Obtener Evaluaciones ya guardadas
        // Filtramos por periodo_id y los alumnos de este grupo
        $alumnoIds = $grupo->alumnos->pluck('alumno_id'); // Usamos la PK correcta del alumno

        $evaluaciones = PdaEvaluacion::where('periodo_id', $periodo_id)
                        ->whereIn('alumno_id', $alumnoIds)
                        ->get();

        return response()->json([
            // Ordenamos alumnos por apellido
            'alumnos' => $grupo->alumnos->sortBy('apellido_paterno')->values(),
            'campos' => $campos,
            'materias' => $materias,
            'evaluaciones' => $evaluaciones
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->input('evaluaciones'); // Array desde el frontend
        $periodo_id = $request->input('periodo_id');

        DB::beginTransaction();
        try {
            foreach ($datos as $item) {
                // Solo guardamos si hay texto o si queremos borrar (podríamos agregar lógica para borrar si viene vacío)
                // Usamos updateOrCreate buscando por las llaves foráneas correctas
                PdaEvaluacion::updateOrCreate(
                    [
                        'alumno_id' => $item['alumno_id'],
                        'periodo_id' => $periodo_id,
                        // Usamos null coalescing (?? null) por si no viene el dato
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