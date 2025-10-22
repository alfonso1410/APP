<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ¡Importante!

class DashboardController extends Controller
{
    /**
     * DIRECTOR DE TRÁFICO.
     * Revisa el rol del usuario logueado y lo redirige a su panel correcto.
     * (Esta parte está PERFECTA)
     */
    public function index()
    {
        $usuario = Auth::user();
        $rol = strtoupper($usuario->rol); // Estandarizar a MAYÚSCULAS

        if ($rol === 'DIRECTOR' || $rol === 'COORDINADOR') {
            
            // Lo mandamos a la ruta del panel de Admin
            return redirect()->route('admin.dashboard');

        } elseif ($rol === 'MAESTRO') {
            
            // Lo mandamos a la ruta del panel de Maestro
            return redirect()->route('maestro.inicio');
        
        } else {
            // Fallback de seguridad
            Auth::logout();
            return redirect('/login')->with('error', 'Rol de usuario no reconocido.');
        }
    }

    /**
     * MUESTRA EL PANEL DE ADMINISTRACIÓN (Director/Coordinador)
     * (Esta parte está PERFECTA)
     */
    public function adminDashboard()
    {
        // 1. Contamos solo los alumnos activos.
        $totalAlumnos = Alumno::where('estado_alumno', 'activo')->count();

        // 2. Contamos los usuarios con rol MAESTRO
        $totalMaestros = User::where('rol', 'MAESTRO')->count(); // Asegúrate que sea MAYÚSCULAS

        // 3. Contamos los grupos activos.
        $totalGrupos = Grupo::where('estado', 'activo')->count();

        // 4. Pasamos todas las variables a la VISTA DE ADMIN
        return view('admin.dashboard', [
            'totalAlumnos' => $totalAlumnos,
            'totalMaestros' => $totalMaestros,
            'totalGrupos' => $totalGrupos,
        ]);
    }

    /**
     * ==========================================================
     * MUESTRA EL PANEL DEL MAESTRO (¡ESTA ES LA CORRECCIÓN!)
     * ==========================================================
     */
    public function maestroDashboard()
    {
        $maestro = Auth::user();

        // 1. Cargamos los grupos donde este maestro es Titular.
        //    (Asumiendo que tienes la relación 'gruposTitulares' en tu modelo User)
        $grupos = $maestro->gruposTitulares()
                          ->with('grado') // Carga la info del grado (Ej: "1°", "2°")
                          ->withCount('alumnos') // Cuenta alumnos y lo guarda en 'alumnos_count'
                          ->get();

        // 2. Definimos las notificaciones (como en tu diseño)
        $notificaciones = [
            [
                'tipo' => 'warning',
                'mensaje' => 'Fecha límite: 05/11/25'
            ]
        ];

        // 3. Pasamos todo a la vista del maestro
        return view('maestro.inicio', [
            'maestro' => $maestro,
            'gruposAsignados' => $grupos,      // <-- Dato añadido
            'notificaciones' => $notificaciones, // <-- Dato añadido
        ]);
    }
}