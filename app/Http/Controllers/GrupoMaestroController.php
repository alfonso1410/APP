<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\User;
use App\Models\GrupoTitular; // <-- IMPORTANTE
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <-- IMPORTANTE

class GrupoMaestroController extends Controller
{
    /**
     * Muestra la LISTA de maestros titulares y auxiliares YA ASIGNADOS.
     */
    public function index(Grupo $grupo)
    {
        // 1. Obtenemos las asignaciones (Español e Inglés)
        // Usamos keyBy para acceder fácil en la vista: $asignaciones['ESPAÑOL']
        $asignaciones = $grupo->asignacionesTitulares()
            ->with('titular', 'auxiliar') // Carga los nombres
            ->get()
            ->keyBy('idioma');

        // 2. Mandamos los datos a la vista de ÍNDICE
        return view('grupos.maestros-index', compact('grupo', 'asignaciones'));
    }

    /**
     * Muestra el FORMULARIO para asignar/editar maestros.
     */
    public function create(Grupo $grupo)
    {
        // 1. Obtenemos TODOS los maestros disponibles
        $maestrosDisponibles = User::maestros()->orderBy('name')->get();

        // 2. Obtenemos las asignaciones actuales (si existen)
        $asignacionEspanol = GrupoTitular::where('grupo_id', $grupo->grupo_id)
                                           ->where('idioma', 'ESPAÑOL')
                                           ->first();
        $asignacionIngles  = GrupoTitular::where('grupo_id', $grupo->grupo_id)
                                           ->where('idioma', 'INGLES')
                                           ->first();

        // 3. Mandamos los datos a la vista de FORMULARIO
        return view('grupos.maestros', compact(
            'grupo',
            'maestrosDisponibles',
            'asignacionEspanol', // Se manda el modelo completo (o null)
            'asignacionIngles'   // Se manda el modelo completo (o null)
        ));
    }

    /**
     * Guarda la asignación del formulario (de los cuatro <select>).
     */
    public function store(Request $request, Grupo $grupo)
    {
        // 1. Validamos los 4 campos
        $request->validate([
            'maestro_titular_espanol_id'   => 'nullable|exists:users,id',
            'maestro_auxiliar_espanol_id'  => 'nullable|exists:users,id',
            'maestro_titular_ingles_id'    => 'nullable|exists:users,id',
            'maestro_auxiliar_ingles_id'   => 'nullable|exists:users,id',
        ]);

        // 2. Usamos updateOrCreate para ESPAÑOL
        // Busca por (grupo_id, idioma) y actualiza o crea el registro
        $grupo->asignacionesTitulares()->updateOrCreate(
            [
                'idioma'   => 'ESPAÑOL',
            ],
            [
                'maestro_titular_id'  => $request->input('maestro_titular_espanol_id'),
                'maestro_auxiliar_id' => $request->input('maestro_auxiliar_espanol_id'),
            ]
        );

        // 3. Usamos updateOrCreate para INGLÉS
        $grupo->asignacionesTitulares()->updateOrCreate(
            [
                'idioma'   => 'INGLES',
            ],
            [
                'maestro_titular_id'  => $request->input('maestro_titular_ingles_id'),
                'maestro_auxiliar_id' => $request->input('maestro_auxiliar_ingles_id'),
            ]
        );

        // 4. Redirigimos de vuelta a la LISTA
        return redirect()->route('admin.grupos.maestros.index', $grupo)
                         ->with('success', 'Maestros titulares y auxiliares actualizados.');
    }
}