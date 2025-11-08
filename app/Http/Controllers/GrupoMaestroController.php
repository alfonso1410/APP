<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\User;
use App\Models\GrupoTitular; // <-- IMPORTANTE
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <-- IMPORTANTE
use Illuminate\Support\Facades\DB;

class GrupoMaestroController extends Controller
{
    /**
     * Muestra la LISTA de maestros titulares y auxiliares YA ASIGNADOS.
     */
   public function index(Grupo $grupo)
    {
        // 1. Preparamos la consulta base
        $query = $grupo->asignacionesTitulares()->with('titular', 'auxiliar');

        // 2. Filtramos según el tipo de grupo
        if ($grupo->tipo_grupo == 'REGULAR') {
            // Para REGULAR, solo buscamos ESPAÑOL e INGLES
            $query->whereIn('idioma', ['ESPAÑOL', 'INGLES']);

        } else {
            // Para EXTRA, solo buscamos GENERAL
            $query->where('idioma', 'GENERAL');
        }

        // 3. Obtenemos los resultados y los pasamos a la vista
        $asignaciones = $query->get()->keyBy('idioma');

        // 4. Mandamos los datos a la vista de ÍNDICE
        return view('grupos.maestros-index', compact('grupo', 'asignaciones'));
    }

    /**
     * Muestra el FORMULARIO para asignar/editar maestros.
     */
    public function create(Grupo $grupo)
    {
       // 1. Obtenemos TODOS los maestros disponibles
    $maestrosDisponibles = User::maestros()->orderBy('name')->get();

    // 2. Variables para las asignaciones
    $asignacionEspanol = null;
    $asignacionIngles = null;
    $asignacionGeneral = null; // <-- Para 'EXTRA'

    // 3. Buscamos las asignaciones según el tipo de grupo
    if ($grupo->tipo_grupo == 'REGULAR') {
        
        // Lógica actual para grupos bilingües
        $asignaciones = $grupo->asignacionesTitulares()
                            ->whereIn('idioma', ['ESPAÑOL', 'INGLES'])
                            ->get()
                            ->keyBy('idioma');
        
        $asignacionEspanol = $asignaciones->get('ESPAÑOL');
        $asignacionIngles = $asignaciones->get('INGLES');

    } else {
        
        // Lógica nueva para grupos 'EXTRA' (Yoga, etc.)
        // Usaremos 'GENERAL' como clave de idioma
        $asignacionGeneral = $grupo->asignacionesTitulares()
                                ->where('idioma', 'GENERAL') // <-- Clave genérica
                                ->first();
    }

    // 4. Mandamos los datos a la vista
    // La vista decidirá qué formulario mostrar
    return view('grupos.maestros', compact(
        'grupo',
        'maestrosDisponibles',
        'asignacionEspanol', // Será null si es 'EXTRA'
        'asignacionIngles',  // Será null si es 'EXTRA'
        'asignacionGeneral'  // Será null si es 'REGULAR'
    ));
}
    /**
     * Guarda la asignación del formulario (de los cuatro <select>).
     */
   public function store(Request $request, Grupo $grupo)
    {
        // 1. Validamos los 4 campos (esto está bien)
       DB::beginTransaction();
    
    try {
        if ($grupo->tipo_grupo == 'REGULAR') {
            
            // --- Lógica para guardar GRUPO REGULAR (Bilingüe) ---
            $request->validate([
                'maestro_titular_espanol_id'  => 'nullable|exists:users,id',
                'maestro_auxiliar_espanol_id' => 'nullable|exists:users,id',
                'maestro_titular_ingles_id'   => 'nullable|exists:users,id',
                'maestro_auxiliar_ingles_id'  => 'nullable|exists:users,id',
            ]);

            // Guardar ESPAÑOL
            DB::table('grupo_titular')->updateOrInsert(
                ['grupo_id' => $grupo->grupo_id, 'idioma' => 'ESPAÑOL'],
                [
                    'maestro_titular_id'  => $request->input('maestro_titular_espanol_id'),
                    'maestro_auxiliar_id' => $request->input('maestro_auxiliar_espanol_id'),
                    'created_at' => now(), 'updated_at' => now()
                ]
            );

            // Guardar INGLÉS
            DB::table('grupo_titular')->updateOrInsert(
                ['grupo_id' => $grupo->grupo_id, 'idioma' => 'INGLES'],
                [
                    'maestro_titular_id'  => $request->input('maestro_titular_ingles_id'),
                    'maestro_auxiliar_id' => $request->input('maestro_auxiliar_ingles_id'),
                    'created_at' => now(), 'updated_at' => now()
                ]
            );
            
            // (Opcional) Limpiar registro 'GENERAL' si existiera
            DB::table('grupo_titular')
                ->where('grupo_id', $grupo->grupo_id)
                ->where('idioma', 'GENERAL')
                ->delete();

        } else {
            
            // --- Lógica para guardar GRUPO EXTRA (Genérico) ---
            $request->validate([
                'maestro_titular_general_id'  => 'nullable|exists:users,id',
                'maestro_auxiliar_general_id' => 'nullable|exists:users,id',
            ]);

            // Guardar GENERAL
            DB::table('grupo_titular')->updateOrInsert(
                ['grupo_id' => $grupo->grupo_id, 'idioma' => 'GENERAL'], // <-- Clave genérica
                [
                    'maestro_titular_id'  => $request->input('maestro_titular_general_id'),
                    'maestro_auxiliar_id' => $request->input('maestro_auxiliar_general_id'),
                    'created_at' => now(), 'updated_at' => now()
                ]
            );

            // (Opcional) Limpiar registros 'ESPAÑOL' e 'INGLES' si existieran
            DB::table('grupo_titular')
                ->where('grupo_id', $grupo->grupo_id)
                ->whereIn('idioma', ['ESPAÑOL', 'INGLES'])
                ->delete();
        }

        DB::commit(); // Todo salió bien, guardar cambios

    } catch (\Exception $e) {
        DB::rollBack(); // Algo salió mal, deshacer cambios
        // Opcional: Registrar el error
        // \Log::error("Error guardando maestros: " . $e->getMessage());
        return redirect()->back()->with('error', 'Ocurrió un error al guardar los maestros.');
    }

    // 4. Redirigimos de vuelta a la LISTA
    return redirect()->route('admin.grupos.maestros.index', $grupo)
                     ->with('success', 'Maestros titulares y auxiliares actualizados.');
}
}