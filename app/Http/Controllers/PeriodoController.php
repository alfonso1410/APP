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
     * 🛑 Se espera el parámetro de la ruta anidada: {ciclo_escolar}
     */
    public function index(Request $request, $ciclo_escolar): View // 🛑 CORRECCIÓN: Inyectamos el ID del Ciclo Escolar
    {
        $cicloEscolarId = $ciclo_escolar;

        $query = Periodo::with('cicloEscolar')
                         ->where('ciclo_escolar_id', $cicloEscolarId) // 🛑 FILTRAMOS por el ID de la ruta
                         ->orderBy('fecha_inicio', 'asc'); 

        $periodos = $query->get();

        // Obtener SOLO el ciclo activo para pasarlo al formulario de creación
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        // El ID del ciclo que estamos viendo
        $cicloFiltradoId = $cicloEscolarId; 

        return view('admin.periodo.index', [
            'periodos' => $periodos,
            'cicloActivo' => $cicloActivo, 
            'cicloFiltradoId' => $cicloFiltradoId 
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No se usa en tu configuración actual con modal
    }

    /**
     * Store a newly created resource in storage.
     * 🛑 Se espera el parámetro de la ruta anidada: {ciclo_escolar}
     */
    public function store(StorePeriodoRequest $request, $ciclo_escolar): RedirectResponse // 🛑 CORRECCIÓN: Inyectamos el ID
    {
        // Si llegamos aquí, la validación (incluyendo unicidad, rango del ciclo y NO solapamiento) ha pasado.
        $validated = $request->validated();
        
        $estado = $validated['estado'] ?? 'ABIERTO'; 

        // NOTA: El campo ciclo_escolar_id viene en el $validated del FormRequest
        Periodo::create([
            'ciclo_escolar_id' => $validated['ciclo_escolar_id'],
            'nombre' => $validated['nombre'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'estado' => $estado, 
        ]);

        // 🛑 CORRECCIÓN: Redireccionamos a la nueva ruta anidada, pasando el ID del ciclo escolar.
        return redirect()->route('admin.ciclo-escolar.periodos.index', ['ciclo_escolar' => $ciclo_escolar])
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
     * 🛑 Se espera el parámetro de la ruta anidada: {ciclo_escolar}
     * NOTA: Laravel inyectará el Periodo automáticamente.
     */
    public function update(UpdatePeriodoRequest $request, $ciclo_escolar, Periodo $periodo): RedirectResponse // 🛑 CORRECCIÓN: Inyectamos el ID
    {
        // Si llegamos a esta línea, la validación (solapamiento, rango, unicidad) ha pasado.
        $validated = $request->validated();
        
        $periodo->update($validated);

        // 🛑 CORRECCIÓN: Redirigimos a la nueva ruta anidada.
        $redirectRoute = route('admin.ciclo-escolar.periodos.index', ['ciclo_escolar' => $ciclo_escolar]);

        return redirect($redirectRoute)->with('success', 'Periodo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * 🛑 Se espera el parámetro de la ruta anidada: {ciclo_escolar}
     */
    public function destroy($ciclo_escolar, Periodo $periodo): RedirectResponse // 🛑 CORRECCIÓN: Inyectamos el ID
    {
        // 1. Verificar dependencias
        if ($periodo->calificaciones()->exists() || $periodo->asistencias()->exists()) {
            // Si tiene dependencias, NO eliminar. Redirigir con error.
            return redirect()->back()
                             ->with('error', 'No se puede eliminar el periodo "'.$periodo->nombre.'" porque tiene calificaciones o asistencias asociadas.');
        } else {
            // Si NO tiene dependencias, se puede eliminar.
            $periodo->delete();
            // 🛑 CORRECCIÓN: Redirigimos a la nueva ruta anidada.
            return redirect()->route('admin.ciclo-escolar.periodos.index', ['ciclo_escolar' => $ciclo_escolar])
                             ->with('success', 'Periodo eliminado permanentemente.');
        }
    }
}