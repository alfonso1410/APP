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

      $complementarios = $grupo->maestrosComplementarios; 

        return view('grupos.maestros-index', compact('grupo', 'asignaciones', 'complementarios'));
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

 // 3. NUEVO: Obtener IDs de los complementarios ya asignados para pre-llenar el select
        // pluck('id') nos da un array simple: [5, 10, 45]
        $idsComplementarios = $grupo->maestrosComplementarios()->pluck('users.id')->toArray();

        return view('grupos.maestros', compact(
            'grupo',
            'maestrosDisponibles',
            'asignacionEspanol',
            'asignacionIngles',
            'asignacionGeneral',
            'idsComplementarios' // <-- Pasamos esto a la vista
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
                'maestros_complementarios'    => 'nullable|array',
                'maestros_complementarios.*'  => 'exists:users,id',
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
                'maestros_complementarios'    => 'nullable|array',
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

        // --- B. NUEVA LÓGICA: MAESTROS COMPLEMENTARIOS ---
            
            // Obtenemos el array del select múltiple (o array vacío si no enviaron nada)
            $complementarios = $request->input('maestros_complementarios', []);

            // El método sync hace la magia:
            // 1. Agrega los nuevos IDs.
            // 2. Elimina los que ya no estén en el array.
            // 3. Mantiene los que siguen igual.
            $grupo->maestrosComplementarios()->sync($complementarios);

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