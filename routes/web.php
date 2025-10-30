<?php

use Illuminate\Support\Facades\Route;

// Importaciones de Controladores
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
use App\Http\Controllers\CalificacionController; // <-- AÑADIR ESTA LÍNEA
use App\Http\Controllers\CalificacionJsonController;
use App\Http\Controllers\CicloEscolarController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\ReporteController;
// --- CORRECCIÓN: Añadir importación del modelo ---
use App\Models\CatalogoCriterio;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS Y DE AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Requieren Iniciar Sesión)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | =================== ZONA DE ADMINISTRACIÓN ===================
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:DIRECTOR,COORDINADOR'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Usuarios
        Route::resource('users', UserController::class);

        // Alumnos
        Route::resource('alumnos', AlumnoController::class)->except(['create', 'edit', 'show']);

        // Grupos y sus relaciones
        Route::resource('grupos', GrupoController::class);
        Route::get('/grupos-archivados', [GrupoController::class, 'indexArchivados'])->name('grupos.archivados');
        Route::patch('/grupos/{grupo}/archivar', [GrupoController::class, 'archivar'])->name('grupos.archivar');
        Route::get('/grupos/{grupo}/alumnos', [GrupoController::class, 'mostrarAlumnos'])->name('grupos.alumnos.index');
        Route::get('/grupos/{grupo}/asignar-alumnos', [AsignacionGrupalController::class, 'create'])->name('grupos.alumnos.create');
        Route::post('/grupos/{grupo}/asignar-alumnos', [AsignacionGrupalController::class, 'store'])->name('grupos.alumnos.store');
        Route::get('/grupos/{grupo}/materias', [GrupoController::class, 'indexMaterias'])->name('grupos.materias.index');
        Route::get('/grupos/{grupo}/materias/asignar', [GrupoController::class, 'createMaterias'])->name('grupos.materias.create');
        Route::post('/grupos/{grupo}/materias', [GrupoController::class, 'storeMaterias'])->name('grupos.materias.store');
        Route::get('grupos/{grupo}/maestros', [GrupoMaestroController::class, 'index'])->name('grupos.maestros.index');
        Route::get('grupos/{grupo}/maestros/asignar', [GrupoMaestroController::class, 'create'])->name('grupos.maestros.create');
        Route::post('grupos/{grupo}/maestros', [GrupoMaestroController::class, 'store'])->name('grupos.maestros.store');
        Route::get('grupos/{grupo}/maestros-materias', [GrupoMateriaMaestroController::class, 'create'])->name('grupos.materias-maestros.create');
        Route::post('grupos/{grupo}/maestros-materias', [GrupoMateriaMaestroController::class, 'store'])->name('grupos.materias-maestros.store');

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
        Route::resource('campos-formativos', CampoFormativoController::class)->except(['create', 'show', 'edit']);

        // Materias
        Route::resource('materias', MateriaController::class)->except(['create', 'show', 'edit']);

        // --- CORRECCIÓN: Rutas individuales para MateriaCriterio Y CatalogoCriterio ---
        Route::get('materia-criterios/create', [MateriaCriterioController::class, 'create'])->name('materia-criterios.create'); // Para crear criterios base
        Route::get('materia-criterios', [MateriaCriterioController::class, 'index'])->name('materia-criterios.index'); // Muestra catálogo o asignación
        Route::post('materia-criterios', [MateriaCriterioController::class, 'store'])->name('materia-criterios.store'); // Guarda catálogo o asignación

        // Rutas update y destroy para ASIGNACIONES y CATÁLOGO usan {id} genérico y apuntan al mismo controlador
        Route::put('materia-criterios/{id}', [MateriaCriterioController::class, 'update'])->name('materia-criterios.update');
        Route::patch('materia-criterios/{id}', [MateriaCriterioController::class, 'update']); // Alias para PATCH
        Route::delete('materia-criterios/{id}', [MateriaCriterioController::class, 'destroy'])->name('materia-criterios.destroy'); // <- Esta ruta la usarán AMBAS vistas

        // Esta ruta carga la página/vista principal
    Route::get('/calificaciones', [CalificacionController::class, 'index'])->name('calificaciones.index');

    // Esta ruta recibe el POST del formulario para guardar
    Route::post('/calificaciones', [CalificacionController::class, 'store'])->name('calificaciones.store');

    // --- Rutas JSON para Alpine.js (para los selects dinámicos) ---
    Route::get('/json/grados/{grado}/grupos', [CalificacionJsonController::class, 'getGrupos'])->name('json.grados.grupos');
    Route::get('/json/grados/{grado}/materias', [CalificacionJsonController::class, 'getMaterias'])->name('json.grados.materias');

    // --- Ruta JSON para cargar la tabla de alumnos vs criterios ---
    Route::get('/json/tabla-calificaciones', [CalificacionJsonController::class, 'getTablaCalificaciones'])->name('json.tabla.calificaciones');
    Route::get('/json/niveles/{nivel}/grados', [CalificacionJsonController::class, 'getGradosPorNivel'])->name('json.niveles.grados');

    Route::get('/json/grados-extracurriculares', [CalificacionJsonController::class, 'getGradosExtracurriculares'])->name('json.grados.extra');
    
        // ==========================================================
        // == FIN: RUTAS DE CALIFICACIONES                         ==
        // ==========================================================

        Route::resource('ciclo-escolar', CicloEscolarController::class);
        Route::resource('periodos', PeriodoController::class);
        Route::get('/reportes/concentrado-periodo/{grupo}/{periodo}', [ReporteController::class, 'generarConcentradoPeriodo'])
     ->name('admin.reportes.concentrado.periodo');
    }); // <-- Fin de la ZONA DE ADMINISTRACIÓN


    /*
    | =================== ZONA DE MAESTRO ===================
    */
    Route::middleware(['role:MAESTRO'])->prefix('maestro')->name('maestro.')->group(function () {

        Route::get('/inicio', [DashboardController::class, 'maestroDashboard'])->name('inicio');
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil');

        // Asistencias (Usando lógica automática)
        Route::get('/asistencias', [AsistenciaController::class, 'gruposIndex'])->name('asistencias.index');
        Route::get('/asistencias/tomar/{grupo}', [AsistenciaController::class, 'tomarAsistencia'])->name('asistencias.tomar');
        Route::post('/asistencias/guardar/{grupo}', [AsistenciaController::class, 'guardarAsistencia'])->name('asistencias.guardar');

    }); // <-- Fin de la ZONA DE MAESTRO

});