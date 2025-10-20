<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlumnoController extends Controller
{
        public function index(Request $request): View
    {
        // Obtenemos los parámetros de la URL
        $nivel_id = $request->input('nivel', 0); // 0 será nuestro ID para "Sin Asignar"
        $search = $request->input('search');

        // Empezamos la consulta base
        $query = Alumno::query();

        // --- INICIO DE LA LÓGICA DE FILTRADO CORREGIDA ---

        if ($nivel_id == 0) {
            // FILTRO "SIN ASIGNAR":
            // Busca alumnos que NO TENGAN un grupo regular ACTIVO.
            $query->whereDoesntHave('grupos', function ($q) {
                $q->where('tipo_grupo', 'REGULAR')
                  ->where('asignacion_grupal.es_actual', 1);
            });
        } else {
            // FILTRO POR NIVEL (Preescolar, Primaria, etc.):
            // Busca alumnos que SÍ TENGAN un grupo regular ACTIVO en el nivel seleccionado.
            $query->whereHas('grupos', function ($q) use ($nivel_id) {
                $q->where('tipo_grupo', 'REGULAR')
                  ->where('asignacion_grupal.es_actual', 1) // <-- ¡LA CLAVE!
                  ->whereHas('grado', function ($subQ) use ($nivel_id) {
                      $subQ->where('nivel_id', $nivel_id);
                  });
            });
        }

        // --- FIN DE LA LÓGICA DE FILTRADO ---

        // Aplicamos la búsqueda por texto si existe
        $query->when($search, function ($q, $s) {
            $q->where(function ($subQ) use ($s) {
                $subQ->where('nombres', 'like', "%{$s}%")
                     ->orWhere('apellido_paterno', 'like', "%{$s}%")
                     ->orWhere('apellido_materno', 'like', "%{$s}%")
                     ->orWhere('curp', 'like', "%{$s}%");
            });
        });

        // Eager loading para optimizar y paginación
        $alumnos = $query->with(['grupos.grado']) // Precargamos las relaciones que la vista necesita
                         ->orderBy('apellido_paterno')
                         ->paginate(15);

        return view('alumnos.index', compact('alumnos', 'nivel_id', 'search'));
    }


    // --- El resto de los métodos (create, store, etc.) no requieren cambios ---
    
    public function create(): View
    {
        return view('alumnos.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp'             => 'required|string|unique:alumnos,curp|size:18',
            'estado_alumno'    => 'required|string|in:ACTIVO,INACTIVO',
        ]);
        Alumno::create($validatedData);
        return redirect()->route('alumnos.index')->with('success', 'Alumno creado exitosamente.');
    }

    public function edit(Alumno $alumno): View
    {
        return view('alumnos.edit', compact('alumno'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $validatedData = $request->validate([
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp'             => [
                'required', 'string', 'size:18',
                Rule::unique('alumnos')->ignore($alumno->alumno_id, 'alumno_id'),
            ],
            'estado_alumno'    => 'required|string|in:ACTIVO,INACTIVO'
        ]);
        $alumno->update($validatedData);
        return redirect()->route('alumnos.index')->with('success', 'Alumno actualizado exitosamente.');
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->estado_alumno = 'INACTIVO';
        $alumno->save();
        return redirect()->route('alumnos.index')->with('success', 'Alumno inactivado exitosamente.');
    }
}