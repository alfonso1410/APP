<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
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
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // 1. Ruta del Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Rutas de Gestión de Usuarios (Route::resource)
    Route::resource('users', UserController::class); // <-- ¡ESTE ES EL CAMBIO CLAVE!
    
     Route::get('/prueba', function () {
        return view('users.prueba');
    })->name('prueba');

    Route::resource('alumnos', AlumnoController::class);

    //grupos
    Route::resource('grupos', GrupoController::class); 
    Route::get('/grupos-archivados', [GrupoController::class, 'indexArchivados'])->name('grupos.archivados');
    Route::patch('/grupos/{grupo}/archivar', [GrupoController::class, 'archivar'])->name('grupos.archivar');
    // 1. MUESTRA la lista de alumnos que YA ESTÁN en un grupo.
    Route::get('/grupos/{grupo}/alumnos', [GrupoController::class, 'mostrarAlumnos'])
        ->name('grupos.alumnos.index');
    // 2. MUESTRA el formulario para buscar y asignar nuevos alumnos.
    Route::get('/grupos/{grupo}/asignar-alumnos', [AsignacionGrupalController::class, 'create'])
        ->name('grupos.alumnos.create');
    // 3. PROCESA el guardado de la asignación.
    Route::post('/grupos/{grupo}/asignar-alumnos', [AsignacionGrupalController::class, 'store'])
        ->name('grupos.alumnos.store');
    // Muestra la vista para asignar/editar las materias de un grupo
// 1. Muestra la LISTA de materias asignadas a un grupo.
Route::get('/grupos/{grupo}/materias', [GrupoController::class, 'indexMaterias'])
     ->name('grupos.materias.index');

// 2. Muestra el FORMULARIO para asignar nuevas materias.
Route::get('/grupos/{grupo}/materias/asignar', [GrupoController::class, 'createMaterias'])
     ->name('grupos.materias.create');

// 3. GUARDA la asignación (esta ruta no cambia).
Route::post('/grupos/{grupo}/materias', [GrupoController::class, 'storeMaterias'])
      ->name('grupos.materias.store');

    // Ruta para la vista de Grados (solo necesitamos la vista principal por ahora)
    Route::resource('grados', GradoController::class);
    Route::get('/grados/{grado}/mapear', [GradoController::class, 'showMapeo'])->name('grados.mapeo');
Route::post('/grados/{grado}/mapear', [GradoController::class, 'storeMapeo'])->name('grados.storeMapeo');
Route::get('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'edit'])->name('grados.estructura');
Route::post('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'update'])->name('grados.estructura.update');

    Route::resource('maestros', MaestroController::class);

    Route::post('/niveles', [NivelController::class, 'store'])->name('niveles.store');

    // Ruta para el CRUD de Campos Formativos
    Route::resource('campos-formativos', CampoFormativoController::class);

    // Ruta para el CRUD de Materias
    Route::resource('materias', MateriaController::class);

    Route::post('/campos-formativos/{campo_formativo}/assign-subjects', [CampoFormativoController::class, 'assignSubjects'])
          ->name('campos-formativos.assign-subjects');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
