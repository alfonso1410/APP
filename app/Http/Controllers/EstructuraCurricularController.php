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

        $materiasDisponibles = Materia::orderBy('nombre')->get();
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
    // Si no se marcó ninguno, será un arreglo vacío.
    $materiasSeleccionadasIds = $request->input('seleccionados', []);
    
    // 2. Obtenemos todos los valores de los selects (incluyendo los vacíos).
    $todosLosCampos = $request->input('materias', []);

    // 3. Filtramos para quedarnos solo con los datos de las materias seleccionadas.
    // array_flip convierte los valores del array en claves para una búsqueda rápida.
    $datosAValidar = array_intersect_key($todosLosCampos, array_flip($materiasSeleccionadasIds));

    // 4. Creamos una validación específica para los datos que SÍ vamos a procesar.
    $validator = Validator::make($datosAValidar, [
        // La regla se aplica a cada elemento del arreglo que le pasamos.
        // 'required' asegura que no se envíe un campo formativo vacío.
        '*' => 'required|exists:campos_formativos,campo_id',
    ], [
        // Mensaje de error personalizado y amigable.
        '*.required' => 'Debes seleccionar un campo formativo para cada materia marcada.',
        '*.exists'   => 'El campo formativo seleccionado no es válido.'
    ]);

    // Si la validación falla, Laravel redirige automáticamente con los errores.
    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }
    
    // 5. Preparamos los datos para el método sync().
    $materiasSyncData = [];
    foreach ($datosAValidar as $materiaId => $campoId) {
        // Asignamos el campo_id a la tabla pivote.
        $materiasSyncData[$materiaId] = ['campo_id' => $campoId];
    }
    
    // 6. Sincronizamos la relación. Esto borrará las materias que ya no están
    // en $materiasSyncData, añadirá las nuevas y actualizará las existentes.
    $grado->materias()->sync($materiasSyncData);

    return redirect()->route('grados.index', ['nivel' => $grado->nivel_id])
                      ->with('success', 'La estructura curricular se ha actualizado exitosamente.');
}
}
