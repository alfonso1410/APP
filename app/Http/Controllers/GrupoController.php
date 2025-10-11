<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; 
use App\Models\Alumno;

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
                             ->with('grado.nivel', 'grado.gradosRegularesAplicables')
                             ->latest()
                             ->paginate(25);

    return view('grupos.archivados', compact('gruposArchivados'));
}

public function showAlumnos(Grupo $grupo)
{
    // Obtenemos los IDs de los alumnos que YA ESTÁN en este grupo.
    $idsAlumnosAsignados = $grupo->alumnos()->pluck('alumnos.alumno_id')->toArray();

    // Ahora, decidimos qué otros alumnos elegibles mostrar según el TIPO de grupo.
    if ($grupo->tipo_grupo === 'REGULAR') {
        
        // --- LÓGICA PARA GRUPOS REGULARES ---
        // Buscamos alumnos activos que NO tengan un grupo regular activo.
        $alumnosElegibles = Alumno::where('estado_alumno', 'ACTIVO')
            ->whereDoesntHave('grupos', function ($query) {
                $query->where('tipo_grupo', 'REGULAR')->where('asignacion_grupal.es_actual', true);
            })
            ->with('grupos.grado') // Eager loading para mostrar la columna 'Extracurricular'
            ->get();

    } else { // ($grupo->tipo_grupo === 'EXTRA')

        // --- LÓGICA PARA GRUPOS EXTRACURRICULARES ---
        // 1. Obtenemos los IDs de los grados regulares permitidos por el mapeo.
        $idsGradosRegularesPermitidos = $grupo->grado->gradosRegularesAplicables()->pluck('grados.grado_id');

        if ($idsGradosRegularesPermitidos->isEmpty()) {
            $alumnosElegibles = collect(); // Si no hay grados mapeados, nadie es elegible.
        } else {
            // 2. Buscamos alumnos que cumplan TODAS las reglas:
            $alumnosElegibles = Alumno::where('estado_alumno', 'ACTIVO')
                // a) DEBE tener un grupo regular activo.
                ->whereHas('grupos', function ($query) {
                    $query->where('tipo_grupo', 'REGULAR')->where('asignacion_grupal.es_actual', true);
                })
                // b) NO DEBE tener ya otro grupo extracurricular activo.
                ->whereDoesntHave('grupos', function ($query) {
                    $query->where('tipo_grupo', 'EXTRA')->where('asignacion_grupal.es_actual', true);
                })
                // c) Su grupo regular DEBE estar en la lista de grados permitidos.
                ->whereHas('grupos', function ($query) use ($idsGradosRegularesPermitidos) {
                    $query->where('tipo_grupo', 'REGULAR')->whereIn('grado_id', $idsGradosRegularesPermitidos);
                })
                ->with('grupos.grado')
                ->get();
        }
    }
        
    // --- PREPARACIÓN FINAL DE DATOS PARA LA VISTA ---
    
    // Obtenemos la colección completa de los alumnos que ya estaban asignados.
    $alumnosYaAsignados = Alumno::whereIn('alumno_id', $idsAlumnosAsignados)->with('grupos.grado')->get();
    
    // Juntamos las dos listas: los elegibles + los que ya estaban.
    // unique() se asegura de no tener duplicados si un alumno estaba en ambas listas.
    $alumnosDisponibles = $alumnosElegibles->merge($alumnosYaAsignados)
                                           ->unique('alumno_id')
                                           ->sortBy('apellido_paterno');

    return view('grupos.alumnos', compact('grupo', 'alumnosDisponibles', 'idsAlumnosAsignados'));
}
    /**
     * Guarda las asignaciones de alumnos para un grupo.
     */
   public function storeAlumnos(Request $request, Grupo $grupo)
{
    $alumnosIds = $request->input('alumnos', []);
    
    // 1. Sincronizamos los alumnos como antes, pero guardamos los cambios.
    $changes = $grupo->alumnos()->sync($alumnosIds);

    // 2. VERIFICACIÓN DE COHERENCIA (Solo si estamos modificando un grupo REGULAR)
    if ($grupo->tipo_grupo === 'REGULAR') {
        
        // Obtenemos los IDs de todos los alumnos que fueron añadidos o quitados.
        $affectedStudentIds = array_merge($changes['attached'], $changes['detached']);

        if (!empty($affectedStudentIds)) {
            // Buscamos a esos alumnos con todas sus relaciones de grupo cargadas.
            $studentsToCheck = Alumno::with('grupos.grado.gradosRegularesAplicables')->findMany($affectedStudentIds);

            foreach ($studentsToCheck as $student) {
                // Buscamos si el alumno tiene un grupo extra.
                $extraGroup = $student->grupos->firstWhere('tipo_grupo', 'EXTRA');
                
                // Si no tiene grupo extra, no hay nada que verificar.
                if (!$extraGroup) {
                    continue;
                }

                // Buscamos su grupo regular actual (puede que ya no tenga si lo quitamos de todos).
                $regularGroup = $student->grupos->firstWhere('tipo_grupo', 'REGULAR');
                $isExtraAssignmentValid = false;

                if ($regularGroup) {
                    // Obtenemos los IDs de los grados permitidos para su extracurricular.
                    $validGradeIds = $extraGroup->grado->gradosRegularesAplicables->pluck('grado_id');
                    
                    // Verificamos si el grado de su grupo regular actual está en la lista de permitidos.
                    if ($validGradeIds->contains($regularGroup->grado_id)) {
                        $isExtraAssignmentValid = true;
                    }
                }

                // Si la asignación extracurricular ya no es válida (porque cambió de grado o ya no tiene
                // grupo regular), lo desvinculamos del grupo extra.
                if (!$isExtraAssignmentValid) {
                    $student->grupos()->detach($extraGroup->grupo_id);
                }
            }
        }
    }

    return redirect()->back()->with('success', 'Alumnos asignados exitosamente.');
}
}