<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Periodo;
use App\Models\CampoFormativo;
use App\Models\ObservacionCampoFormativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreescolarObservacionController extends Controller
{
    /**
     * Muestra la cuadrícula para capturar las observaciones
     * de un grupo y periodo específicos.
     */
    public function index(Request $request)
    {
        // 1. Validar que tengamos el grupo y el periodo
        $request->validate([
            'grupo_id' => 'required|integer|exists:grupos,grupo_id',
            'periodo_id' => 'required|integer|exists:periodos,periodo_id',
        ]);

        // 2. Cargar los modelos principales
        $grupo = Grupo::with('grado.nivel')->findOrFail($request->grupo_id);
        $periodo = Periodo::findOrFail($request->periodo_id);

        // 3. Obtener el Nivel (Preescolar)
        //    (Asumo que tu relación es Grupo -> Grado -> Nivel)
        $nivel = $grupo->grado->nivel;

        // 4. Obtener los Alumnos y Campos Formativos
        $alumnos = $grupo->alumnosActuales()->orderBy('apellido_paterno')->get();
        
        $camposFormativos = CampoFormativo::where('nivel_id', $nivel->nivel_id)
                                          ->orderBy('campo_id') // O por 'orden' si tienes esa columna
                                          ->get();

        // 5. Obtener las observaciones YA GUARDADAS para este grupo/periodo
        //    Usamos groupBy y map para crear un mapa fácil de usar en la vista:
        //    [alumno_id => [campo_id => 'texto de observación']]
        $observacionesGuardadas = ObservacionCampoFormativo::where('periodo_id', $periodo->periodo_id)
            ->whereIn('alumno_id', $alumnos->pluck('alumno_id'))
            ->get()
            ->groupBy('alumno_id')
            ->map(function ($obsPorAlumno) {
                return $obsPorAlumno->keyBy('campo_id');
            });

        // 6. Mandar todo a la vista
        return view('tu-vista-de-observaciones', [
            'grupo' => $grupo,
            'periodo' => $periodo,
            'alumnos' => $alumnos,
            'camposFormativos' => $camposFormativos,
            'observaciones' => $observacionesGuardadas,
        ]);
    }

    /**
     * Guarda (actualiza o crea) las observaciones de la cuadrícula.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos que necesitamos
        $request->validate([
            'periodo_id' => 'required|integer|exists:periodos,periodo_id',
            'observaciones' => 'required|array', // Esperamos un array
            'observaciones.*.*' => 'nullable|string', // Cada observación es un string o null
        ]);

        $periodo_id = $request->periodo_id;
        $observaciones = $request->input('observaciones', []);

        // 2. Usamos una transacción para guardar todo de golpe
        //    Esto asegura que si algo falla, no se guarde nada.
        DB::beginTransaction();
        try {

            // $observaciones vendrá como: [alumno_id => [campo_id => 'texto']]
            foreach ($observaciones as $alumno_id => $campos) {
                foreach ($campos as $campo_id => $texto) {
                    
                    ObservacionCampoFormativo::updateOrCreate(
                        [
                            // Columnas para BUSCAR
                            'alumno_id'  => $alumno_id,
                            'periodo_id' => $periodo_id,
                            'campo_id'   => $campo_id,
                        ],
                        [
                            // Columnas para ACTUALIZAR o CREAR
                            'observaciones' => $texto,
                        ]
                    );
                }
            }

            DB::commit(); // Todo salió bien, guardar cambios

        } catch (\Exception $e) {
            DB::rollBack(); // Algo salió mal, deshacer cambios
            // Opcional: Registrar el error: \Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al guardar las observaciones.');
        }

        // 3. Redirigir de vuelta con mensaje de éxito
        return redirect()->back()->with('success', 'Observaciones guardadas exitosamente.');
    }
}