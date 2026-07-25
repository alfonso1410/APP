<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nivel;
use App\Models\Grupo;
use App\Models\CampoFormativo;
use App\Models\CicloEscolar;
use App\Models\PdaEvaluacion;
use App\Models\Periodo;
use App\Models\GrupoTitular;
use App\Models\GrupoMateriaMaestro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PdaController extends Controller
{
    /**
     * Vista principal.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Ciclo Activo
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->with('periodos')->first();
        $periodos = $cicloActivo ? $cicloActivo->periodos : collect([]);
        $ciclos = CicloEscolar::orderBy('ciclo_escolar_id', 'desc')->get();

        // --- LÓGICA PARA ADMINISTRADORES ---
        if (in_array($user->rol, ['DIRECTOR', 'COORDINADOR'])) {
            $niveles = Nivel::where('nombre', 'LIKE', '%Preescolar%')->get();
            return view('admin.pda.index', compact('niveles', 'ciclos', 'cicloActivo', 'periodos'));
        }

        // --- LÓGICA PARA MAESTROS ---
        if ($user->rol === 'MAESTRO') {
            $gruposData = [];

            if ($cicloActivo) {
                
                // ==========================================
                // 1. BUSCAR ASIGNACIONES COMO TITULAR O AUXILIAR
                // ==========================================
                $titularidades = GrupoTitular::where(function($q) use ($user) {
                        $q->where('maestro_titular_id', $user->id)
                          ->orWhere('maestro_auxiliar_id', $user->id);
                    })
                    ->with(['grupo.grado'])
                    ->whereHas('grupo', function($q) use ($cicloActivo) {
                        // Filtro 1: Que sea del ciclo activo
                        $q->where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id);
                        
                        // Filtro 2: Que sea SOLO de Preescolar
                        $q->whereHas('grado.nivel', function($qNivel) {
                            $qNivel->where('nombre', 'LIKE', '%Preescolar%');
                        });
                    })
                    ->get();

                foreach ($titularidades as $tit) {
                    $gId = $tit->grupo_id;
                    $nombreGrupo = $tit->grupo->grado->nombre . ' - ' . $tit->grupo->nombre_grupo;

                    if (!isset($gruposData[$gId])) {
                        $gruposData[$gId] = [
                            'id' => $gId,
                            'nombre' => $nombreGrupo,
                            'opciones' => []
                        ];
                    }

                    if ($tit->idioma === 'ESPAÑOL') {
                        $gruposData[$gId]['opciones'][] = ['val' => 'campos_formativos', 'label' => 'Campos Formativos'];
                    } elseif ($tit->idioma === 'INGLES') {
                        $gruposData[$gId]['opciones'][] = ['val' => 'lengua_extranjera', 'label' => 'Lengua Extranjera'];
                    }
                }

                // ==========================================
                // 2. BUSCAR ASIGNACIONES DE MATERIA (COMPLEMENTARIAS)
                // ==========================================
                $materiasAsignadas = GrupoMateriaMaestro::where('maestro_id', $user->id)
                    ->with(['grupo.grado', 'materia'])
                    ->whereHas('grupo', function($q) use ($cicloActivo) {
                        // Filtro 1: Ciclo activo
                        $q->where('ciclo_escolar_id', $cicloActivo->ciclo_escolar_id);

                        // Filtro 2: Que sea SOLO de Preescolar
                        $q->whereHas('grado.nivel', function($qNivel) {
                            $qNivel->where('nombre', 'LIKE', '%Preescolar%');
                        });
                    })
                    ->get();

                foreach ($materiasAsignadas as $assign) {
                    $gId = $assign->grupo_id;
                    $nombreMateria = $assign->materia->nombre;
                    $nombreGrupo = $assign->grupo->grado->nombre . ' - ' . $assign->grupo->nombre_grupo;

                    if (!isset($gruposData[$gId])) {
                        $gruposData[$gId] = [
                            'id' => $gId,
                            'nombre' => $nombreGrupo,
                            'opciones' => []
                        ];
                    }

                    if (str_contains($nombreMateria, 'Artes')) {
                        $existe = array_filter($gruposData[$gId]['opciones'], fn($op) => $op['val'] === 'artes');
                        if (!$existe) {
                            $gruposData[$gId]['opciones'][] = ['val' => 'artes', 'label' => 'Artes'];
                        }
                    } elseif (str_contains($nombreMateria, 'Educación Física')) {
                        $existe = array_filter($gruposData[$gId]['opciones'], fn($op) => $op['val'] === 'educacion_fisica');
                        if (!$existe) {
                            $gruposData[$gId]['opciones'][] = ['val' => 'educacion_fisica', 'label' => 'Educación Física'];
                        }
                    } elseif (str_contains($nombreMateria, 'Socioemocional')) {
                        $existe = array_filter($gruposData[$gId]['opciones'], fn($op) => $op['val'] === 'socioemocional');
                        if (!$existe) {
                            $gruposData[$gId]['opciones'][] = ['val' => 'socioemocional', 'label' => 'Socioemocional'];
                        }
                    }
                }
            }

            $misGrupos = collect($gruposData)->values();
            return view('maestro.pda.index', compact('misGrupos', 'periodos', 'cicloActivo'));
        }

        return redirect()->route('dashboard');
    }

    public function getPeriodos($ciclo_id)
    {
        $ciclo = CicloEscolar::with('periodos')->findOrFail($ciclo_id);
        return response()->json($ciclo->periodos);
    }

    /**
     * Retorna datos JSON filtrados.
     */
  public function getData(Request $request)
{
    $grupo_id = $request->grupo_id;
    $periodo_id = $request->periodo_id;
    $tipo = $request->input('tipo'); 

    $periodo = Periodo::find($periodo_id);
    $periodoEstado = $periodo ? $periodo->estado : 'CERRADO';

    // Cargar el grupo con su ciclo para saber si es historico
    $grupo = Grupo::with(['grado', 'cicloEscolar'])->findOrFail($grupo_id);

    // Si el grupo es del ciclo activo, filtrar por es_actual = 1
    // Si es de un ciclo cerrado (historico), traer todos los asignados
    $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();
    $esHistorico = $cicloActivo && $grupo->ciclo_escolar_id !== $cicloActivo->ciclo_escolar_id;

    $grupo->load(['alumnos' => function ($q) use ($esHistorico) {
        if (!$esHistorico) {
            $q->where('es_actual', 1);
        }
        // Si es historico, no filtrar por es_actual
    }]);

        $campos = collect([]);
        $materias = collect([]);

        // --- FILTRADO INTELIGENTE (NIVEL + NOMBRE) ---
        if ($tipo) {
            if ($tipo === 'campos_formativos') {
                $nivelId = $grupo->grado->nivel_id;

                $campos = CampoFormativo::where('nivel_id', $nivelId)
                    ->where(function ($query) {
                        $query->where('nombre', 'LIKE', '%Lenguajes%')
                              ->orWhere('nombre', 'LIKE', '%Saberes%')
                              ->orWhere('nombre', 'LIKE', '%Ética%')
                              ->orWhere('nombre', 'LIKE', '%Humano%');
                    })
                    ->get();

            } elseif ($tipo === 'lengua_extranjera') {
                $materias = $grupo->grado->materias()->where('nombre', 'LIKE', '%Lengua Extranjera%')->get();
            } elseif ($tipo === 'artes') {
                $materias = $grupo->grado->materias()->where('nombre', 'LIKE', '%Artes%')->get();
            } elseif ($tipo === 'educacion_fisica') {
                $materias = $grupo->grado->materias()->where('nombre', 'LIKE', '%Educación Física%')->get();
            } elseif ($tipo === 'socioemocional') {
                $materias = $grupo->grado->materias()->where('nombre', 'LIKE', '%Socioemocional%')->get();
            }
        } else {
            // Lógica Admin
            $nivelId = $grupo->grado->nivel_id;
            $campos = CampoFormativo::where('nivel_id', $nivelId)
                ->where(function ($query) {
                    $query->where('nombre', 'LIKE', '%Lenguajes%')
                          ->orWhere('nombre', 'LIKE', '%Saberes%')
                          ->orWhere('nombre', 'LIKE', '%Ética%')
                          ->orWhere('nombre', 'LIKE', '%Humano%');
                })
                ->get();

            // Fallback si no encuentra nada
            if ($campos->isEmpty()) {
                $campos = CampoFormativo::take(4)->get();
            }

            $materias = $grupo->grado->materias()
                ->where(function ($q) {
                    $q->where('nombre', 'LIKE', '%Artes%')
                      ->orWhere('nombre', 'LIKE', '%Educación Física%')
                      ->orWhere('nombre', 'LIKE', '%Lengua Extranjera%')
                      ->orWhere('nombre', 'LIKE', '%Socioemocional%');
                })
                ->get();
        }

        $alumnoIds = $grupo->alumnos->pluck('alumno_id');
        $query = PdaEvaluacion::where('periodo_id', $periodo_id)->whereIn('alumno_id', $alumnoIds);
        
        if ($tipo === 'campos_formativos') {
             $query->whereNotNull('campo_formativo_id');
        } elseif ($tipo && $tipo !== 'campos_formativos') {
             $query->whereNotNull('materia_id');
        }
        
        $evaluaciones = $query->get();

        return response()->json([
            'alumnos' => $grupo->alumnos->sortBy('apellido_paterno')->values(),
            'campos' => $campos,
            'materias' => $materias,
            'evaluaciones' => $evaluaciones,
            'periodo_estado' => $periodoEstado
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->input('evaluaciones');
        $periodo_id = $request->input('periodo_id');
        
        $periodo = Periodo::findOrFail($periodo_id);
        $user = Auth::user();

        if ($user->rol === 'MAESTRO' && $periodo->estado !== 'ABIERTO') {
            return response()->json(['error' => 'El periodo está cerrado.'], 403);
        }

        DB::beginTransaction();
        try {
            foreach ($datos as $item) {
                if (empty(trim($item['texto']))) {
                    PdaEvaluacion::where([
                        'alumno_id' => $item['alumno_id'],
                        'periodo_id' => $periodo_id,
                        'campo_formativo_id' => $item['campo_formativo_id'] ?? null,
                        'materia_id' => $item['materia_id'] ?? null,
                    ])->delete();
                    continue;
                }

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