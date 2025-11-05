<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; 
use App\Models\Alumno;
use App\Models\Materia; 
use App\Models\CicloEscolar; // <-- 1. Importar CicloEscolar
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

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

        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();

        // 3. Devolvemos la vista con la información del grado.
        return view('grupos.create', compact('grado', 'cicloActivo'));
    }

    /**
     * Guarda el nuevo grupo en la base de datos.
     */
     public function store(Request $request): RedirectResponse
    {
        // 1. Validamos solo los datos que vienen del formulario.
      $validated = $request->validate([
            'grado_id'         => 'required|exists:grados,grado_id',
            'ciclo_escolar_id' => 'required|integer|exists:ciclo_escolars,ciclo_escolar_id', // <-- Nuevo
            'nombre_grupo'     => [ // <-- Validación de unique actualizada
                                'required',
                                'string',
                                'max:50',
                                Rule::unique('grupos')->where(function ($query) use ($request) {
                                    return $query->where('grado_id', $request->grado_id)
                                                 ->where('ciclo_escolar_id', $request->ciclo_escolar_id);
                                }),
                              ],
            // 'ciclo_escolar' => 'required|string|max:10', // <-- Viejo (Eliminado)
        ]);
        // 2. Buscamos el grado padre para obtener su tipo.
        $gradoPadre = Grado::find($validated['grado_id']);

        // 3. Creamos el nuevo grupo, añadiendo el tipo de grupo y el estado.
        Grupo::create([
            'grado_id'         => $validated['grado_id'],
            'ciclo_escolar_id' => $validated['ciclo_escolar_id'], // <-- Nuevo
            'nombre_grupo'     => $validated['nombre_grupo'],
            // 'ciclo_escolar'    => $validated['ciclo_escolar'], // <-- Viejo (Eliminado)
            'tipo_grupo'       => $gradoPadre->tipo_grado,
            'estado'           => 'ACTIVO',
        ]);

        // 4. Preparamos los parámetros para la redirección inteligente.
        $redirectParams = $gradoPadre->tipo_grado === 'REGULAR' 
            ? ['nivel' => $gradoPadre->nivel_id] 
            : ['view_mode' => 'extracurricular'];

        return redirect()->route('admin.grados.index', $redirectParams)
                         ->with('success', 'Grupo creado exitosamente.');
    }
    // --- Los siguientes métodos los implementaremos cuando necesitemos editar y eliminar ---

    public function show(Grupo $grupo)
    {
        //
    }

    public function edit(Grupo $grupo)
{
    return view('grupos.edit', compact('grupo'));
}

public function update(Request $request, Grupo $grupo): RedirectResponse
{
    $validated = $request->validate([
            // La validación de unique debe ignorar el grupo actual
             'nombre_grupo'     => [
                                'required',
                                'string',
                                'max:50',
                                Rule::unique('grupos')->where(function ($query) use ($grupo) {
                                    return $query->where('grado_id', $grupo->grado_id)
                                                 ->where('ciclo_escolar_id', $grupo->ciclo_escolar_id);
                                })->ignore($grupo->grupo_id, 'grupo_id'), // Ignorar el ID actual
                              ],
            // No permitimos cambiar ciclo_escolar_id al editar un grupo
            // 'ciclo_escolar_id' => 'required|integer|exists:ciclos_escolares,ciclo_escolar_id',
            // 'ciclo_escolar' => 'required|string|max:10', // <-- Viejo (Eliminado)
        ]);

    $grupo->update([
            'nombre_grupo' => $validated['nombre_grupo']
            // 'estado' => $request->estado // Si añades un selector de estado
        ]);

    // Redirección inteligente
    $redirectParams = $grupo->grado->tipo_grado === 'REGULAR' 
        ? ['nivel' => $grupo->grado->nivel_id] 
        : ['view_mode' => 'extracurricular'];

    return redirect()->route('admin.grados.index', $redirectParams)
                     ->with('success', 'Grupo actualizado exitosamente.');
}
  public function destroy(Grupo $grupo)
{
    // 1. Desvinculamos a todos los alumnos de este grupo.
    // Esto borra las entradas en la tabla pivote 'asignacion_grupal'.
    $grupo->alumnos()->detach();

    // 2. Ahora que el grupo ya no tiene alumnos, podemos eliminarlo sin problemas.
    $grupo->delete();

    return back()->with('success', 'Grupo y todas sus asignaciones de alumnos han sido eliminados.');
}
public function archivar(Grupo $grupo)
{
    DB::transaction(function () use ($grupo) {
        // 1. Cambiamos el estado del grupo a 'ARCHIVADO' (o 'INACTIVO' si prefieres)
        $grupo->estado = 'ARCHIVADO';
        $grupo->save();

        // 2. Desactivamos todas las asignaciones actuales de alumnos a este grupo.
        $grupo->alumnos()->updateExistingPivot(null, ['es_actual' => false]);
    });

    return back()->with('success', 'El grupo ha sido archivado y los alumnos desvinculados.');
}

public function indexArchivados()
{
    // Vista temporal para mantenimiento
   $gruposArchivados = Grupo::where('estado', 'ARCHIVADO')
->with('grado.nivel', 'grado.gradosRegularesMapeados', 'cicloEscolar')
->latest()
->paginate(25);

    return view('grupos.archivados', compact('gruposArchivados'));
}

public function mostrarAlumnos(Grupo $grupo, Request $request)
{
    // 1. Obtenemos TODOS los alumnos del grupo con sus calificaciones.
   $alumnos = $grupo->alumnosActuales()
    ->with([
        'calificaciones',
        'grupos.materias' // 👈 agregamos esto
    ])
    ->orderBy('apellido_paterno')
    ->orderBy('apellido_materno')
    ->get();
    // 2. Preparamos el nombre de la materia (valor por defecto)
    $materiaExtraNombre = 'N/A'; 

    // 3. Si el grupo es EXTRA, buscamos el nombre de sus materias
    if ($grupo->tipo_grupo === 'EXTRA') {
        
        // El dd() que estaba aquí demostró que esta línea funciona:
        $nombres = $grupo->materias()->pluck('nombre')->implode(', ');
        
        if (!empty($nombres)) {
            $materiaExtraNombre = $nombres; // Esto valdrá "Educación Física"
        } else {
            $materiaExtraNombre = 'Sin materia asignada'; 
        }
    }

    // 4. Añadimos este nombre como una nueva propiedad a CADA alumno
    $alumnos->each(function ($alumno) use ($materiaExtraNombre) {
        $alumno->materia_extracurricular = $materiaExtraNombre;
    });
    
    // 5. Pasamos la colección MODIFICADA a la vista.
    return view('grupos.alumnos-index', compact('grupo', 'alumnos'));
}
public function indexMaterias(Grupo $grupo): View
{
    if ($grupo->tipo_grupo === 'REGULAR') {
        // Para grupos regulares, las materias vienen de la ESTRUCTURA CURRICULAR DEL GRADO.
        $materias = $grupo->grado->materias()
                        ->with(['camposFormativos', 'maestros' => function ($query) use ($grupo) {
                            // Cargamos solo el maestro asignado A ESTE GRUPO para esta materia
                            $query->where('grupo_materia_maestro.grupo_id', $grupo->grupo_id);
                        }])
                        ->get();
    } else { // EXTRA
        // Para grupos extra, las materias vienen de la asignación directa al grupo.
        $materias = $grupo->materias()
                        ->with(['maestros' => function ($query) use ($grupo) {
                            $query->where('grupo_materia_maestro.grupo_id', $grupo->grupo_id);
                        }])
                        ->get();
    }

    return view('grupos.materias-index', compact('grupo', 'materias'));
}
    /**
     * Guarda las asignaciones de alumnos para un grupo.
     */
  public function createMaterias(Grupo $grupo): View
    {
        if ($grupo->tipo_grupo === 'REGULAR') {
            // Materias definidas por la estructura curricular del grado.
            $materiasDisponibles = $grupo->grado->materias;
        } else {
        // 2. Para grupos EXTRA (¡AQUÍ ESTÁ EL CAMBIO!):
        // Las materias disponibles son todas las que están marcadas
        // explícitamente como 'EXTRA' en la tabla materias.
        $materiasDisponibles = Materia::where('tipo', 'EXTRA')
                                      ->orderBy('nombre')
                                      ->get();
    }

        $idsMateriasAsignadas = $grupo->materias()->pluck('materias.materia_id')->toArray();

        // Esta es tu vista anterior (grupos.materias), la usaremos como formulario.
        return view('grupos.materias', compact('grupo', 'materiasDisponibles', 'idsMateriasAsignadas'));
    }
      
    public function storeMaterias(Request $request, Grupo $grupo)
    {
        // ... (la lógica de validación y sync() se queda igual)
        $grupo->materias()->sync($request->input('materias', []));

        // Redirigimos a la nueva lista de materias.
        return redirect()->route('admin.grupos.materias.index', $grupo)
                         ->with('success', 'Materias del grupo actualizadas exitosamente.');
    }
}