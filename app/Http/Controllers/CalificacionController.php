<?php

namespace App\Http\Controllers; // <-- Corregido (sin Admin)

use App\Http\Controllers\Controller;
use App\Models\Grado;
use App\Models\Periodo; // Asumo que tienes este modelo
use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MateriaCriterio;
use App\Models\Nivel;
class CalificacionController extends Controller
{
    /**
     * Muestra la vista principal de captura de calificaciones.
     */
    public function index()
    {
        // 2. Ya no cargamos Grados, ahora cargamos Niveles
        $niveles = Nivel::orderBy('nivel_id')->get(['nivel_id as id', 'nombre']);
        $periodos = Periodo::where('estado', 'ABIERTO')->get(['periodo_id as id', 'nombre']); 

        return view('admin.calificaciones.index', [
            'niveles' => $niveles, // 3. Pasamos niveles
            'periodos' => $periodos,
            // 'grados' => $grados, // <-- 4. Ya no pasamos grados
        ]);
    }

    /**
     * Guarda las calificaciones enviadas desde el formulario.
     */
  public function store(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|integer|exists:periodos,periodo_id',
            'calificaciones' => 'required|array',
            'calificaciones.*.*' => 'nullable|numeric|min:0|max:100', // Asumo max 100
        ]);

        $periodoId = $request->periodo_id;
        $calificacionesInput = $request->calificaciones;

        // 1. Necesitamos saber la materia_id para obtener los criterios
        // No la estamos enviando. ¡Hay que añadirla!
        // Ve al Paso 3 (Vista) y añade este campo.
        $request->validate(['materia_id' => 'required|integer|exists:materias,materia_id']);
        $materiaId = $request->materia_id;

        // 2. Obtener las reglas de ponderación
        $materiaCriterios = MateriaCriterio::where('materia_id', $materiaId)
                                            ->with('catalogoCriterio')
                                            ->get();

        $criterioPromedioId = null;
        $criteriosParaPromediar = []; // [criterio_id => ponderacion]

        foreach ($materiaCriterios as $mc) {
            $nombreCriterio = $mc->catalogoCriterio->nombre ?? '';
            if ($mc->incluido_en_promedio) {
                $criteriosParaPromediar[$mc->materia_criterio_id] = $mc->ponderacion > 0 ? $mc->ponderacion : 1;
            }
            if (strcasecmp($nombreCriterio, 'Promedio') == 0) {
                $criterioPromedioId = $mc->materia_criterio_id;
            }
        }

        // 3. Recalcular promedios ANTES de guardar
        $calificacionesParaGuardar = $calificacionesInput;

        foreach ($calificacionesInput as $alumnoId => $criterios) {
            $sumaPonderada = 0;
            $sumaPonderaciones = 0;

            foreach ($criterios as $criterioId => $valor) {
                if (isset($criteriosParaPromediar[$criterioId]) && is_numeric($valor)) {
                    $ponderacion = $criteriosParaPromediar[$criterioId];
                    $sumaPonderada += $valor * $ponderacion;
                    $sumaPonderaciones += $ponderacion;
                }
            }

            // Sobrescribir el valor del promedio en el array que vamos a guardar
            if ($criterioPromedioId && $sumaPonderaciones > 0) {
                $promedioCalculado = $sumaPonderada / $sumaPonderaciones;
                $calificacionesParaGuardar[$alumnoId][$criterioPromedioId] = round($promedioCalculado, 2);
            }
        }

        // 4. Guardar en la Base de Datos
        DB::transaction(function () use ($calificacionesParaGuardar, $periodoId) {
            foreach ($calificacionesParaGuardar as $alumnoId => $criterios) {
                foreach ($criterios as $materiaCriterioId => $valor) {
                    
                    if (is_null($valor) || $valor === '') {
                        Calificacion::where([
                            'alumno_id' => $alumnoId,
                            'materia_criterio_id' => $materiaCriterioId,
                            'periodo_id' => $periodoId,
                        ])->delete();
                        continue;
                    }

                    Calificacion::updateOrCreate(
                        [
                            'alumno_id' => $alumnoId,
                            'materia_criterio_id' => $materiaCriterioId,
                            'periodo_id' => $periodoId,
                        ],
                        [
                            'calificacion_obtenida' => $valor,
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Calificaciones guardadas y promedios recalculados exitosamente.')
                     ->withInput();
    }
}