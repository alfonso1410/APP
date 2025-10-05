<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Importante para la validación de 'unique'

class AlumnoController extends Controller
{
    /**
     * Muestra una lista de todos los alumnos.
     */
    public function index()
    {
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
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp' => 'required|string|unique:alumnos,curp|size:18',
        ]);

        Alumno::create($request->all());

        return redirect()->route('alumnos.index')
                         ->with('success', 'Alumno creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un alumno existente.
     */
    public function edit(Alumno $alumno)
    {
        return view('alumnos.edit', compact('alumno'));
    }

    /**
     * Actualiza un alumno en la base de datos.
     */
    public function update(Request $request, Alumno $alumno)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'curp' => [
                'required',
                'string',
                'size:18',
                Rule::unique('alumnos')->ignore($alumno->id), // Ignora la CURP del propio alumno al validar
            ],
        ]);

        $alumno->update($request->all());

        return redirect()->route('alumnos.index')
                         ->with('success', 'Alumno actualizado exitosamente.');
    }

    /**
     * Elimina un alumno de la base de datos.
     */
    public function destroy(Alumno $alumno)
    {
        $alumno->delete();

        return redirect()->route('alumnos.index')
                         ->with('success', 'Alumno eliminado exitosamente.');
    }
}