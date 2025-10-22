<?php

use Illuminate\Support\Facades\Route;

// Importaciones de Controladores (las que ya tenías)
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\AsignacionGrupalController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\EstructuraCurricularController;
use App\Http\Controllers\CampoFormativoController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\GrupoMaestroController;
use App\Http\Controllers\GrupoMateriaMaestroController;
use App\Http\Controllers\MateriaCriterioController;
use App\Http\Controllers\AsistenciaController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS Y DE AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

// Ruta raíz redirige al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Carga las rutas de auth (login, logout, register, etc.)
require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Requieren Iniciar Sesión)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // 1. EL "DIRECTOR DE TRÁFICO" (Redirige según el rol)
    // Laravel te envía aquí después de iniciar sesión.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. RUTAS COMUNES (Para todos los roles)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | =================== ZONA DE ADMINISTRACIÓN ===================
    | (Solo accesible para DIRECTOR y COORDINADOR)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:DIRECTOR,COORDINADOR'])->prefix('admin')->name('admin.')->group(function () {
        
        // Panel de Admin (con estadísticas)
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // --- TODAS TUS RUTAS DE GESTIÓN VAN AQUÍ ---
        
        // Usuarios
        Route::resource('users', UserController::class); 

        // Alumnos
        Route::resource('alumnos', AlumnoController::class)->except([
            'create', 'edit', 'show'
        ]);

        // Grupos
        Route::resource('grupos', GrupoController::class);
        Route::get('/grupos-archivados', [GrupoController::class, 'indexArchivados'])->name('grupos.archivados');
        Route::patch('/grupos/{grupo}/archivar', [GrupoController::class, 'archivar'])->name('grupos.archivar');
        // Alumnos en Grupos
        Route::get('/grupos/{grupo}/alumnos', [GrupoController::class, 'mostrarAlumnos'])
            ->name('grupos.alumnos.index');
        Route::get('/grupos/{grupo}/asignar-alumnos', [AsignacionGrupalController::class, 'create'])
            ->name('grupos.alumnos.create');
        Route::post('/grupos/{grupo}/asignar-alumnos', [AsignacionGrupalController::class, 'store'])
            ->name('grupos.alumnos.store');
        // Materias en Grupos
        Route::get('/grupos/{grupo}/materias', [GrupoController::class, 'indexMaterias'])
            ->name('grupos.materias.index');
        Route::get('/grupos/{grupo}/materias/asignar', [GrupoController::class, 'createMaterias'])
            ->name('grupos.materias.create');
        Route::post('/grupos/{grupo}/materias', [GrupoController::class, 'storeMaterias'])
            ->name('grupos.materias.store');
        Route::get('grupos/{grupo}/maestros', [GrupoMaestroController::class, 'index'])
            ->name('grupos.maestros.index');
        Route::get('grupos/{grupo}/maestros/asignar', [GrupoMaestroController::class, 'create'])
            ->name('grupos.maestros.create');
        Route::post('grupos/{grupo}/maestros', [GrupoMaestroController::class, 'store'])
            ->name('grupos.maestros.store');
        Route::get('grupos/{grupo}/maestros-materias', [GrupoMateriaMaestroController::class, 'create'])
            ->name('grupos.materias-maestros.create');
        Route::post('grupos/{grupo}/maestros-materias', [GrupoMateriaMaestroController::class, 'store'])
            ->name('grupos.materias-maestros.store');

        // Grados y Estructura
        Route::resource('grados', GradoController::class);
        Route::get('/grados/{grado}/mapear', [GradoController::class, 'showMapeo'])->name('grados.mapeo');
        Route::post('/grados/{grado}/mapear', [GradoController::class, 'storeMapeo'])->name('grados.storeMapeo');
        Route::get('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'edit'])->name('grados.estructura');
        Route::post('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'update'])->name('grados.estructura.update');

        // Maestros
        Route::resource('maestros', MaestroController::class); 

        // Niveles
        Route::post('/niveles', [NivelController::class, 'store'])->name('niveles.store');

        // Campos Formativos
        Route::resource('campos-formativos', CampoFormativoController::class)->except([
            'create', 'show', 'edit'
        ]);

        // Materias 
        Route::resource('materias', MateriaController::class)->except([
            'create', 'show', 'edit'
        ]);

        // Materia Criterios
        Route::get('materia-criterios/create', [MateriaCriterioController::class, 'create'])->name('materia-criterios.create');
        Route::resource('materia-criterios', MateriaCriterioController::class)->only(['index', 'store', 'update', 'destroy']);

    }); // <-- Fin de la ZONA DE ADMINISTRACIÓN


    /*
    |--------------------------------------------------------------------------
    | =================== ZONA DE MAESTRO ===================
    | (Solo accesible para MAESTRO)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:MAESTRO'])->prefix('maestro')->name('maestro.')->group(function () {

        // Panel de Maestro (Mis Grupos Asignados)
        Route::get('/inicio', [DashboardController::class, 'maestroDashboard'])->name('inicio');

        // Perfil de Maestro (Mi Perfil)
        // Reutilizamos el controlador de Profile, pero la ruta es específica de maestro
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil');

        // --- INICIO DE LA MODIFICACIÓN DE ASISTENCIAS ---

        // 1. (GET) Muestra la lista de grupos
        Route::get('/asistencias', [AsistenciaController::class, 'gruposIndex'])->name('asistencias.index');

        // 2. (GET) Muestra la tabla para tomar asistencia de UN grupo
        //    Esta es la ruta para tu nuevo diseño
        Route::get('/asistencias/tomar/{grupo}', [AsistenciaController::class, 'tomarAsistencia'])->name('asistencias.tomar');

        // 3. (POST) Guarda los datos de asistencia de esa tabla
        Route::post('/asistencias/guardar/{grupo}', [AsistenciaController::class, 'guardarAsistencia'])->name('asistencias.guardar');
        
        // --- FIN DE LA MODIFICACIÓN ---

    }); // <-- Fin de la ZONA DE MAESTRO

});