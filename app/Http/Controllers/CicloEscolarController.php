<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CicloEscolar;
// 1. ELIMINA 'Request' y 'Rule' de aquí
// use Illuminate\Http\Request;
// use Illuminate\Validation\Rule;

// 2. IMPORTA LOS NUEVOS FORM REQUESTS
use App\Http\Requests\StoreCicloEscolarRequest;
use App\Http\Requests\UpdateCicloEscolarRequest;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CicloEscolarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $ciclos = CicloEscolar::orderBy('fecha_inicio', 'desc')->get();
        return view('admin.ciclo-escolar.index', ['ciclos' => $ciclos]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // 3. CAMBIA Request por StoreCicloEscolarRequest
    public function store(StoreCicloEscolarRequest $request): RedirectResponse
    {
        // 4. La validación YA PASÓ automáticamente.
        // Si falla, Laravel redirige solo.
        $validatedData = $request->validated();

        // --- LÓGICA PARA ASEGURAR SOLO UN CICLO ACTIVO ---
        CicloEscolar::where('estado', 'ACTIVO')->update(['estado' => 'CERRADO']);
        // --- FIN LÓGICA ---

        CicloEscolar::create([
            'nombre' => $validatedData['nombre'],
            'fecha_inicio' => $validatedData['fecha_inicio'],
            'fecha_fin' => $validatedData['fecha_fin'],
            'estado' => 'ACTIVO', // El nuevo siempre es ACTIVO
        ]);

        return redirect()->route('admin.ciclo-escolar.index')
                         ->with('success', 'Ciclo Escolar creado exitosamente.');
    }


    /**
     * Update the specified resource in storage.
     */
    // 5. CAMBIA Request por UpdateCicloEscolarRequest
    public function update(UpdateCicloEscolarRequest $request, CicloEscolar $cicloEscolar): RedirectResponse
    {
        // 6. La validación YA PASÓ (incluyendo la regla de solapamiento y unique)
        $validatedData = $request->validated();

        // --- LÓGICA PARA ASEGURAR SOLO UN CICLO ACTIVO ---
        if ($validatedData['estado'] === 'ACTIVO' && $cicloEscolar->estado !== 'ACTIVO') {
            CicloEscolar::where('ciclo_escolar_id', '!=', $cicloEscolar->ciclo_escolar_id)
                        ->where('estado', 'ACTIVO')
                        ->update(['estado' => 'CERRADO']);
        }
        // --- FIN LÓGICA ---

        $cicloEscolar->update($validatedData);

        return redirect()->route('admin.ciclo-escolar.index')
                         ->with('success', 'Ciclo Escolar actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CicloEscolar $cicloEscolar): RedirectResponse
    {
        // (Tu lógica de destroy es correcta y no necesita cambios)
        if ($cicloEscolar->grupos()->exists() || $cicloEscolar->periodos()->exists()) {
            if ($cicloEscolar->estado === 'ACTIVO') {
                $cicloEscolar->update(['estado' => 'CERRADO']);
                return redirect()->route('admin.ciclo-escolar.index')
                                 ->with('warning', 'El Ciclo Escolar tiene grupos o periodos asociados. Se ha marcado como CERRADO.');
            } else {
                return redirect()->route('admin.ciclo-escolar.index')
                                 ->with('info', 'Este Ciclo Escolar ya está cerrado y tiene dependencias.');
            }
        } else {
            $cicloEscolar->delete();
            return redirect()->route('admin.ciclo-escolar.index')
                             ->with('success', 'Ciclo Escolar eliminado permanentemente.');
        }
    }
}