<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\CicloEscolar;
use App\Models\PonderacionCampo;
use App\Models\CampoFormativo; // Importar CampoFormativo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PonderacionController extends Controller
{
    /**
     * Muestra la interfaz para editar ponderaciones.
     */
    public function index(Request $request)
    {
        // 1. Cargar los selectores
        $ciclos = CicloEscolar::orderBy('fecha_inicio', 'desc')->get();
        // Cargamos los grados con sus niveles para agruparlos en el select
        $grados = Grado::with('nivel')->get()->sortBy('nivel.nombre');

        // 2. Determinar el ciclo y grado seleccionados
        // Por defecto, selecciona el ciclo ACTIVO
        $cicloSeleccionadoId = $request->input('ciclo_escolar_id', 
            $ciclos->firstWhere('estado', 'ACTIVO')?->ciclo_escolar_id ?? $ciclos->first()?->ciclo_escolar_id
        );
        $gradoSeleccionadoId = $request->input('grado_id');

        $camposConPonderacion = collect();
        $gradoSeleccionado = null;

        // 3. Si el usuario ha seleccionado un grado y un ciclo...
        if ($cicloSeleccionadoId && $gradoSeleccionadoId) {
            
            // 4. Cargamos el grado y sus campos formativos (a través del Nivel)
            $gradoSeleccionado = Grado::with('nivel.camposFormativos')->find($gradoSeleccionadoId);
            
            if ($gradoSeleccionado && $gradoSeleccionado->nivel) {
                // 5. Obtenemos las ponderaciones que YA existen en la BD
                $ponderacionesGuardadas = PonderacionCampo::where('ciclo_escolar_id', $cicloSeleccionadoId)
                    ->where('grado_id', $gradoSeleccionadoId)
                    ->get()
                    ->keyBy('campo_formativo_id'); // [campo_id => ponderacion_obj]

                // 6. Mapeamos los campos formativos de ese nivel
                // y les adjuntamos la ponderación guardada (o 0.00 si no existe)
                $camposConPonderacion = $gradoSeleccionado->nivel->camposFormativos->map(function ($campo) use ($ponderacionesGuardadas) {
                    $ponderacion = $ponderacionesGuardadas->get($campo->campo_id); // Usamos 'campo_id'
                    return (object)[
                        'id' => $campo->campo_id, // PK del campo formativo
                        'nombre' => $campo->nombre,
                        'ponderacion' => $ponderacion ? $ponderacion->ponderacion : 0.00
                    ];
                });
            }
        }

        // 7. Enviamos todos los datos a la vista
        return view('admin.ponderaciones.index', [
            'ciclos' => $ciclos,
            'grados' => $grados,
            'campos' => $camposConPonderacion,
            'cicloSeleccionadoId' => $cicloSeleccionadoId,
            'gradoSeleccionadoId' => $gradoSeleccionadoId,
            'gradoSeleccionado' => $gradoSeleccionado,
        ]);
    }

    /**
     * Guarda las ponderaciones.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ciclo_escolar_id' => 'required|exists:ciclo_escolars,ciclo_escolar_id',
            'grado_id' => 'required|exists:grados,grado_id',
            'ponderaciones' => 'required|array',
            'ponderaciones.*' => 'required|numeric|min:0|max:100',
        ]);

        $cicloId = $request->ciclo_escolar_id;
        $gradoId = $request->grado_id;
        $ponderaciones = $request->ponderaciones; // [campo_id => valor]

        // Validación Nivel Senior: La suma debe ser exactamente 100.
        // Usamos bccomp para comparar decimales de forma segura.
        $totalPonderacion = array_sum($ponderaciones);
        if (bccomp($totalPonderacion, '100.00', 2) != 0 && bccomp($totalPonderacion, '100', 0) != 0) {
            return back()->withErrors(['total' => 'La suma de las ponderaciones debe ser exactamente 100%. Total actual: ' . $totalPonderacion]);
        }

        // Usamos una transacción para asegurar que todo se guarde
        // o nada se guarde si ocurre un error.
        DB::transaction(function () use ($cicloId, $gradoId, $ponderaciones) {
            foreach ($ponderaciones as $campoId => $valor) {
                
                // updateOrCreate es perfecto aquí:
                // Busca un registro que coincida con el primer array.
                // Si lo encuentra, lo actualiza con el segundo array.
                // Si no, crea un nuevo registro con la mezcla de ambos.
                PonderacionCampo::updateOrCreate(
                    [
                        'ciclo_escolar_id' => $cicloId,
                        'grado_id' => $gradoId,
                        'campo_formativo_id' => $campoId,
                    ],
                    [
                        'ponderacion' => $valor
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Ponderaciones guardadas exitosamente.');
    }
}