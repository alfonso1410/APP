<?php

namespace App\Http\Controllers;

// El modelo se importa en singular y PascalCase
use App\Models\Alumno; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Controlador para gestionar el CRUD de Alumnos.
 */
class AlumnoController extends Controller
{
    /**
     * Muestra una lista paginada de todos los alumnos.
     */
    public function index()
    {
        // Se usa el Modelo (Alumno) para consultar la tabla (alumnos)
        $alumnos = Alumno::latest()->paginate(10);
        return view('alumnos.index', compact('alumnos'));
    }

    /**
     * Muestra el formulario para crear un nuevo alumno.
     */
    public function create()
    {
        return view('alumnos.create');
    }

    /**
     * Guarda un nuevo alumno en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp'             => 'required|string|unique:alumnos,curp|size:18',
            'estado_alumno'    => 'required|boolean',
        ]);

        // CORRECCIÓN: Se llama al método create() sobre el Modelo 'Alumno'.
        Alumno::create($validatedData);

        return redirect()->route('alumnos.index')
                         ->with('success', 'Alumno creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un alumno existente.
     */
    // CORRECCIÓN: El type-hint debe ser el nombre de la clase del Modelo 'Alumno'.
    public function edit(Alumno $alumno)
    {
        return view('alumnos.edit', compact('alumno'));
    }

    /**
     * Actualiza un alumno en la base de datos.
     */
    // CORRECCIÓN: El type-hint debe ser el nombre de la clase del Modelo 'Alumno'.
    public function update(Request $request, Alumno $alumno)
    {
        $validatedData = $request->validate([
            'nombres'          => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp'             => [
                'required',
                'string',
                'size:18',
                Rule::unique('alumnos')->ignore($alumno->id),
            ],
            'estado_alumno'    => 'required|boolean',
        ]);

        $alumno->update($validatedData);

        return redirect()->route('alumnos.index')
                         ->with('success', 'Alumno actualizado exitosamente.');
    }

    /**
     * "Elimina" un alumno de forma lógica (inactivar).
     */
    // CORRECCIÓN: El type-hint debe ser el nombre de la clase del Modelo 'Alumno'.
    public function destroy(Alumno $alumno)
    {
        $alumno->estado_alumno = false;
        $alumno->save();

        return redirect()->route('alumnos.index')
                         ->with('success', 'Alumno inactivado exitosamente.');
    }
}