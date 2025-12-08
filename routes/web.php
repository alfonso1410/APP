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
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CalificacionJsonController;
use App\Http\Controllers\CicloEscolarController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PonderacionController;
use App\Http\Controllers\BoletaController;
use App\Models\CatalogoCriterio;
use App\Http\Controllers\PdaController; 
use App\Http\Controllers\ReporteAsistenciaController;


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
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil');

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
        Route::resource('niveles', NivelController::class)->parameters([
            'niveles' => 'nivel'
        ]);


        // Campos Formativos
        Route::resource('campos-formativos', CampoFormativoController::class)->except(['create', 'show', 'edit']);

        // Materias
        Route::resource('materias', MateriaController::class)->except(['create', 'show', 'edit']);

        // Criterios de Evaluación (Catálogo y Asignación)
        Route::resource('materia-criterios', MateriaCriterioController::class)->except(['show', 'edit']);

        // Administración Escolar (Ciclos y Periodos)
        Route::resource('ciclo-escolar', CicloEscolarController::class);
        
        // ANIDACIÓN DE PERIODOS BAJO CICLO-ESCOLAR
        Route::resource('ciclo-escolar.periodos', PeriodoController::class)->except(['show', 'edit']);

        Route::get('/ponderaciones', [PonderacionController::class, 'index'])
             ->name('ponderaciones.index');
             
        Route::post('/ponderaciones/guardar', [PonderacionController::class, 'store'])
             ->name('ponderaciones.store');

        // Boletas
        Route::get('/boletas', [BoletaController::class, 'index'])->name('boletas.index');
        Route::get('/reportes/boleta-alumno/{grupo}/{alumno}', [BoletaController::class, 'generarBoletaAlumno'])
            ->name('reportes.boleta.alumno');
        Route::get('/json/grupo/{grupo}/alumnos', [BoletaController::class, 'getAlumnosPorGrupo'])->name('json.grupo.alumnos');

        // Reporte de asistencia
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/asistencia', [ReporteAsistenciaController::class, 'index'])->name('asistencia.index');
    Route::get('/asistencia/generar', [ReporteAsistenciaController::class, 'generar'])->name('asistencia.generar');
});
   

    }); // <-- Fin de la ZONA DE ADMINISTRACIÓN


    /*
    |--------------------------------------------------------------------------
    | ===== ZONA COMPARTIDA (ADMINS Y MAESTROS) =====
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:DIRECTOR,COORDINADOR,MAESTRO'])
             ->prefix('admin') 
             ->name('admin.')
             ->group(function () {
    
        // ==========================================================
        // == INICIO: RUTAS DE CALIFICACIONES (COMPARTIDAS) 
        // ==========================================================
        
        Route::resource('calificaciones', CalificacionController::class)->only(['index', 'store']);

        // --- Rutas JSON para Alpine.js (para los selects dinámicos) ---
        Route::get('/json/niveles/{nivel}/grados', [CalificacionJsonController::class, 'getGradosPorNivel'])->name('json.niveles.grados');
        Route::get('/json/grados-extracurriculares', [CalificacionJsonController::class, 'getGradosExtracurriculares'])->name('json.grados.extra');
        Route::get('/json/grados/{grado}/grupos', [CalificacionJsonController::class, 'getGrupos'])->name('json.grados.grupos');
        
        // 🔥 RUTA CORREGIDA/NUEVA: Obtiene materias filtradas por GRUPO 🔥
        Route::get('/json/grupos/{grupo}/materias', [CalificacionJsonController::class, 'getMateriasPorGrupo'])->name('json.grupos.materias');
        
        // Esta ruta original para obtener materias por Grado ya NO se usará en la vista
        // para evitar que cargue todas las extracurriculares.
        Route::get('/json/grados/{grado}/materias', [CalificacionJsonController::class, 'getMaterias'])->name('json.grados.materias');

        Route::get('/json/tabla-calificaciones', [CalificacionJsonController::class, 'getTablaCalificaciones'])->name('json.tabla.calificaciones');
    
        // ==========================================================
        // == FIN: RUTAS DE CALIFICACIONES 
        // ==========================================================

        // ==========================================================
        // == INICIO: RUTAS PDA (PREESCOLAR) 
        // ==========================================================
        
        Route::get('/pda', [PdaController::class, 'index'])->name('pda.index');
        Route::post('/pda/guardar', [PdaController::class, 'store'])->name('pda.store');
        Route::get('/json/pda/data', [PdaController::class, 'getData'])->name('json.pda.data');

        // NUEVA RUTA PARA OBTENER PERIODOS DINÁMICAMENTE
        Route::get('/json/ciclos/{ciclo}/periodos', [PdaController::class, 'getPeriodos'])->name('json.ciclos.periodos');

        // ==========================================================
        // == FIN: RUTAS PDA 
        // ==========================================================

        Route::get('/reportes/concentrado-periodo/{grupo}/{periodo}/{materia}', [ReporteController::class, 'generarConcentradoPeriodo'])
            ->name('reportes.concentrado.periodo');

        // La ruta ahora apunta al nuevo BoletaController
        Route::get('/reportes/boleta-alumno/{grupo}/{alumno}', [BoletaController::class, 'generarBoletaAlumno'])
            ->name('reportes.boleta.alumno');
            
    }); // <-- Fin de la ZONA COMPARTIDA


    /*
    | =================== ZONA DE MAESTRO ===================
    | Rutas exclusivas para Maestros
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