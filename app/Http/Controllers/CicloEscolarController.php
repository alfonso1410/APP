<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CicloEscolar;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse; // <-- Importar para Redirects
use Illuminate\Validation\Rule;
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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:ciclo_escolars,nombre',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            // 'form_type' => 'required|string', // Quitado si no lo usas específicamente
        ]);

        // --- LÓGICA PARA ASEGURAR SOLO UN CICLO ACTIVO ---
        // Si se intenta crear uno nuevo como ACTIVO (que es el default),
        // ponemos los demás como CERRADO primero.
        CicloEscolar::where('estado', 'ACTIVO')->update(['estado' => 'CERRADO']);
        // --- FIN LÓGICA ---

        CicloEscolar::create([
            'nombre' => $validated['nombre'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'estado' => 'ACTIVO', // El nuevo siempre es ACTIVO
        ]);

        return redirect()->route('admin.ciclo-escolar.index')
                         ->with('success', 'Ciclo Escolar creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     * (Usaremos un modal en index, pero este método puede ser útil si quieres una página dedicada)
     */
    // public function edit(CicloEscolar $cicloEscolar): View
    // {
    //     // El Route Model Binding funciona porque CicloEscolar tiene $primaryKey
    //     return view('admin.ciclo-escolar.edit', ['ciclo' => $cicloEscolar]);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CicloEscolar $cicloEscolar): RedirectResponse
    {
        $validated = $request->validate([
            // Validar unique ignorando el ciclo actual
            'nombre' => ['required','string','max:50', Rule::unique('ciclo_escolars','nombre')->ignore($cicloEscolar->ciclo_escolar_id, 'ciclo_escolar_id')],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => ['required', Rule::in(['ACTIVO', 'CERRADO'])], // Validar estado
            // 'ciclo_escolar_id' => 'required|integer', // Para saber qué modal reabrir
        ]);

        // --- LÓGICA PARA ASEGURAR SOLO UN CICLO ACTIVO ---
        if ($validated['estado'] === 'ACTIVO' && $cicloEscolar->estado !== 'ACTIVO') {
            // Si estamos activando este ciclo, desactivamos los demás primero.
             CicloEscolar::where('ciclo_escolar_id', '!=', $cicloEscolar->ciclo_escolar_id)
                         ->where('estado', 'ACTIVO')
                         ->update(['estado' => 'CERRADO']);
        }
        // --- FIN LÓGICA ---

        $cicloEscolar->update($validated);

        return redirect()->route('admin.ciclo-escolar.index')
                         ->with('success', 'Ciclo Escolar actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * (O cambia el estado si tiene dependencias)
     */
    public function destroy(CicloEscolar $cicloEscolar): RedirectResponse
    {
        // 1. Verificar dependencias
        if ($cicloEscolar->grupos()->exists() || $cicloEscolar->periodos()->exists()) {
            // Si tiene dependencias, NO eliminar, solo marcar como CERRADO
            if ($cicloEscolar->estado === 'ACTIVO') {
                 // Si era el activo, necesitamos activar otro (o manejar este caso)
                 // Por ahora, solo lo cerramos. Deberás activar otro manualmente.
                $cicloEscolar->update(['estado' => 'CERRADO']);
                return redirect()->route('admin.ciclo-escolar.index')
                                 ->with('warning', 'El Ciclo Escolar tiene grupos o periodos asociados. Se ha marcado como CERRADO en lugar de eliminar.');
            } else {
                 // Si ya estaba cerrado y tiene dependencias, no hacemos nada más
                 return redirect()->route('admin.ciclo-escolar.index')
                                  ->with('info', 'Este Ciclo Escolar ya está cerrado y tiene dependencias.');
            }

        } else {
            // Si NO tiene dependencias, se puede eliminar de forma segura
            $cicloEscolar->delete();
            return redirect()->route('admin.ciclo-escolar.index')
                             ->with('success', 'Ciclo Escolar eliminado permanentemente (no tenía dependencias).');
        }
    }
}