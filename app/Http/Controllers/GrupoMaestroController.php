<?php

namespace App\Http\Controllers;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\Request;
class GrupoMaestroController extends Controller
{
    /**
     * Muestra la LISTA de maestros titulares YA ASIGNADOS al grupo.
     * (Esta es la vista de TABLA)
     */
    public function index(Grupo $grupo)
    {
        // 1. Obtenemos solo los maestros que ya están en la tabla pivote
        $maestros = $grupo->maestrosTitulares()->orderBy('name')->get();

        // 2. Mandamos los datos a la vista de ÍNDICE (la tabla)
        return view('grupos.maestros-index', compact('grupo', 'maestros'));
    }

    /**
     * Muestra el FORMULARIO para asignar/editar maestros titulares.
     * (Esta es la vista de CHECKBOXES)
     */
    public function create(Grupo $grupo)
    {
        // 1. Obtenemos TODOS los maestros disponibles
        $maestrosDisponibles = User::maestros()->orderBy('name')->get();

        // 2. Obtenemos los IDs de los que YA están asignados
        $idsMaestrosAsignados = $grupo->maestrosTitulares()
                                      ->pluck('users.id')
                                      ->toArray();

        // 3. Mandamos los datos a la vista de FORMULARIO (la de checkboxes)
        return view('grupos.maestros', compact(
            'grupo', 
            'maestrosDisponibles', 
            'idsMaestrosAsignados'
        ));
    }

    /**
     * Guarda la asignación del formulario.
     * (Esta función ya estaba correcta)
     */
    public function store(Request $request, Grupo $grupo)
    {
        $request->validate([
            'maestros'   => 'nullable|array',
            'maestros.*' => 'exists:users,id',
        ]);

        $grupo->maestrosTitulares()->sync($request->input('maestros', []));

        // Redirigimos de vuelta a la LISTA
        return redirect()->route('admin.grupos.maestros.index', $grupo)
                         ->with('success', 'Maestros titulares actualizados.');
    }
}