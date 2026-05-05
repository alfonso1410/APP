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
use App\Http\Controllers\ReporteGrupoController;

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | ZONA DE ADMINISTRACIÓN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:DIRECTOR,COORDINADOR'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil');

        // Recursos Admin...
        Route::resource('users', UserController::class);
        Route::resource('alumnos', AlumnoController::class)->except(['create', 'edit', 'show']);
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

        Route::resource('grados', GradoController::class);
        Route::get('/grados/{grado}/mapear', [GradoController::class, 'showMapeo'])->name('grados.mapeo');
        Route::post('/grados/{grado}/mapear', [GradoController::class, 'storeMapeo'])->name('grados.storeMapeo');
        Route::get('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'edit'])->name('grados.estructura');
        Route::post('/grados/{grado}/estructura', [EstructuraCurricularController::class, 'update'])->name('grados.estructura.update');

        Route::resource('maestros', MaestroController::class);
        Route::resource('niveles', NivelController::class)->parameters(['niveles' => 'nivel']);
        Route::resource('campos-formativos', CampoFormativoController::class)->except(['create', 'show', 'edit']);
        Route::resource('materias', MateriaController::class)->except(['create', 'show', 'edit']);
        Route::resource('materia-criterios', MateriaCriterioController::class)->except(['show', 'edit']);
        Route::resource('ciclo-escolar', CicloEscolarController::class);
        Route::resource('ciclo-escolar.periodos', PeriodoController::class)->except(['show', 'edit']);

        Route::get('/ponderaciones', [PonderacionController::class, 'index'])->name('ponderaciones.index');
        Route::post('/ponderaciones/guardar', [PonderacionController::class, 'store'])->name('ponderaciones.store');

        Route::get('/boletas', [BoletaController::class, 'index'])->name('boletas.index');
        Route::get('/reportes/boleta-alumno/{grupo}/{alumno}', [BoletaController::class, 'generarBoletaAlumno'])->name('reportes.boleta.alumno');
        Route::get('/json/grupo/{grupo}/alumnos', [BoletaController::class, 'getAlumnosPorGrupo'])->name('json.grupo.alumnos');
        
        //reportes de calificaciones de promedio grupal
        Route::get('reportes/grupo/seleccion', [ReporteGrupoController::class, 'index'])->name('reportes.resumen.index');
        Route::get('reportes/grupo/{grupo}/resumen', [ReporteGrupoController::class, 'resumen'])->name('reportes.resumen');
        Route::get('reportes/grupo/{grupo}/pdf', [ReporteGrupoController::class, 'descargarPdf'])->name('reportes.resumen.pdf');
        // Reporte de asistencia
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/asistencia', [ReporteAsistenciaController::class, 'index'])->name('asistencia.index');
    Route::get('/asistencia/generar', [ReporteAsistenciaController::class, 'generar'])->name('asistencia.generar');
});
   
// <-- Fin de la ZONA DE ADMINISTRACIÓN

    });

    /*
    |--------------------------------------------------------------------------
    | ZONA COMPARTIDA (ADMINS Y MAESTROS)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:DIRECTOR,COORDINADOR,MAESTRO'])
             ->prefix('admin') 
             ->name('admin.')
             ->group(function () {
        
        // Calificaciones
        Route::resource('calificaciones', CalificacionController::class)->only(['index', 'store']);
        Route::get('/json/niveles/{nivel}/grados', [CalificacionJsonController::class, 'getGradosPorNivel'])->name('json.niveles.grados');
        Route::get('/json/grados-extracurriculares', [CalificacionJsonController::class, 'getGradosExtracurriculares'])->name('json.grados.extra');
        Route::get('/json/grados/{grado}/grupos', [CalificacionJsonController::class, 'getGrupos'])->name('json.grados.grupos');
        Route::get('/json/grupos/{grupo}/materias', [CalificacionJsonController::class, 'getMateriasPorGrupo'])->name('json.grupos.materias');
        Route::get('/json/grados/{grado}/materias', [CalificacionJsonController::class, 'getMaterias'])->name('json.grados.materias');
        Route::get('/json/tabla-calificaciones', [CalificacionJsonController::class, 'getTablaCalificaciones'])->name('json.tabla.calificaciones');
    
        // ==========================================================
        // == RUTAS PDA (PREESCOLAR)
        // ==========================================================
        
        Route::get('/pda', [PdaController::class, 'index'])->name('pda.index');
        Route::post('/pda/guardar', [PdaController::class, 'store'])->name('pda.store');
        
        // --- AQUÍ ESTABA EL PROBLEMA: Regresamos al nombre original ---
        Route::get('/json/pda/data', [PdaController::class, 'getData'])->name('json.pda.data'); 

        // Ruta nueva (no afecta al admin) para obtener periodos dinámicamente
        Route::get('/json/ciclos/{ciclo}/periodos', [PdaController::class, 'getPeriodos'])->name('json.ciclos.periodos');

        // Reportes
        Route::get('/reportes/concentrado-periodo/{grupo}/{periodo}/{materia}', [ReporteController::class, 'generarConcentradoPeriodo'])->name('reportes.concentrado.periodo');
        Route::get('/reportes/boleta-alumno/{grupo}/{alumno}', [BoletaController::class, 'generarBoletaAlumno'])->name('reportes.boleta.alumno');
    });

    /*
    | ZONA DE MAESTRO
    */
    Route::middleware(['role:MAESTRO'])->prefix('maestro')->name('maestro.')->group(function () {
        Route::get('/inicio', [DashboardController::class, 'maestroDashboard'])->name('inicio');
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil');
        Route::get('/asistencias', [AsistenciaController::class, 'gruposIndex'])->name('asistencias.index');
        Route::get('/asistencias/tomar/{grupo}', [AsistenciaController::class, 'tomarAsistencia'])->name('asistencias.tomar');
        Route::post('/asistencias/guardar/{grupo}', [AsistenciaController::class, 'guardarAsistencia'])->name('asistencias.guardar');
    });
});