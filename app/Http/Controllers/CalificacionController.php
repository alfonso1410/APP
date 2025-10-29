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
use App\Models\CicloEscolar;

class CalificacionController extends Controller
{
    /**
     * Muestra la vista principal de captura de calificaciones.
     */
 public function index()
    {
        // 1. Encontrar el ciclo escolar ACTIVO
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        // 2. Cargar niveles (esto no cambia)
        $niveles = Nivel::orderBy('nivel_id')->get(['nivel_id as id', 'nombre']);
        $niveles->push((object)[
            'id' => 'extra',
            'nombre' => 'Extracurricular'
        ]);

        // --- INICIO DE MODIFICACIÓN ---
        // 3. Cargar periodos ABIERTOS SÓLO del ciclo ACTIVO
        $periodos = collect(); // Colección vacía por defecto
        if ($cicloActivo) {
            $periodos = Periodo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                               ->where('estado', 'ABIERTO') // Solo periodos abiertos
                               ->orderBy('fecha_inicio')
                               ->get(['periodo_id as id', 'nombre']);
        }
        // --- FIN DE MODIFICACIÓN ---

        return view('admin.calificaciones.index', [
            'niveles' => $niveles,
            'periodos' => $periodos, // Ahora solo contiene periodos del ciclo activo
            // 'cicloActivo' => $cicloActivo // Puedes pasar esto si lo necesitas
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
            
            $totalFaltasCalculadas = 0; // Guardamos el valor calculado aquí
            // Recalcular FALTAS
            if ($criterioFaltasId) {
                 $totalFaltasCalculadas = RegistroAsistencia::where('alumno_id', $alumnoId)
                    ->where('grupo_id', $grupoId)
                    ->where('tipo_asistencia', 'FALTA')
                    ->whereBetween('fecha', [$periodo->fecha_inicio, $periodo->fecha_fin])
                    ->count();
                // Sobrescribimos el valor del input con el valor calculado
                $calificacionesParaGuardar[$alumnoId][$criterioFaltasId] = $totalFaltasCalculadas;
            }

            // Recalcular PROMEDIO (usando los valores actualizados, incluyendo faltas)
            $sumaPonderada = 0;
            $sumaPonderaciones = 0;
            foreach ($calificacionesParaGuardar[$alumnoId] as $criterioId => $valor) {
                 // Usamos $totalFaltasCalculadas si es el criterio de faltas
                 $valorReal = ($criterioId == $criterioFaltasId) ? $totalFaltasCalculadas : $valor;

                 if (isset($criteriosParaPromediar[$criterioId]) && is_numeric($valorReal)) {
                     $ponderacion = $criteriosParaPromediar[$criterioId];
                     // NOTA: Convierte el valor de faltas si es necesario para el promedio
                     
                     $sumaPonderada += $calificacionParaPromedio * $ponderacion;
                     $sumaPonderaciones += $ponderacion;
                 }
            }

            if ($criterioPromedioId && $sumaPonderaciones > 0) {
                $promedioCalculado = $sumaPonderada / $sumaPonderaciones;
                $calificacionesParaGuardar[$alumnoId][$criterioPromedioId] = round($promedioCalculado, 2);
            } else if ($criterioPromedioId) {
                $calificacionesParaGuardar[$alumnoId][$criterioPromedioId] = 0;
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