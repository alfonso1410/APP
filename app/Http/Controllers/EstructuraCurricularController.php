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
     * Es decir, para asignar materias a un grado.
     */
    public function edit(Grado $grado): View
    {
        if ($grado->tipo_grado !== 'REGULAR') {
            abort(404, 'La estructura curricular solo se puede definir para grados regulares.');
        }

        $materiasDisponibles = Materia::where('tipo', 'REGULAR')
                                  ->orderBy('nombre')
                                  ->get();
        $camposFormativos = CampoFormativo::orderBy('nombre')->get();

        // Obtenemos las asignaciones actuales en un formato práctico: [materia_id => campo_id]
        $asignacionesActuales = DB::table('estructura_curricular')
            ->where('grado_id', $grado->grado_id)
            ->pluck('campo_id', 'materia_id');

        return view('grados.estructura', compact('grado', 'materiasDisponibles', 'camposFormativos', 'asignacionesActuales'));
    }

    
    public function update(Request $request, Grado $grado)
    {
        // 1. Obtenemos los IDs de las materias que el usuario marcó con el checkbox.
        $materiasSeleccionadasIds = $request->input('seleccionados', []);
        
        // 2. Obtenemos todos los valores de los selects (incluyendo los vacíos).
        $todosLosCampos = $request->input('materias', []);

        // 3. Filtramos para quedarnos solo con los datos de las materias seleccionadas.
        // Este array tiene la forma [materia_id => campo_id]
        $datosAProcesar = array_intersect_key($todosLosCampos, array_flip($materiasSeleccionadasIds));

        // 4. --- INICIO DE LA CORRECCIÓN ---
        // Creamos un nuevo array con las claves "prefijadas" que la vista espera.
        // Ej: [ 'materias.1' => '4', 'materias.2' => '' ]
        $datosAValidar = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            // Añadimos el prefijo "materias." a la clave
            $datosAValidar["materias.{$materiaId}"] = $campoId;
        }

        // 5. Validamos el NUEVO array prefijado.
        $validator = Validator::make($datosAValidar, [
            // La regla ahora coincide con las claves prefijadas que la vista espera (materias.*)
            'materias.*' => 'required|exists:campos_formativos,campo_id',
        ], [
            // Los mensajes de error ahora también coinciden con la clave 'materias.*'
            'materias.*.required' => 'Debes seleccionar un campo formativo para cada materia marcada.',
            'materias.*.exists'   => 'El campo formativo seleccionado no es válido.'
        ]);
        // --- FIN DE LA CORRECCIÓN ---
            // --- AÑADE ESTA LÍNEA PARA DEPURAR ---
      //  dd($datosAValidar, $validator->fails(), $validator->errors());
        // --- FIN DE LA DEPURACIÓN -
        // 6. Si la validación falla, Laravel redirige automáticamente con los errores.
        // Ahora la vista SÍ encontrará los errores y los mostrará.
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // 7. Preparamos los datos para el método sync().
        // Usamos el array original SIN prefijo ($datosAProcesar).
        $materiasSyncData = [];
        foreach ($datosAProcesar as $materiaId => $campoId) {
            // Asignamos el campo_id a la tabla pivote.
            $materiasSyncData[$materiaId] = ['campo_id' => $campoId];
        }
        
        // 8. Sincronizamos la relación.
        $grado->materias()->sync($materiasSyncData);

        return redirect()->route('grados.index', ['nivel' => $grado->nivel_id])
                         ->with('success', 'La estructura curricular se ha actualizado exitosamente.');
    }
}