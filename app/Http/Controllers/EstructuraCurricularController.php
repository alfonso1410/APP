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
        // 1. Obtenemos los datos del formulario
        $materiasSeleccionadasIds = $request->input('seleccionados', []);
        $todosLosCampos = $request->input('materias', []);
        $todasLasPonderaciones = $request->input('ponderaciones', []);

        // 2. Filtramos para procesar solo lo seleccionado
        $datosAProcesar = array_intersect_key($todosLosCampos, array_flip($materiasSeleccionadasIds));
        $ponderacionesAProcesar = array_intersect_key($todasLasPonderaciones, array_flip($materiasSeleccionadasIds));

        // ============================================================
        // === 3. CÁLCULO AUTOMÁTICO DE PONDERACIONES (LÓGICA DE REPARTO) ===
        // ============================================================

        // A. Definimos los NOMBRES de los campos MANUALES (SEP) - "Blindado"
        $nombresManuales = [
            'Lenguajes', 'LENGUAJES', 'Lenguaje',
            'Saberes y Pensamiento Científico', 'Saberes y Pensamiento Cientifico', 
            'SABERES Y PENSAMIENTO CIENTIFICO', 'SABERES Y PENSAMIENTO CIENTÍFICO',
            'Ética, Naturaleza y Sociedades', 'Ética Naturaleza y Sociedad', 
            'Etica Naturaleza y Sociedad', 'ETICA NATURALEZA Y SOCIEDAD',
            'De lo Humano y lo Comunitario', 'De lo Humano a lo Comunitario', 
            'DE LO HUMANO Y LO COMUNITARIO', 'DE LO HUMANO A LO COMUNITARIO'
        ];

        // B. Agrupamos por Campo Formativo
        $gruposPorCampo = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            if (!empty($campoId)) {
                $gruposPorCampo[$campoId][] = $materiaId;
            }
        }

        // C. Procesamos cada grupo para calcular porcentajes si es necesario
        foreach ($gruposPorCampo as $campoId => $materiasDelGrupo) {
            $campo = CampoFormativo::find($campoId);
            
            // Si el campo existe Y su nombre NO está en la lista manual, es automático
            if ($campo && !in_array($campo->nombre, $nombresManuales)) {
                
                $cantidadMaterias = count($materiasDelGrupo);
                
                if ($cantidadMaterias > 0) {
                    // 1. Valor Base (Piso)
                    $valorBase = floor((100 / $cantidadMaterias) * 100) / 100;
                    
                    // 2. Residuo (Centavos)
                    $residuo = round((100 - ($valorBase * $cantidadMaterias)) * 100); 

                    // 3. Reparto
                    $materiasReindexadas = array_values($materiasDelGrupo);

                    foreach ($materiasDelGrupo as $index => $materiaId) {
                        $indiceNumerico = array_search($materiaId, $materiasReindexadas);
                        $extra = ($indiceNumerico < $residuo) ? 0.01 : 0;
                        $valorFinal = $valorBase + $extra;
                        
                        // SOBREESCRIBIMOS el valor para que pase la validación
                        $ponderacionesAProcesar[$materiaId] = number_format($valorFinal, 2, '.', '');
                    }
                }
            }
        }
        // ============================================================


        // 4. Validamos que cada materia tenga un Campo Formativo
        $datosAValidar = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            $datosAValidar["materias.{$materiaId}"] = $campoId;
        }
        
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

        // 5. Validamos las Ponderaciones (Individuales)
        // NOTA: Aquí ya validamos los valores calculados automáticamente
        $ponderacionesAValidar = [];
        foreach ($ponderacionesAProcesar as $materiaId => $ponderacion) {
             $ponderacionesAValidar["ponderaciones.{$materiaId}"] = $ponderacion;
        }
        $ponderacionValidator = Validator::make($ponderacionesAValidar, [
            'ponderaciones.*' => 'required|numeric|min:0|max:100',
        ], [
            'ponderaciones.*.required' => 'Debe asignar una ponderación (%) a cada materia.',
            'ponderaciones.*.numeric'  => 'La ponderación (%) debe ser un número.',
            'ponderaciones.*.max'      => 'La ponderación (%) no puede ser mayor a 100.',
        ]);

        if ($ponderacionValidator->fails()) {
            return redirect()->back()->withErrors($ponderacionValidator)->withInput();
        }

        // 6. Validamos Suma Total 100%
        $sumaPorCampo = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            if (!empty($campoId)) {
                $ponderacion = (float)($ponderacionesAProcesar[$materiaId] ?? 0);
                if (!isset($sumaPorCampo[$campoId])) $sumaPorCampo[$campoId] = 0;
                $sumaPorCampo[$campoId] += $ponderacion;
            }
        }

        foreach ($sumaPorCampo as $campoId => $suma) {
            // Usamos bccomp para comparar decimales de forma segura (tolerancia estricta)
            if (bccomp($suma, '100.00', 2) != 0) {
                $campo = CampoFormativo::find($campoId);
                return redirect()->back()->withErrors([
                    'total' => "Error en el campo '{$campo->nombre}': La suma debe ser 100%. Suma actual: {$suma}%"
                ])->withInput();
            }
        }


        // 7. Guardamos en la Base de Datos
        $materiasSyncData = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            $ponderacion = $ponderacionesAProcesar[$materiaId] ?? 0;
            
            // Validación final de seguridad (redundante pero segura)
            if (empty($campoId)) {
                return redirect()->back()
                    ->withErrors(['materias' => 'Error interno: Materia sin campo asignado.'])
                    ->withInput();
            }

            $materiasSyncData[$materiaId] = [
                'campo_id' => $campoId,
                'ponderacion_materia' => $ponderacion
            ];
        }

        $grado->materias()->sync($materiasSyncData);

        return redirect()->route('admin.grados.index', ['nivel' => $grado->nivel_id])
                         ->with('success', 'La estructura curricular se ha actualizado exitosamente.');
    }
}