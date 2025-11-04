<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\User;
use App\Models\CicloEscolar; // <-- Importación correcta
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- Importación correcta

class DashboardController extends Controller
{
    /**
     * DIRECTOR DE TRÁFICO.
     */
    public function index()
    {
        $usuario = Auth::user();
        $rol = strtoupper($usuario->rol);

        if ($rol === 'DIRECTOR' || $rol === 'COORDINADOR') {
            return redirect()->route('admin.dashboard');
        } elseif ($rol === 'MAESTRO') {
            return redirect()->route('maestro.inicio');
        } else {
            Auth::logout();
            return redirect('/login')->with('error', 'Rol de usuario no reconocido.');
        }
    }

    /**
     * MUESTRA EL PANEL DE ADMINISTRACIÓN
     */
    public function adminDashboard()
    {
        $totalAlumnos = Alumno::where('estado_alumno', 'activo')->count();
        $totalMaestros = User::where('rol', 'MAESTRO')->count();
        $totalGrupos = Grupo::where('estado', 'activo')->count();

        return view('admin.dashboard', [
            'totalAlumnos' => $totalAlumnos,
            'totalMaestros' => $totalMaestros,
            'totalGrupos' => $totalGrupos,
        ]);
    }

    /**
     * MUESTRA EL PANEL DEL MAESTRO
     */
    public function maestroDashboard()
    {
        $maestro = Auth::user();
        $maestroId = $maestro->id;
        
        // Obtenemos el ciclo activo para filtrar ambas consultas
        $cicloActivo = CicloEscolar::where('estado', 'ACTIVO')->first();
        $cicloActivoId = $cicloActivo ? $cicloActivo->ciclo_escolar_id : null;

        // --- CORRECCIÓN 1: Obtener el nombre del ciclo para el modal ---
        $cicloActivoNombre = $cicloActivo ? $cicloActivo->nombre : 'Ciclo no activo';

        // 1. GRUPOS DONDE ES TITULAR (Tutor)
        $gruposTitulares = $maestro->gruposTitulares()
            ->where('ciclo_escolar_id', $cicloActivoId)
            // --- CORRECCIÓN 2: Carga eficiente de Nivel ---
            ->with('grado.nivel') // Carga Grado Y el Nivel del grado
            ->withCount('alumnos')
            ->get();

        // 2. GRUPOS DONDE IMPARTE MATERIAS
        $gruposDondeImparteIds = DB::table('grupo_materia_maestro as gmm')
            ->join('grupos', 'gmm.grupo_id', '=', 'grupos.grupo_id')
            ->where('gmm.maestro_id', $maestroId)
            ->where('grupos.ciclo_escolar_id', $cicloActivoId)
            ->distinct()
            ->pluck('grupos.grupo_id');

        $gruposDondeImparte = Grupo::whereIn('grupo_id', $gruposDondeImparteIds)
            // --- CORRECCIÓN 2: Carga eficiente de Nivel ---
            ->with('grado.nivel') // Carga Grado Y el Nivel del grado
            ->withCount('alumnos')
            ->get();
        
        // 3. Definimos las notificaciones
        $notificaciones = [
            [
                'tipo' => 'warning',
                'mensaje' => 'Fecha límite: 05/11/25'
            ]
        ];

        // 4. Pasamos todo a la vista del maestro
        return view('maestro.inicio', [
            'maestro' => $maestro,
            'gruposTitulares' => $gruposTitulares,
            'gruposDondeImparte' => $gruposDondeImparte,
            'notificaciones' => $notificaciones,
            'cicloActivoNombre' => $cicloActivoNombre, // <-- CORRECCIÓN 1: Pasar el nombre
        ]);
    }
}