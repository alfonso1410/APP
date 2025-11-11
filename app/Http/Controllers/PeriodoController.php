<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Periodo;
use App\Models\CicloEscolar;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// Importamos nuestros FormRequests para centralizar la validación
use App\Http\Requests\StorePeriodoRequest;
use App\Http\Requests\UpdatePeriodoRequest; 

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
        // Generalmente redirige a un formulario. No necesita cambios.
    }

    /**
     * Store a newly created resource in storage.
     * **CRÍTICO: Inyectamos StorePeriodoRequest**
     */
    public function store(StorePeriodoRequest $request): RedirectResponse
    {
        // Si llegamos aquí, la validación (incluyendo unicidad, rango del ciclo y NO solapamiento) ha pasado.
        $validated = $request->validated();
        
        // El estado se obtiene del request si se envía, o asumimos 'ABIERTO' si no se incluyó.
        $estado = $validated['estado'] ?? 'ABIERTO'; 

        Periodo::create([
            'ciclo_escolar_id' => $validated['ciclo_escolar_id'],
            'nombre' => $validated['nombre'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'estado' => $estado, 
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
     * **CRÍTICO: Inyectamos UpdatePeriodoRequest**
     */
    public function update(UpdatePeriodoRequest $request, Periodo $periodo): RedirectResponse
    {
        // Si llegamos a esta línea, la validación (solapamiento, rango, unicidad) ha pasado.
        $validated = $request->validated();
        
        // **NOTA:** Eliminamos la validación manual de rango, ya que la regla la maneja.
        
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
        // 1. Verificar dependencias
        if ($periodo->calificaciones()->exists() || $periodo->asistencias()->exists()) {
            // Si tiene dependencias, NO eliminar. Redirigir con error.
            return redirect()->back()
                             ->with('error', 'No se puede eliminar el periodo "'.$periodo->nombre.'" porque tiene calificaciones o asistencias asociadas.');
        } else {
            // Si NO tiene dependencias, se puede eliminar.
            $periodo->delete();
            return redirect()->route('admin.periodos.index')
                             ->with('success', 'Periodo eliminado permanentemente.');
        }
    }
}