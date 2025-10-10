<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradoController extends Controller
{
    /**
     * Muestra una lista de grados y sus grupos regulares activos, filtrada por nivel.
     */
    public function index(Request $request): View
    {
        // 1. Validar la entrada para mayor seguridad
        $validated = $request->validate([
            'nivel' => 'nullable|integer|exists:niveles,nivel_id',
            'search' => 'nullable|string|max:50',
        ]);

        // 2. Establecer 'Preescolar' (ID 1) como filtro por defecto si no se recibe uno
        $nivel_id = $validated['nivel'] ?? 1;
        $search = $validated['search'] ?? null;

        // 3. Consulta los grados del nivel seleccionado
        $grados = Grado::query()
            ->where('nivel_id', $nivel_id)
            ->when($search, function ($query, $search) {
                return $query->where('nombre', 'like', "%{$search}%");
            })
            ->with(['grupos' => function ($query) {
                // ✅ CAMBIO 1: Se ajusta el filtro a tu columna `tipo_grupo`.
                // Asumimos que los grupos que quieres mostrar tienen el valor 'REGULAR'.
                $query->where('estado', 'ACTIVO')
                      ->where('tipo_grupo', 'REGULAR'); 
            }])
            // ✅ CAMBIO 2: Se ordena por `grado_id` en lugar de la columna `orden` que no existe.
            // Esto mantendrá el orden lógico de los grados.
            ->orderBy('grado_id') 
            ->get();
            
        $niveles = Nivel::all();

        // 4. Pasamos los datos a la vista
        return view('grados.index', [
            'grados' => $grados,
            'niveles' => $niveles,
            'nivel_id' => $nivel_id,
            'search' => $search,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo grado.
     */
    public function create(): View
    {
        $niveles = Nivel::all(); 
        return view('grados.create', compact('niveles'));
    }

    /**
     * Actualiza un grado existente en la base de datos.
     */
    public function update(Request $request, Grado $grado)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:50',
            'nivel_id' => 'required|exists:niveles,nivel_id',
        ]);

        $grado->update($validatedData);

        return redirect()->route('grados.index', ['nivel' => $grado->nivel_id])
                         ->with('success', 'Grado actualizado exitosamente.');
    }
}