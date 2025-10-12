<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; 
use App\Models\Alumno;
use App\Models\Materia; 

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
        // 1. Validamos solo los datos que vienen del formulario.
        $validated = $request->validate([
            'grado_id'      => 'required|exists:grados,grado_id',
            'nombre_grupo'  => 'required|string|max:50',
            'ciclo_escolar' => 'required|string|max:10',
        ]);

        // 2. Buscamos el grado padre para obtener su tipo.
        $gradoPadre = Grado::find($validated['grado_id']);

        // 3. Creamos el nuevo grupo, añadiendo el tipo de grupo y el estado.
        Grupo::create([
            'grado_id'       => $validated['grado_id'],
            'nombre_grupo'   => $validated['nombre_grupo'],
            'ciclo_escolar'  => $validated['ciclo_escolar'],
            'tipo_grupo'     => $gradoPadre->tipo_grado, // <-- Lógica automática
            'estado'         => 'ACTIVO',
        ]);

        // 4. Preparamos los parámetros para la redirección inteligente.
        $redirectParams = $gradoPadre->tipo_grado === 'REGULAR' 
            ? ['nivel' => $gradoPadre->nivel_id] 
            : ['view_mode' => 'extracurricular'];

        return redirect()->route('grados.index', $redirectParams)
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

public function update(Request $request, Grupo $grupo)
{
    $validated = $request->validate([
        'nombre_grupo'  => 'required|string|max:50',
        'ciclo_escolar' => 'required|string|max:10',
    ]);

    $grupo->update($validated);

    // Redirección inteligente
    $redirectParams = $grupo->grado->tipo_grado === 'REGULAR' 
        ? ['nivel' => $grupo->grado->nivel_id] 
        : ['view_mode' => 'extracurricular'];

    return redirect()->route('grados.index', $redirectParams)
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
                             // ¡Añadimos todas las relaciones que la modal necesita!
                             ->with('grado.nivel', 'grado.gradosRegularesMapeados')
                             ->latest()
                             ->paginate(25);

    return view('grupos.archivados', compact('gruposArchivados'));
}
public function mostrarAlumnos(Grupo $grupo, Request $request)
{
    // Ya no necesitamos la lógica de búsqueda aquí, ni la paginación.
    // Simplemente obtenemos TODOS los alumnos del grupo con sus calificaciones.
    $alumnos = $grupo->alumnosActuales()
                     ->with(['calificaciones', 'grupos.materias'])
                     ->orderBy('apellido_paterno')
                     ->orderBy('apellido_materno')
                     ->get(); // <-- Cambiamos paginate(10) por get()

    return view('grupos.alumnos-index', compact('grupo', 'alumnos'));
}
    /**
     * Guarda las asignaciones de alumnos para un grupo.
     */
  public function showMaterias(Grupo $grupo): View
    {
        if ($grupo->tipo_grupo === 'REGULAR') {
            // Para grupos regulares, las materias vienen de la estructura curricular del grado.
            $materiasDisponibles = $grupo->grado->materias;
        } else {
            // Para grupos EXTRA, ofrecemos todas las materias existentes.
            // Opcional: podrías filtrar para excluir las que ya son parte de una estructura.
            $materiasDisponibles = Materia::orderBy('nombre')->get();
        }

        // Obtenemos los IDs de las materias que ya están asignadas a este grupo
        // para poder marcar los checkboxes en la vista.
        $idsMateriasAsignadas = $grupo->materias()->pluck('materias.materia_id')->toArray();

        return view('grupos.materias', compact('grupo', 'materiasDisponibles', 'idsMateriasAsignadas'));
    }

    /**
     * Guarda las materias seleccionadas para un grupo.
     */
    public function storeMaterias(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'materias' => 'nullable|array',
            'materias.*' => 'exists:materias,materia_id',
        ]);

        $materiasIds = $validated['materias'] ?? [];

        // sync() es el método perfecto de Laravel para tablas pivote.
        // Automáticamente añade las nuevas, quita las desmarcadas y deja las que no cambiaron.
        $grupo->materias()->sync($materiasIds);

        return redirect()->route('grados.index')->with('success', 'Materias del grupo actualizadas exitosamente.');
    }
}