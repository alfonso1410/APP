<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Maestro;
use App\Models\Grupo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Contamos los registros de cada tabla
        $totalAlumnos = Alumno::count();
        $totalMaestros = Maestro::count();
        $totalGrupos = Grupo::count();

        // Pasamos las variables a la vista
        return view('dashboard', [
            'totalAlumnos' => $totalAlumnos,
            'totalMaestros' => $totalMaestros,
            'totalGrupos' => $totalGrupos,
        ]);
    }
}