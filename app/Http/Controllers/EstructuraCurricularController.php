<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\Materia;
use Illuminate\View\View;
use App\Models\CampoFormativo; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EstructuraCurricularController extends Controller
{
    /**
     * Muestra la vista para editar la estructura curricular de un grado.
     */
    public function edit(Grado $grado): View
    {
        if ($grado->tipo_grado !== 'REGULAR') {
            abort(404, 'La estructura curricular solo se puede definir para grados regulares.');
        }

        $materiasDisponibles = Materia::where('tipo', 'REGULAR')
                                    ->orderBy('nombre')
                                    ->get();
        $camposFormativos = CampoFormativo::where('nivel_id', $grado->nivel_id)
                                          ->orderBy('nombre')
                                          ->get();

        // --- INICIO DE CORRECCIÓN ---
        // Obtenemos las asignaciones actuales con todos sus datos (incluyendo ponderación)
        // y las indexamos por 'materia_id' para fácil acceso en la vista.
        $asignacionesActuales = DB::table('estructura_curricular')
            ->where('grado_id', $grado->grado_id)
            ->get()
            ->keyBy('materia_id'); // Devuelve [materia_id => {objeto completo}]
        // --- FIN DE CORRECCIÓN ---

        return view('grados.estructura', compact(
            'grado', 
            'materiasDisponibles', 
            'camposFormativos', 
            'asignacionesActuales' // <-- Ahora contiene la ponderación
        ));
    }

    
    public function update(Request $request, Grado $grado)
    {
        // 1. Obtenemos los IDs de las materias que el usuario marcó
        $materiasSeleccionadasIds = $request->input('seleccionados', []);
        
        // 2. Obtenemos todos los valores (campos y ponderaciones)
        $todosLosCampos = $request->input('materias', []);
        $todasLasPonderaciones = $request->input('ponderaciones', []); // <-- NUEVA LÍNEA

        // 3. Filtramos para quedarnos solo con los datos de las materias seleccionadas.
        $datosAProcesar = array_intersect_key($todosLosCampos, array_flip($materiasSeleccionadasIds));
        $ponderacionesAProcesar = array_intersect_key($todasLasPonderaciones, array_flip($materiasSeleccionadasIds)); // <-- NUEVA LÍNEA

        // 4. Validamos los Campos Formativos (lógica que ya tenías)
        $datosAValidar = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            $datosAValidar["materias.{$materiaId}"] = $campoId;
        }

        // 5. Validamos el array prefijado.
        // AÑADIMOS 'min:1' para asegurar que un string vacío ("") falle la validación.
        $validator = Validator::make($datosAValidar, [
            'materias.*' => 'required|numeric|min:1|exists:campos_formativos,campo_id',
        ], [
            'materias.*.required' => 'Debes seleccionar un campo formativo para cada materia marcada.',
            'materias.*.min'      => 'Debes seleccionar un campo formativo para cada materia marcada.',
            'materias.*.exists'   => 'El campo formativo seleccionado no es válido.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 6. --- INICIO DE NUEVA VALIDACIÓN: Ponderaciones ---

        // 6a. Validar que las ponderaciones sean numéricas y estén en rango
        $ponderacionesAValidar = [];
        foreach ($ponderacionesAProcesar as $materiaId => $ponderacion) {
             $ponderacionesAValidar["ponderaciones.{$materiaId}"] = $ponderacion;
        }
        $ponderacionValidator = Validator::make($ponderacionesAValidar, [
            'ponderaciones.*' => 'required|numeric|min:0|max:100',
        ], [
            'ponderaciones.*.required' => 'Debe asignar una ponderación (%) a cada materia seleccionada.',
            'ponderaciones.*.numeric'  => 'La ponderación (%) debe ser un número.',
            'ponderaciones.*.min'      => 'La ponderación (%) no puede ser negativa.',
            'ponderaciones.*.max'      => 'La ponderación (%) no puede ser mayor a 100.',
        ]);

        if ($ponderacionValidator->fails()) {
            return redirect()->back()->withErrors($ponderacionValidator)->withInput();
        }

        // 6b. Validar que la suma por campo formativo sea 100%
        $sumaPorCampo = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            if (!empty($campoId)) { // Solo sumar las que tienen un campo asignado
                $ponderacion = (float)($ponderacionesAProcesar[$materiaId] ?? 0);
                
                if (!isset($sumaPorCampo[$campoId])) {
                    $sumaPorCampo[$campoId] = 0; // Inicializar la suma
                }
                $sumaPorCampo[$campoId] += $ponderacion;
            }
        }

        // 6c. Revisar las sumas
        foreach ($sumaPorCampo as $campoId => $suma) {
            // Usamos bccomp para comparar decimales de forma segura
            if (bccomp($suma, '100.00', 2) != 0 && bccomp($suma, '100', 0) != 0) {
                $campo = CampoFormativo::find($campoId);
                return redirect()->back()->withErrors([
                    'total' => "Error en el campo '{$campo->nombre}': La suma de las ponderaciones de sus materias no es 100%. Suma actual: {$suma}%"
                ])->withInput();
            }
        }
        // --- FIN DE NUEVA VALIDACIÓN ---

        
        // 7. Preparamos los datos para el método sync().
        $materiasSyncData = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            // --- INICIO DE CORRECCIÓN ---
            // Añadimos la ponderacion_materia a la tabla pivote
            $ponderacion = $ponderacionesAProcesar[$materiaId] ?? 0;
            $materiasSyncData[$materiaId] = [
                'campo_id' => $campoId,
                'ponderacion_materia' => $ponderacion // <-- NUEVA COLUMNA
            ];
            // --- FIN DE CORRECCIÓN ---
        }
        
        // 8. Sincronizamos la relación.
        // El modelo Grado ya fue actualizado a 'withPivot('campo_id', 'ponderacion_materia')'
        $grado->materias()->sync($materiasSyncData);

        return redirect()->route('admin.grados.index', ['nivel' => $grado->nivel_id])
                         ->with('success', 'La estructura curricular se ha actualizado exitosamente.');
    }
}