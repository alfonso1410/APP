<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GuardarCalificacionActividadRequest;
use App\Http\Requests\StoreActividadRequest;
use App\Models\ActividadMateria;
use App\Models\CalificacionActividad;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\MateriaCriterio;
use App\Models\Periodo;
use App\Models\Nivel;
use App\Models\Grado;
use App\Services\ActividadService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Requests\getDatosTablaActividadesRequest;
use Illuminate\Support\Facades\DB;
class ActividadController extends Controller
{
    protected ActividadService $actividadService;

    public function __construct(ActividadService $actividadService)
    {
        $this->actividadService = $actividadService;
    }

    public function index(): View
    {
       $periodos = Periodo::whereHas('cicloEscolar', function ($query) {
        $query->where('estado', 'ACTIVO'); // o where('estado', 'ACTIVO') según el nombre de tu columna
    })
    ->orderBy('periodo_id')
    ->get();
        $niveles = Nivel::orderBy('nivel_id')->get();
        //$grados = Grado::orderBy('grado_id', 'desc')->get();
        return view('admin.actividades_diarias.index_actividades_diarias', compact('periodos', 'niveles'));
    }

    public function store(StoreActividadRequest $request): JsonResponse
    {
        try {
            $actividad = $this->actividadService->crearActividad(
                $request->validated(),
                $request->input('grupos_replicar', [])
            );

            return response()->json([
                'success'   => true,
                'mensaje'   => 'Actividad creada con éxito.',
                'actividad' => $actividad,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }
public function capturaMaestro(): View
    {
        $periodos = Periodo::whereHas('cicloEscolar', fn($q) => $q->where('estado', 'ACTIVO'))
            ->orderBy('periodo_id')
            ->get();

        $misGrupos = $this->actividadService->getGruposDocente(auth()->id());

        return view('maestro.actividades_diarias.index', compact('periodos', 'misGrupos'));
    }

    /**
     * Endpoint JSON para métricas de tarjetas del maestro.
     */
    public function getMateriasResumenMaestro(Request $request): JsonResponse
    {
        $request->validate([
            'grupo_id'   => ['required', 'integer', 'exists:grupos,grupo_id'],
            'periodo_id' => ['required', 'integer', 'exists:periodos,periodo_id'],
        ]);

        $tarjetas = $this->actividadService->getMateriasResumenMaestro(
            (int) $request->grupo_id,
            (int) $request->periodo_id,
            auth()->id()
        );

        return response()->json($tarjetas);
    }

    public function update(Request $request, int $id): JsonResponse
{
    $request->validate([
        'nombre_actividad'    => ['required', 'string', 'max:150'],
        'descripcion'         => ['nullable', 'string'],
        'materia_criterio_id' => ['required', 'exists:materia_criterios,materia_criterio_id'],
        'fecha_actividad'     => ['required', 'date'],
        'valor_maximo'        => ['required', 'numeric', 'min:0.01', 'max:100'],
    ]);

    try {
        $actividad = $this->actividadService->actualizarActividad($id, $request->all());

        return response()->json([
            'success'   => true,
            'mensaje'   => 'Actividad actualizada correctamente.',
            'actividad' => $actividad,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'mensaje' => $e->getMessage(),
        ], 422);
    }
}
    public function destroy(int $id): JsonResponse
{
    try {
        $resultado = $this->actividadService->eliminarActividad($id, auth()->id());

        return response()->json([
            'success' => true,
            'mensaje' => $resultado['mensaje'],
            'quedan_actividades' => $resultado['quedan_actividades'],
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'mensaje' => $e->getMessage(),
        ], 422);
    }
}


    public function getDatosTabla(getDatosTablaActividadesRequest $request): JsonResponse
    {
        $datos = $this->actividadService->getDatosTabla(
            (int)$request->grupo_id,
            (int)$request->materia_id,
            (int)$request->periodo_id
            
        );
        return response()->json($datos);
    }


   public function guardarCalificacion(GuardarCalificacionActividadRequest $request): JsonResponse
{
    try {
        $calificaciones = $request->validated()['calificaciones'];
        $actividadIdsModificadas = [];

        DB::transaction(function () use ($calificaciones, &$actividadIdsModificadas) {
            foreach ($calificaciones as $item) {
                CalificacionActividad::updateOrCreate(
                    [
                        'actividad_id' => $item['actividad_id'],
                        'alumno_id'    => $item['alumno_id'],
                    ],
                    [
                        'calificacion_obtenida' => $item['estado_entrega'] === 'ENTREGADO' ? $item['calificacion_obtenida'] : null,
                        'estado_entrega'        => $item['estado_entrega'],
                        'observaciones'         => $item['observaciones'] ?? null,
                        'updated_at'            => now(),
                    ]
                );

                $actividadIdsModificadas[] = $item['actividad_id'];
            }

            // Marcar las actividades afectadas como desincronizadas
            if (!empty($actividadIdsModificadas)) {
                ActividadMateria::whereIn('actividad_id', array_unique($actividadIdsModificadas))->touch();
            }
        });

        return response()->json([
            'success' => true,
            'mensaje' => 'Cambios guardados correctamente.',
            'total'   => count($calificaciones)
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al procesar el lote de calificaciones: ' . $e->getMessage(),
        ], 500);
    }
}

public function captura(): View
{
    $periodos = Periodo::whereHas('cicloEscolar', function ($query) {
        $query->where('estado', 'ACTIVO');
    })
    ->orderBy('periodo_id')
    ->get();

    $niveles = Nivel::orderBy('nivel_id')->get();

    return view('admin.actividades_diarias.captura', compact('periodos', 'niveles'));
}

public function getResumen(Request $request): JsonResponse
{
    $request->validate([
        'periodo_id' => ['required', 'integer', 'exists:periodos,periodo_id'],
        'nivel_id'   => ['nullable', 'string'],  // ← STRING siempre
        'grado_id'   => ['nullable', 'integer', 'exists:grados,grado_id'],
        'grupo_id'   => ['nullable', 'integer', 'exists:grupos,grupo_id'],
    ]);

    $resumen = $this->actividadService->getResumenEstado(
        (int) $request->periodo_id,
        $request->filled('nivel_id') ? $request->input('nivel_id') : null,  // ← SIN (int)
        $request->filled('grado_id') ? (int) $request->grado_id : null,
        $request->filled('grupo_id') ? (int) $request->grupo_id : null
    );

    return response()->json($resumen);
}

    public function getGruposReplicables(Request $request): JsonResponse
{
    $request->validate([
        'grado_id' => ['required', 'integer'],
        'grupo_id_actual' => ['required', 'integer'],
    ]);

    $grupos = Grupo::where('grado_id', $request->grado_id)
        ->where('grupo_id', '!=', $request->grupo_id_actual)
        ->get(['grupo_id', 'nombre_grupo']);

    return response()->json($grupos);
}


public function getDetallesPendientes(Request $request): JsonResponse
{
    $request->validate([
        'periodo_id' => ['required', 'integer', 'exists:periodos,periodo_id'],
    ]);

    $pendientes = $this->actividadService->getDetallesPendientes((int)$request->periodo_id);

    return response()->json($pendientes);
}


    public function sincronizar(Request $request): JsonResponse
    {
        $request->validate([
            'grupo_id'   => ['required', 'integer'],
            'materia_id' => ['required', 'integer'],
            'periodo_id' => ['required', 'integer'],
        ]);

        try {
            $resumen = $this->actividadService->sincronizarTodoElGrupo(
                (int)$request->grupo_id,
                (int)$request->materia_id,
                (int)$request->periodo_id
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Calificaciones calculadas y sincronizadas con éxito.',
                'resumen' => $resumen,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], 422);
        }
    }
}
