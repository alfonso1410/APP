<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Nivel;
use App\Models\Grupo;

class GradoController extends Controller
{
    /**
     * Muestra una lista de grados y sus grupos activos, filtrada por nivel.
     */
      public function index(Request $request): View
    {
        $view_mode = $request->input('view_mode', 'regular');
        $niveles = Nivel::all();
        $search = $request->input('search');

      if ($view_mode === 'extracurricular') {
        // MODO EXTRACURRICULAR
        $grados = Grado::where('tipo_grado', 'EXTRA')
            // --- LÍNEA MODIFICADA ---
            // Le decimos que cargue también la relación del mapeo
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

    }else {
            // MODO REGULAR: Busca los grados de tipo REGULAR por nivel
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

    public function create(): View
    {
        // Obtenemos todos los niveles para pasarlos al selector
        $niveles = Nivel::all(); 

        // Pasamos la variable 'niveles' a la vista
        return view('grados.create', compact('niveles'));
    }

    public function update(Request $request, Grado $grado)
{
    // Las reglas de validación se aplican aquí
    $validatedData = $request->validate([
        'nombre' => 'required|string|max:50',
        'nivel_id' => 'required|exists:niveles,nivel_id',
        // No validamos 'grado_id' porque solo es un identificador
    ]);

    $grado->update($validatedData);

    // Redirección inteligente (mejora)
    $redirectParams = $grado->tipo_grado === 'REGULAR'
        ? ['nivel' => $grado->nivel_id]
        : ['view_mode' => 'extracurricular'];

    return redirect()->route('grados.index', $redirectParams)
                     ->with('success', 'Grado actualizado exitosamente.');
}

public function store(Request $request)
{
    // 1. Validamos los datos, incluyendo el 'tipo_grado' oculto.
    $validated = $request->validate([
        'nombre' => 'required|string|max:50',
        'nivel_id' => 'required|exists:niveles,nivel_id',
        'tipo_grado' => 'required|string|in:REGULAR,EXTRA',
    ]);

    // 2. Lógica para calcular el 'orden' automáticamente.
    // Busca el 'orden' más alto para ese nivel y tipo, y le suma 1.
    $orden = Grado::where('nivel_id', $validated['nivel_id'])
                  ->where('tipo_grado', $validated['tipo_grado'])
                  ->max('orden') + 1;

    // 3. Creamos el grado con todos los datos.
    $grado = Grado::create([
        'nombre' => $validated['nombre'],
        'nivel_id' => $validated['nivel_id'],
        'tipo_grado' => $validated['tipo_grado'],
        'orden' => $orden,
    ]);

    // 4. Redirección inteligente.
    $redirectParams = $grado->tipo_grado === 'REGULAR' 
        ? ['nivel' => $grado->nivel_id] 
        : ['view_mode' => 'extracurricular'];

    return redirect()->route('grados.index', $redirectParams)
                     ->with('success', 'Registro creado exitosamente.');
}


public function showMapeo(Grado $grado)
{
    // Nos aseguramos de que solo se pueda mapear un grado de tipo EXTRA
    if ($grado->tipo_grado !== 'EXTRA') {
        abort(404);
    }

    // 1. Obtenemos el ID del nivel del grado extracurricular que estamos editando.
    $nivelIdDelExtra = $grado->nivel_id;

    // 2. Modificamos la consulta para que traiga solo los grados regulares
    //    QUE PERTENECEN A ESE MISMO NIVEL.
    $gradosRegulares = Grado::where('tipo_grado', 'REGULAR')
                          ->where('nivel_id', $nivelIdDelExtra) // <-- ¡ESTA ES LA LÍNEA CLAVE!
                          ->orderBy('orden')
                          ->get();

    // 3. Obtenemos los IDs de los grados que ya están mapeados (esto no cambia).
    $idsMapeados = $grado->gradosRegularesMapeados()->pluck('grados.grado_id')->toArray();

    return view('grados.mapeo', compact('grado', 'gradosRegulares', 'idsMapeados'));
}

    /**
     * Guarda el mapeo de grados en la base de datos.
     */
    public function storeMapeo(Request $request, Grado $grado)
    {
        // Validamos que los IDs enviados realmente existan en la tabla de grados
        $request->validate([
            'grados_regulares' => 'nullable|array',
            'grados_regulares.*' => 'exists:grados,grado_id',
        ]);

        $gradosIds = $request->input('grados_regulares', []);
        
        // Usamos sync() para actualizar la tabla pivote.
        // Laravel automáticamente añadirá los nuevos, quitará los desmarcados
        // y dejará los que ya estaban. Es la forma más eficiente.
        $grado->gradosRegularesMapeados()->sync($gradosIds);

        return redirect()->route('grados.index', ['view_mode' => 'extracurricular'])
                         ->with('success', 'Mapeo de grados actualizado exitosamente.');
    }

    public function destroy(Grado $grado)
{
    // 1. Verificación de seguridad: ¿Este grado tiene grupos asociados?
    if ($grado->grupos()->count() > 0) {
        // Si tiene grupos, no lo borramos y regresamos con un mensaje de error.
        return back()->with('error', 'No se puede eliminar este grado porque tiene grupos asociados.');
    }

    // 2. Si no tiene grupos, procedemos a eliminarlo.
    $grado->delete();

    // 3. Redirigimos con un mensaje de éxito.
    return back()->with('success', 'Grado eliminado exitosamente.');
}
}