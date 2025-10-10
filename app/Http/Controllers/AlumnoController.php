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
        $search = $request->input('search');
        
        // Si no se encuentra un 'nivel' en la URL, se establece '1' (Preescolar) por defecto.
        $nivel_id = $request->input('nivel', 1);
        
       $alumnosQuery = Alumno::query();

    // --- LÓGICA DE FILTRADO MODIFICADA ---
    if ($nivel_id > 0) {
       $alumnosQuery->whereHas('grupos', function ($groupQuery) use ($nivel_id) {
        $groupQuery->where('tipo_grupo', 'REGULAR') // <-- Condición clave añadida
                   ->whereHas('grado', function ($gradeQuery) use ($nivel_id) {
                       $gradeQuery->where('nivel_id', $nivel_id);
                   });
    });
    } else {
        // Si el ID es 0, busca alumnos que NO TIENEN ningún grupo asignado.
        $alumnosQuery->whereDoesntHave('grupos');
    }
    // --- FIN DE LA MODIFICACIÓN ---

    // El resto de la consulta continúa encadenándose
    $alumnos = $alumnosQuery->with(['grupos' => function ($query) {
            $query->wherePivot('es_actual', true)
                  ->with('grado')
                  ->orderBy('tipo_grupo', 'asc');
        }])
        ->when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('curp', 'like', "%{$search}%");
            });
        })
        ->orderBy('apellido_paterno')
        ->orderBy('apellido_materno')
        ->orderBy('nombres')
        ->paginate(10)
        ->withQueryString();
        $niveles = Nivel::all();

        // Se añade 'nivel_id' para pasarlo a la vista y que el componente sepa qué botón resaltar
        return view('alumnos.index', [
            'alumnos' => $alumnos,
            'search' => $search,
            'niveles' => $niveles,
            'nivel_id' => $nivel_id,
        ]);
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