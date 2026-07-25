<?php

namespace App\Http\Controllers;

use App\Services\CycleTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CycleTransitionController extends Controller
{
    protected $transitionService;

    public function __construct(CycleTransitionService $transitionService)
    {
        $this->transitionService = $transitionService;
        
      
    }

public function index()
    {
        // 1. Ciclo origen: El que está actualmente en curso
        $oldCycle = \App\Models\CicloEscolar::where('estado', 'ACTIVO')
                                            ->orderBy('fecha_inicio', 'desc')
                                            ->first();

        if (!$oldCycle) {
            return redirect()->route('admin.ciclo-escolar.index')
                             ->with('error', 'No hay ningún ciclo activo para cerrar.');
        }

        // 2. Ciclo destino: El cronológicamente siguiente al activo
        $newCycle = \App\Models\CicloEscolar::where('fecha_inicio', '>', $oldCycle->fecha_inicio)
                                            ->orderBy('fecha_inicio', 'asc')
                                            ->first();

        if (!$newCycle) {
            return redirect()->route('admin.ciclo-escolar.index')
                             ->with('error', 'Debes crear el nuevo ciclo escolar antes de iniciar la transición.');
        }

        // 3. Obtener la matriz de transición
        $matrixData = $this->transitionService->getPromotionMatrix(
            $oldCycle->ciclo_escolar_id,
            $newCycle->ciclo_escolar_id
        );
        $newGroups = \App\Models\Grupo::where('ciclo_escolar_id', $newCycle->ciclo_escolar_id)
                              ->select('grupo_id', 'grado_id', 'nombre_grupo')
                              ->get();
        return view('admin.ciclo-escolar.transicion', compact('oldCycle', 'newCycle', 'matrixData', 'newGroups'));
    }

 public function ejecutarTransicion(Request $request)
{
    $request->validate([
        'old_cycle_id' => 'required|exists:ciclo_escolars,ciclo_escolar_id', 
        'new_cycle_id' => 'required|exists:ciclo_escolars,ciclo_escolar_id',
        
        'mappings' => 'required|array',
        'mappings.*.old_group_id' => 'required|exists:grupos,grupo_id',
        
        // Validación para 6to
        'mappings.*.graduating_student_ids' => 'nullable|array',
        'mappings.*.graduating_student_ids.*' => 'integer|exists:alumnos,alumno_id',
        
        // Validación para regulares
        'mappings.*.students' => 'nullable|array',
        'mappings.*.students.*.alumno_id' => 'required|integer|exists:alumnos,alumno_id',
        'mappings.*.students.*.new_group_id' => 'nullable|integer|exists:grupos,grupo_id',
    ]);

    try {
        $newCycle = $this->transitionService->executeTransition(
            $request->old_cycle_id,
            $request->new_cycle_id,
            $request->mappings
        );

        return response()->json(['success' => true, 'message' => 'Transición exitosa al ciclo: ' . $newCycle->nombre]);
        
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
}