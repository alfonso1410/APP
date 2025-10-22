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
use App\Http\Controllers\GrupoMaestroController;
use App\Http\Controllers\GrupoMateriaMaestroController;
use App\Http\Controllers\MateriaCriterioController;
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Ruta del Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

// 2. (GET) Muestra el FORMULARIO para asignar nuevos maestros (los checkboxes)
Route::get('grupos/{grupo}/maestros/asignar', [GrupoMaestroController::class, 'create'])
     ->name('grupos.maestros.create');
// 3. (POST) GUARDA la asignación del formulario
Route::post('grupos/{grupo}/maestros', [GrupoMaestroController::class, 'store'])
     ->name('grupos.maestros.store');
     // (GET) Muestra el FORMULARIO para asignar maestros (del pool) a materias (del grupo)
Route::get('grupos/{grupo}/maestros-materias', [GrupoMateriaMaestroController::class, 'create'])
     ->name('grupos.materias-maestros.create');
// (POST) GUARDA la asignación en la tabla 'grupo_materia_maestro'
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

     // Ruta para el botón global "Crear Criterio"
    // Usamos el método 'create' para la vista de definición del criterio base.
    Route::get('materia-criterios/create', [MateriaCriterioController::class, 'create'])->name('materia-criterios.create');
    
    // Rutas para la gestión de criterios base (store, update, destroy)
    Route::resource('materia-criterios', MateriaCriterioController::class)->only(['index', 'store', 'update', 'destroy']);

    // NOTA: La ruta 'materia-criterios.index' se utilizará para mostrar
    // la vista de ASIGNACIÓN de criterios a una materia específica,
    // usando un parámetro de consulta (ej: /materia-criterios?materia=1)

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';