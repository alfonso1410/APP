<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Grupo;
use Illuminate\Validation\Rule;

class GradoController extends Controller
{
    /**
     * Muestra una lista de grados y sus grupos regulares activos, filtrada por nivel.
     */
    public function index(Request $request): View
    {
        $view_mode = $request->input('view_mode', 'regular');
        $niveles = Nivel::all();
        $search = $request->input('search');

        if ($view_mode === 'extracurricular') {
            // MODO EXTRACURRICULAR
            $grados = Grado::where('tipo_grado', 'EXTRA')
                ->with(['grupos', 'gradosRegularesMapeados']) 
                ->when($search, fn($q, $s) => $q->where('nombre', 'like', "%{$s}%"))
                ->orderBy('orden')
                ->get();

            return view('grados.index', [
                'view_mode' => 'extracurricular',
                'grados' => $grados,
                'niveles' => $niveles,
                'nivel_id' => 0,
                'search' => $search,
            ]);
        } else {
            // MODO REGULAR
            $nivel_id = $request->input('nivel', 1);

            $grados = Grado::where('tipo_grado', 'REGULAR')
                ->where('nivel_id', $nivel_id)
                ->with(['grupos' => function ($query) {
                    $query->where('estado', 'ACTIVO')->where('tipo_grupo', 'REGULAR');
                }])
                ->when($search, fn($q, $s) => $q->where('nombre', 'like', "%{$s}%"))
                ->orderBy('orden')
                ->get();

            return view('grados.index', [
                'view_mode' => 'regular',
                'grados' => $grados,
                'nivel_id' => (int)$nivel_id,
                'search' => $search,
                'niveles' => $niveles,
            ]);
        }
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

        $redirectParams = $grado->tipo_grado === 'REGULAR'
            ? ['nivel' => $grado->nivel_id]
            : ['view_mode' => 'extracurricular'];

        return redirect()->route('admin.grados.index', $redirectParams)
                         ->with('success', 'Grado actualizado exitosamente.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'nivel_id' => 'required|exists:niveles,nivel_id',
            'tipo_grado' => 'required|string|in:REGULAR,EXTRA',
        ]);

        $orden = Grado::where('nivel_id', $validated['nivel_id'])
                      ->where('tipo_grado', $validated['tipo_grado'])
                      ->max('orden') + 1;

        $grado = Grado::create([
            'nombre' => $validated['nombre'],
            'nivel_id' => $validated['nivel_id'],
            'tipo_grado' => $validated['tipo_grado'],
            'orden' => $orden,
        ]);

        $redirectParams = $grado->tipo_grado === 'REGULAR' 
            ? ['nivel' => $grado->nivel_id] 
            : ['view_mode' => 'extracurricular'];

        return redirect()->route('admin.grados.index', $redirectParams)
                         ->with('success', 'Registro creado exitosamente.');
    }

    public function showMapeo(Grado $grado)
    {
        if ($grado->tipo_grado !== 'EXTRA') {
            abort(404);
        }

        $nivelIdDelExtra = $grado->nivel_id;

        $gradosRegulares = Grado::where('tipo_grado', 'REGULAR')
                                ->where('nivel_id', $nivelIdDelExtra)
                                ->orderBy('orden')
                                ->get();

        $idsMapeados = $grado->gradosRegularesMapeados()->pluck('grados.grado_id')->toArray();

        return view('grados.mapeo', compact('grado', 'gradosRegulares', 'idsMapeados'));
    }

    /**
     * Guarda el mapeo de grados en la base de datos.
     */
    public function storeMapeo(Request $request, Grado $grado)
    {
        $request->validate([
            'grados_regulares' => 'nullable|array',
            'grados_regulares.*' => 'exists:grados,grado_id',
        ]);

        $gradosIds = $request->input('grados_regulares', []);

        $grado->gradosRegularesMapeados()->sync($gradosIds);

        return redirect()->route('admin.grados.index', ['view_mode' => 'extracurricular'])
                         ->with('success', 'Mapeo de grados actualizado exitosamente.');
    }

    public function destroy(Grado $grado)
    {
        if ($grado->grupos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar este grado porque tiene grupos asociados.');
        }

        $grado->delete();

        return back()->with('success', 'Grado eliminado exitosamente.');
    }
}
