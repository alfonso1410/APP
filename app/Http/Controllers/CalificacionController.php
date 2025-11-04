<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Grado;
use App\Models\Periodo;
use App\Models\Calificacion;
use App\Models\MateriaCriterio;
use App\Models\Nivel;
use App\Models\CicloEscolar;
use App\Models\CatalogoCriterio;
use App\Models\RegistroAsistencia;
use App\Models\Grupo; // <-- Importar Grupo
use App\Models\Materia; // <-- Importar Materia
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Importar Auth

class CalificacionController extends Controller
{
    /**
     * Muestra la vista principal de captura de calificaciones,
     * adaptada según el rol del usuario.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Encontrar el ciclo escolar ACTIVO
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        // 2. Cargar periodos ABIERTOS SÓLO del ciclo ACTIVO
        $periodos = collect(); // Colección vacía por defecto
        if ($cicloActivo) {
            $periodos = Periodo::where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                              ->where('estado', 'ABIERTO')
                              ->orderBy('fecha_inicio')
                              ->get(['periodo_id as id', 'nombre']);
        } else {
            // Si no hay ciclo activo, no se puede hacer nada.
            // (Podrías redirigir con un error si lo prefieres)
        }

        // --- LÓGICA DE ROLES ---

        // 3A. Lógica para Administradores
        if (in_array($user->rol, ['DIRECTOR', 'COORDINADOR'])) {
            
            // Cargar niveles (lógica original)
            $niveles = Nivel::orderBy('nivel_id')->get(['nivel_id as id', 'nombre']);
            $niveles->push((object)[
                'id' => 'extra',
                'nombre' => 'Extracurricular'
            ]);

            // Devolver la vista de Administrador (la original)
            return view('admin.calificaciones.index', [
                'niveles' => $niveles,
                'periodos' => $periodos,
            ]);
        }

        // 3B. Lógica para Maestros
        if ($user->rol === 'MAESTRO') {
            $maestroId = $user->id;
            $gruposDelMaestro = collect();

            if ($cicloActivo) {
                // Buscamos todas las asignaciones (grupo-materia) del maestro
                // en el ciclo escolar activo.
                $asignaciones = DB::table('grupo_materia_maestro as gmm')
                    ->join('grupos', 'gmm.grupo_id', '=', 'grupos.grupo_id')
                    ->join('grados', 'grupos.grado_id', '=', 'grados.grado_id')
                    ->join('materias', 'gmm.materia_id', '=', 'materias.materia_id')
                    ->where('gmm.maestro_id', $maestroId)
                    ->where('grupos.ciclo_escolar_id', $cicloActivo->ciclo_escolar_id)
                    ->select(
                        'gmm.grupo_id', 
                        'grupos.nombre_grupo', 
                        'grados.nombre as nombre_grado',
                        'gmm.materia_id', 
                        'materias.nombre as nombre_materia'
                    )
                    ->orderBy('grupos.nombre_grupo')
                    ->orderBy('materias.nombre')
                    ->get();
                
                // Agrupamos las materias por grupo
                // [ 
                //   '3' => [ {materia_id: 5, ...}, {materia_id: 7, ...} ], 
                //   '4' => [ ... ] 
                // ]
                $gruposDelMaestro = $asignaciones->groupBy('grupo_id');
            }

            // Devolver la NUEVA vista de Maestro
            return view('admin.calificaciones.index_maestro', [
                'gruposDelMaestro' => $gruposDelMaestro,
                'periodos' => $periodos,
            ]);
        }

        // Por si acaso, si un rol no coincide
        return redirect()->route('dashboard')->withErrors('No tiene permisos para esta sección.');
    }

    /**
     * Guarda las calificaciones enviadas desde el formulario.
     */
    public function store(Request $request)
    {
        $request->validate([
            'periodo_id' => 'required|integer|exists:periodos,periodo_id',
            'materia_id' => 'required|integer|exists:materias,materia_id',
            'grupo_id' => 'required|integer|exists:grupos,grupo_id',
            'calificaciones' => 'required|array',
            'calificaciones.*.*' => 'nullable|numeric|min:0|max:10', // <-- CAMBIADO DE 100 A 10
        ]);
        
        $user = Auth::user();
        $periodoId = $request->periodo_id;
        $materiaId = $request->materia_id;
        $grupoId = $request->grupo_id;
        $calificacionesInput = $request->calificaciones;

        // --- MEJORA DE SEGURIDAD ---
        // Si es un maestro, validar que tenga asignada esta combinación
        if ($user->rol === 'MAESTRO') {
            $esAsignado = DB::table('grupo_materia_maestro')
                ->where('maestro_id', $user->id)
                ->where('grupo_id', $grupoId)
                ->where('materia_id', $materiaId)
                ->exists();
            
            if (!$esAsignado) {
                return back()->withErrors('Usted no tiene permiso para guardar calificaciones para este grupo o materia.');
            }
        }
        // --- FIN DE MEJORA DE SEGURIDAD ---


        // 1. Obtener el modelo Periodo
        $periodo = Periodo::findOrFail($periodoId);

        // 2. Obtener las reglas de ponderación y criterios calculados
        $materiaCriterios = MateriaCriterio::where('materia_id', $materiaId)
                                          ->with('catalogoCriterio')
                                          ->get();

        $criterioPromedioId = null;
        $criterioFaltasId = null; 
        $criteriosParaPromediar = []; 

        foreach ($materiaCriterios as $mc) {
            $nombreCriterio = $mc->catalogoCriterio->nombre ?? '';
            if ($mc->incluido_en_promedio) {
                $criteriosParaPromediar[$mc->materia_criterio_id] = $mc->ponderacion > 0 ? $mc->ponderacion : 1;
            }
            if (strcasecmp($nombreCriterio, 'Promedio') == 0) {
                $criterioPromedioId = $mc->materia_criterio_id;
            }
            if (strcasecmp($nombreCriterio, 'Faltas') == 0) {
                $criterioFaltasId = $mc->materia_criterio_id;
            }
        }

        // 3. Recalcular promedios ANTES de guardar
        $calificacionesParaGuardar = $calificacionesInput;

        foreach ($calificacionesInput as $alumnoId => $criterios) {
            
            $totalFaltasCalculadas = 0;
            // --- Recalcular FALTAS ---
            if ($criterioFaltasId) {
                $totalFaltasCalculadas = RegistroAsistencia::where('alumno_id', $alumnoId)
                    ->where('grupo_id', $grupoId) 
                    ->where('tipo_asistencia', 'FALTA')
                    ->whereBetween('fecha', [$periodo->fecha_inicio, $periodo->fecha_fin])
                    ->count();
                
                $calificacionesParaGuardar[$alumnoId][$criterioFaltasId] = $totalFaltasCalculadas;
            }

            // --- Recalcular PROMEDIO ---
            $sumaPonderada = 0;
            $sumaPonderaciones = 0;

            foreach ($calificacionesParaGuardar[$alumnoId] as $criterioId => $valor) {
                // Excluir "Promedio" del cálculo de sí mismo
                // y verificar si es un criterio ponderable
                if ($criterioId != $criterioPromedioId && isset($criteriosParaPromediar[$criterioId])) {
                    
                    // Asumimos que "Faltas" no se incluye en el promedio ponderado.
                    // Si SÍ debe incluirse, esta lógica debe ajustarse.
                    if ($criterioId == $criterioFaltasId) {
                        continue;
                    }

                    if (is_numeric($valor)) {
                        $ponderacion = $criteriosParaPromediar[$criterioId];
                        $sumaPonderada += $valor * $ponderacion;
                        $sumaPonderaciones += $ponderacion;
                    }
                }
            }

            // Asignar el promedio calculado
            if ($criterioPromedioId) {
                if ($sumaPonderaciones > 0) {
                    $promedioCalculado = $sumaPonderada / $sumaPonderaciones;
                    $calificacionesParaGuardar[$alumnoId][$criterioPromedioId] = round($promedioCalculado, 2);
                } else {
                    $calificacionesParaGuardar[$alumnoId][$criterioPromedioId] = 0;
                }
            }
        } // Fin del bucle foreach $alumnoId

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
                            'capturado_por' => Auth::id() // Auditoría
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Calificaciones guardadas y promedios recalculados exitosamente.')
                     ->withInput();
    }
}