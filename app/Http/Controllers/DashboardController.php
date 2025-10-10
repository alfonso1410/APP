<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\User; // Importamos el modelo User para contar a los maestros
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con las estadísticas principales.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 1. Contamos solo los alumnos cuyo estado es 'activo' (true).
        $totalAlumnos = Alumno::where('estado_alumno', 'activo')->count();

        // 2. Contamos los usuarios que tienen el rol de 'maestro'.
        $totalMaestros = User::where('rol', 'maestro')->count();

        // 3. Contamos los grupos activos (suponiendo que tienes una columna 'activo').
        $totalGrupos = Grupo::where('estado', 'activo')->count();

        // 4. Pasamos todas las variables a la vista.
        return view('dashboard', [
            'totalAlumnos' => $totalAlumnos,
            'totalMaestros' => $totalMaestros,
            'totalGrupos' => $totalGrupos,
        ]);
    }
}