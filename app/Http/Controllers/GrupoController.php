<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrupoController extends Controller
{
    /**
     * Muestra el formulario para crear un nuevo grupo para un grado específico.
     */
    public function create(Request $request): View
    {
        // 1. Validamos que el ID del grado venga en la URL.
        $request->validate(['grado' => 'required|exists:grados,grado_id']);

        // 2. Buscamos el grado para mostrar su nombre en la vista (ej: "Crear grupo para Primero").
        $grado = Grado::findOrFail($request->query('grado'));

        // 3. Devolvemos la vista con la información del grado.
        return view('grupos.create', compact('grado'));
    }

    /**
     * Guarda el nuevo grupo en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validamos los datos del formulario.
        $validated = $request->validate([
            'grado_id'      => 'required|exists:grados,grado_id',
            'nombre_grupo'        => 'required|string|max:50',
            'ciclo_escolar' => 'required|string|max:10',
            'tipo_grupo'    => 'required|string|in:REGULAR,EXTRA',
        ]);

        // 2. Creamos el nuevo grupo con los datos validados.
        Grupo::create($validated);

        // 3. Redirigimos al usuario a la lista de grados con un mensaje de éxito.
        return redirect()->route('grados.index')->with('success', 'Grupo creado exitosamente.');
    }

    // --- Los siguientes métodos los implementaremos cuando necesitemos editar y eliminar ---

    public function show(Grupo $grupo)
    {
        //
    }

    public function edit(Grupo $grupo)
    {
        //
    }

    public function update(Request $request, Grupo $grupo)
    {
        //
    }

    public function destroy(Grupo $grupo)
    {
        //
    }
}