<?php

namespace App\Http\Controllers;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\Request;

class GrupoMaestroController extends Controller
{
    /**
     * Muestra la LISTA de maestros titulares YA ASIGNADOS al grupo.
     * (Esta vista 'grupos.maestros-index' deberá ser actualizada
     * para mostrar la columna 'idioma' que ahora está en $maestro->pivot->idioma)
     */
    public function index(Grupo $grupo)
    {
        // 1. Obtenemos los maestros (la relación ya incluye ->withPivot('idioma'))
        $maestros = $grupo->maestrosTitulares()->orderBy('name')->get();

        // 2. Mandamos los datos a la vista de ÍNDICE (la tabla)
        return view('grupos.maestros-index', compact('grupo', 'maestros'));
    }

    /**
     * --- CAMBIO GRANDE ---
     * Muestra el FORMULARIO para asignar/editar maestros titulares.
     * Ya no usa checkboxes, ahora debe mostrar dos <select> (dropdowns).
     */
    public function create(Grupo $grupo)
    {
        // 1. Obtenemos TODOS los maestros disponibles
        $maestrosDisponibles = User::maestros()->orderBy('name')->get();

        // 2. Obtenemos los maestros YA asignados y los buscamos
        $maestrosAsignados = $grupo->maestrosTitulares;

        $maestroEspanol = $maestrosAsignados->firstWhere('pivot.idioma', 'ESPAÑOL');
        $maestroIngles = $maestrosAsignados->firstWhere('pivot.idioma', 'INGLES');

        // 3. Mandamos los datos a la vista de FORMULARIO
        return view('grupos.maestros', compact(
            'grupo', 
            'maestrosDisponibles', 
            'maestroEspanol', // Se manda el modelo completo (o null)
            'maestroIngles'   // Se manda el modelo completo (o null)
        ));
    }

    /**
     * --- CAMBIO GRANDE ---
     * Guarda la asignación del formulario (de los dos <select>).
     */
    public function store(Request $request, Grupo $grupo)
    {
        // 1. Validamos los nuevos campos que DEBE enviar la vista
        $request->validate([
            'maestro_espanol_id' => 'nullable|exists:users,id',
            // 'different' evita que el mismo maestro sea asignado a ambos idiomas
            'maestro_ingles_id'  => 'nullable|exists:users,id|different:maestro_espanol_id',
        ],[
            'maestro_ingles_id.different' => 'Un maestro no puede impartir Español e Inglés en el mismo grupo.'
        ]);

        // 2. Preparamos el array especial para el método sync()
        // El formato es: [maestro_id => ['columna_pivote' => 'valor']]
        $maestrosParaSync = [];

        if ($request->filled('maestro_espanol_id')) {
            $maestrosParaSync[$request->input('maestro_espanol_id')] = ['idioma' => 'ESPAÑOL'];
        }

        if ($request->filled('maestro_ingles_id')) {
            $maestrosParaSync[$request->input('maestro_ingles_id')] = ['idioma' => 'INGLES'];
        }

        // 3. Ejecutamos el sync()
        // Esto adjunta los nuevos, actualiza los existentes y borra los antiguos.
        $grupo->maestrosTitulares()->sync($maestrosParaSync);

        // 4. Redirigimos de vuelta a la LISTA
        return redirect()->route('admin.grupos.maestros.index', $grupo)
                         ->with('success', 'Maestros titulares actualizados.');
    }
}