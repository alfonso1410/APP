<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Periodo;
use App\Models\CicloEscolar;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule; // Importar Rule

class PeriodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request): View
    {
        $query = Periodo::with('cicloEscolar') // Cargar la relación
                        ->orderBy('ciclo_escolar_id', 'desc') // Agrupar visualmente por ciclo
                        ->orderBy('fecha_inicio', 'asc'); // Ordenar dentro del ciclo

        // Filtrar si se pasa un ciclo_escolar_id en la URL
        if ($request->has('ciclo_escolar_id')) {
            $query->where('ciclo_escolar_id', $request->ciclo_escolar_id);
        }

        $periodos = $query->get();

        // Obtener SOLO el ciclo activo para pasarlo al formulario de creación
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        return view('admin.periodo.index', [
            'periodos' => $periodos,
            'cicloActivo' => $cicloActivo, // Para el modal de creación
            'cicloFiltradoId' => $request->ciclo_escolar_id // Para saber si estamos filtrando
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ciclo_escolar_id' => 'required|integer|exists:ciclo_escolars,ciclo_escolar_id', // o ciclo_escolars
            // Validar que el nombre sea único DENTRO de ese ciclo escolar
            'nombre' => ['required','string','max:50', Rule::unique('periodos','nombre')->where('ciclo_escolar_id', $request->ciclo_escolar_id)],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            // 'estado' => ['required', Rule::in(['ABIERTO', 'CERRADO'])], // Si incluyes el select de estado
        ]);

        // Validar que las fechas del periodo estén dentro del ciclo escolar
        $ciclo = CicloEscolar::find($validated['ciclo_escolar_id']);
        if (!$ciclo || $validated['fecha_inicio'] < $ciclo->fecha_inicio || $validated['fecha_fin'] > $ciclo->fecha_fin) {
             return back()->withErrors(['fecha_inicio' => 'Las fechas del periodo deben estar dentro del rango del ciclo escolar.'])->withInput();
        }

        Periodo::create([
            'ciclo_escolar_id' => $validated['ciclo_escolar_id'],
            'nombre' => $validated['nombre'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'estado' => 'ABIERTO', // Default o $validated['estado'] si usas el select
        ]);

        return redirect()->route('admin.periodos.index')
                         ->with('success', 'Periodo creado exitosamente.');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request, Periodo $periodo): RedirectResponse
    {
        $validated = $request->validate([
            // Validar unique ignorando el periodo actual DENTRO del mismo ciclo
            'nombre' => ['required','string','max:50', Rule::unique('periodos','nombre')
                            ->where('ciclo_escolar_id', $periodo->ciclo_escolar_id) // Solo dentro del mismo ciclo
                            ->ignore($periodo->periodo_id, 'periodo_id')],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => ['required', Rule::in(['ABIERTO', 'CERRADO'])],
            // 'periodo_id' => 'required|integer', // Necesario si usas old() para reabrir modal
        ]);

        // Validar que las fechas del periodo estén dentro del ciclo escolar asociado
        // No permitimos cambiar el ciclo_escolar_id al editar
        $ciclo = $periodo->cicloEscolar; // Carga la relación
        if (!$ciclo || $validated['fecha_inicio'] < $ciclo->fecha_inicio || $validated['fecha_fin'] > $ciclo->fecha_fin) {
             return back()->withErrors(['fecha_inicio' => 'Las fechas del periodo deben estar dentro del rango del ciclo escolar.'])->withInput();
        }

        $periodo->update($validated);

        // Redirigir de vuelta a la lista (posiblemente filtrada si venía de ahí)
        $redirectRoute = $request->input('redirect_back_filter')
            ? route('admin.periodos.index', ['ciclo_escolar_id' => $periodo->ciclo_escolar_id])
            : route('admin.periodos.index');

        return redirect($redirectRoute)
                         ->with('success', 'Periodo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Periodo $periodo): RedirectResponse
    {
        // 1. Verificar dependencias (Añade más relaciones si es necesario)
        //    Asumimos que Periodo tiene relaciones 'calificaciones()' y 'asistencias()'
        if ($periodo->calificaciones()->exists() || $periodo->asistencias()->exists()) {
            // Si tiene dependencias, NO eliminar. Redirigir con error.
            return redirect()->back() // O a ->route('admin.periodos.index')
                             ->with('error', 'No se puede eliminar el periodo "'.$periodo->nombre.'" porque tiene calificaciones o asistencias asociadas.');
        } else {
            // Si NO tiene dependencias, se puede eliminar.
            $periodo->delete();
            return redirect()->route('admin.periodos.index')
                             ->with('success', 'Periodo eliminado permanentemente.');
        }
    }
}
