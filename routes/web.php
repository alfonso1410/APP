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
    // Ruta del Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Usuarios
    Route::resource('users', UserController::class); // Asumiendo que 'edit' sigue siendo modal aquí

    // --- CORRECCIÓN ALUMNOS ---
    // Excluimos create y edit (son modales) y show (no se usa)
    Route::resource('alumnos', AlumnoController::class)->except([
        'create', 'edit', 'show'
    ]);
    // --- FIN CORRECCIÓN ---

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

    // Grados y Estructura
    Route::resource('grados', GradoController::class);
    Route::get('/grados/{grado}/mapear', [GradoController::class, 'showMapeo'])->name('grados.mapeo');
    Route::post('/grados/{grado}/mapear', [GradoController::class, 'storeMapeo'])->name('grados.storeMapeo');
    Route::get('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'edit'])->name('grados.estructura');
    Route::post('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'update'])->name('grados.estructura.update');

    // Maestros
    Route::resource('maestros', MaestroController::class); // Asumiendo que 'edit' sigue siendo modal aquí

    // Niveles
    Route::post('/niveles', [NivelController::class, 'store'])->name('niveles.store');

    // Campos Formativos (Ya estaba corregido)
    Route::resource('campos-formativos', CampoFormativoController::class)->except([
        'create', 'show', 'edit'
    ]);

    // Materias (Ya estaba corregido)
    Route::resource('materias', MateriaController::class)->except([
        'create', 'show', 'edit'
    ]);

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';