<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  array<string>  $roles  Lista de roles permitidos
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Si el usuario no está logueado, a la pantalla de login
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // 2. Convertimos los roles permitidos (del archivo de rutas) a mayúsculas
        $allowedRoles = array_map('strtoupper', $roles);

        // 3. Verificamos si el rol del usuario (en mayúsculas) está en la lista de permitidos
        if (!in_array(strtoupper($user->rol), $allowedRoles)) {

            // 4. Si no tiene permiso, abortamos con error 403 (Acceso Prohibido)
            abort(403, 'ACCESO NO AUTORIZADO.');
        }

        // 5. Si tiene permiso, dejamos que continúe la petición
        return $next($request);
    }
}