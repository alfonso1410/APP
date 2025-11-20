<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\CatalogoCriterio;
use App\Models\MateriaCriterio;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Grado; // Se mantiene por si se usa

class MateriaCriterioController extends Controller
{
    /**
     * Muestra la tabla del catálogo (si no hay materiaId) O la vista de asignación.
     */
    public function index(Request $request)
    {
        $materiaId = $request->query('materia');

        if ($materiaId) {
            // Caso 1: VISTA DE ASIGNACIÓN
            $materia = Materia::findOrFail($materiaId);
            $criteriosBase = CatalogoCriterio::orderBy('nombre')->get();
            $criteriosAsignados = MateriaCriterio::where('materia_id', $materiaId)
                                                ->with('catalogoCriterio')
                                                ->get();
            return view('materia-criterios.assign', compact('materia', 'criteriosBase', 'criteriosAsignados'));
        }

        // Caso 2: VISTA DE CATÁLOGO BASE
        $criterios = CatalogoCriterio::orderBy('nombre')->get();
        return view('materia-criterios.index', compact('criterios'));
    }

    /**
     * Muestra la vista para crear un nuevo Criterio base. (Redirige a index)
     */
    public function create()
    {
        return redirect()->route('admin.materia-criterios.index');
    }

    /**
     * Almacena un nuevo CRITERIO BASE (Catálogo) O un CRITERIO ASIGNADO (MateriaCriterio).
     */
    public function store(Request $request)
    {
        if ($request->has('materia_id') && $request->has('catalogo_criterio_id')) {
            return $this->storeMateriaCriterio($request);
        }

        $validatedData = $request->validateWithBag('store', [
            'nombre' => 'required|string|max:150|unique:catalogo_criterios,nombre',
        ], [
            'nombre.unique' => 'Ya existe un criterio base con este nombre.',
        ]);
        CatalogoCriterio::create($validatedData);
        return redirect()->route('admin.materia-criterios.index')->with('success', 'Criterio base creado exitosamente.');
    }

   private function recalcularPonderacionesEquitativas($materiaId)
    {
        // 1. Definir materias automáticas (Asegúrate que coincida con el store)
        $materia = Materia::find($materiaId);
        $materiasAutomaticas =  ['Hábitos', 'Habitos', 'Habits', 'HABITS', 'Reading Program', 'READING PROGRAM', 'Programa Academico', 'Programa Académico', 'PROGRAMA ACADÉMICO']; 

        if (!$materia || !in_array($materia->nombre, $materiasAutomaticas)) {
            return;
        }

        // 2. Obtener criterios
        $criterios = MateriaCriterio::where('materia_id', $materiaId)
            ->where('incluido_en_promedio', true) 
            ->get();

        $cantidad = $criterios->count();

        if ($cantidad > 0) {
            // 3. CÁLCULO CON 4 DECIMALES (Alta Precisión)
            // Multiplicamos por 10000 para trabajar con enteros grandes
            
            // Valor Base: floor((1 / 7) * 10000) / 10000 = 0.1428
            $valorBase = floor((1.00 / $cantidad) * 10000) / 10000; 
            
            // Residuo: 1.00 - (0.1428 * 7) = 0.0004
            // Convertimos a entero: 0.0004 * 10000 = 4
            $residuo = round((1.00 - ($valorBase * $cantidad)) * 10000); 

            // 4. Actualizar
            foreach ($criterios as $index => $criterio) {
                // Sumamos 0.0001 a los primeros
                $extra = ($index < $residuo) ? 0.0001 : 0;
                
                $nuevoValor = $valorBase + $extra;

                // Guardamos con 4 decimales
                $criterio->ponderacion = $nuevoValor;
                $criterio->save();
            }
        }
    }
    /**
     * Lógica para guardar la asignación de un criterio a una materia.
     */
    protected function storeMateriaCriterio(Request $request)
    {
        // 1. Pre-validación
        $includesAverage = $request->input('incluido_en_promedio') == '1';
        $materia = Materia::find($request->materia_id);
        $esAutomatica = in_array($materia->nombre, ['Hábitos', 'Habitos', 'Habits', 'HABITS', 'Reading Program', 'READING PROGRAM', 'Programa Academico', 'Programa Académico', 'PROGRAMA ACADÉMICO']);
        
        $rules = [
            'materia_id' => 'required|exists:materias,materia_id',
            'catalogo_criterio_id' => [
                'required',
                'exists:catalogo_criterios,catalogo_criterio_id',
                Rule::unique('materia_criterios')->where(fn ($query) => $query->where('materia_id', $request->materia_id)),
            ],
           // Si es automática, la ponderación es nullable porque la calcularemos nosotros
            'ponderacion' => [($includesAverage && !$esAutomatica) ? 'required' : 'nullable', 'numeric', 'min:0.00', 'max:1.00'], 
            'incluido_en_promedio' => 'required|in:1,0',
        ];
        $messages = [
            'catalogo_criterio_id.unique' => 'Este criterio ya está asignado a esta materia.',
            'ponderacion.required' => 'La ponderación es obligatoria si el criterio se incluye en el promedio.',
            'ponderacion.min' => 'La ponderación mínima es 0.01.',
            'ponderacion.max' => 'La ponderación máxima es 1.00.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        // 2. Regla de negocio suma <= 1.00
       if ($includesAverage && !$esAutomatica) { 
            $currentTotalPonderacion = MateriaCriterio::where('materia_id', $request->materia_id)->sum('ponderacion');
            $validator->after(function ($validator) use ($request, $currentTotalPonderacion) {
                if ($validator->errors()->has('ponderacion')) return;
                $newPonderacion = (float)$request->input('ponderacion');
                $sumAfterAddition = $currentTotalPonderacion + $newPonderacion;
                if ($sumAfterAddition > 1.00) {
                    $remaining = max(0, 1.00 - $currentTotalPonderacion);
                    $validator->errors()->add('ponderacion', "La ponderación total excede el 1.00. Solo puedes agregar {$remaining}.");
                }
            });
        }
        $validated = $validator->validate();

        // 3. Manejo de valor final
        $finalPonderacion = ($includesAverage && !$esAutomatica) ? $validated['ponderacion'] : 0.00;
        $validated['incluido_en_promedio'] = (bool)($validated['incluido_en_promedio'] ?? 0);
        $validated['ponderacion'] = $finalPonderacion;

        MateriaCriterio::create($validated);
        if ($esAutomatica) {
            $this->recalcularPonderacionesEquitativas($request->materia_id);
        }
        return redirect()->route('admin.materia-criterios.index', ['materia' => $request->materia_id])->with('success', 'Criterio asignado correctamente.');
    }

    /**
     * Actualiza un criterio existente (del Catálogo) O un CRITERIO ASIGNADO.
     */
    public function update(Request $request, $id) // Usa ID genérico
    {
        // Intenta encontrar como MateriaCriterio primero
        $materiaCriterio = MateriaCriterio::find($id);
        if ($materiaCriterio && $request->has('materia_id') && $request->has('catalogo_criterio_id')) {
            // Asegúrate que esta llamada esté correcta según tu lógica interna
            return $this->updateMateriaCriterio($request, $materiaCriterio);
        }

        // Si no, intenta encontrar como CatalogoCriterio
        $catalogoCriterio = CatalogoCriterio::find($id);
        if ($catalogoCriterio) {
            $validatedData = $request->validateWithBag('update', [
                'nombre' => [
                    'required', 'string', 'max:150',
                    Rule::unique('catalogo_criterios', 'nombre')->ignore($catalogoCriterio->catalogo_criterio_id, 'catalogo_criterio_id'),
                ],
            ], [
                'nombre.unique' => 'Ya existe un criterio base con este nombre.',
            ]);
            $catalogoCriterio->update($validatedData);
            return redirect()->route('admin.materia-criterios.index')->with('success', 'Criterio base actualizado exitosamente.');
        }

        // Si no encontró ninguno
         return redirect()->route('admin.materia-criterios.index')->with('error', 'No se encontró el criterio o asignación a actualizar.');
    }

    /**
     * Lógica para actualizar la asignación de un criterio a una materia.
     */
    protected function updateMateriaCriterio(Request $request, MateriaCriterio $materia_criterio)
    {
        $includesAverage = $request->input('incluido_en_promedio') == '1';
        $currentTotalPonderacion = MateriaCriterio::where('materia_id', $materia_criterio->materia_id)
                                                ->where('materia_criterio_id', '!=', $materia_criterio->materia_criterio_id)
                                                ->sum('ponderacion');
        $rules = [
             'materia_id' => 'exists:materias,materia_id',
             'catalogo_criterio_id' => 'exists:catalogo_criterios,catalogo_criterio_id',
             'ponderacion' => [$includesAverage ? 'required' : 'nullable','numeric','min:0.01','max:1.00'],
             'incluido_en_promedio' => 'required|in:1,0',
        ];
        $messages = [
             'ponderacion.required' => 'La ponderación es obligatoria si el criterio se incluye en el promedio.',
             'ponderacion.min' => 'La ponderación mínima es 0.01.',
             'ponderacion.max' => 'La ponderación máxima es 1.00.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($includesAverage) {
             $validator->after(function ($validator) use ($request, $currentTotalPonderacion) {
                 if ($validator->errors()->has('ponderacion')) return;
                 $newPonderacion = (float)$request->input('ponderacion');
                 $sumAfterUpdate = $currentTotalPonderacion + $newPonderacion;
                 if ($sumAfterUpdate > 1.00) {
                     $remaining = max(0, 1.00 - $currentTotalPonderacion);
                     $validator->errors()->add('ponderacion',"La ponderación total excede el 1.00 (100%). Solo puedes asignar {$remaining}.");
                 }
             });
        }
        $validated = $validator->validate();
        $finalPonderacion = $includesAverage ? $validated['ponderacion'] : 0.00;
        $materia_criterio->update([
            'ponderacion' => $finalPonderacion,
            'incluido_en_promedio' => (bool)($validated['incluido_en_promedio'] ?? 0),
        ]);
        return redirect()->route('admin.materia-criterios.index', ['materia' => $materia_criterio->materia_id])->with('success', 'Criterio asignado correctamente.');
    }

    /**
     * Elimina una ASIGNACIÓN (MateriaCriterio) O un CRITERIO BASE (CatalogoCriterio).
     */
    public function destroy($id) // Recibe un ID genérico
    {
        // 1. Intentar encontrar y borrar como MateriaCriterio (Asignación)
        $materiaCriterio = MateriaCriterio::find($id);
        if ($materiaCriterio) {
            $materiaId = $materiaCriterio->materia_id; // Guardar para redirección
            $materia = Materia::find($materiaId);
            $esAutomatica = in_array($materia->nombre, ['Hábitos', 'Habitos', 'Habits', 'HABITS', 'Reading Program', 'READING PROGRAM', 'Programa Academico', 'Programa Académico', 'PROGRAMA ACADÉMICO']);
            // ----------------------------------
            try {
                $materiaCriterio->delete();
                // Redirige a la PÁGINA DE ASIGNACIÓN
                // --- LLAMADA AL HELPER ---
                // Después de borrar, recalculamos el 100% entre los sobrevivientes
                if ($esAutomatica) {
                    $this->recalcularPonderacionesEquitativas($materiaId);
                }
                // -------------------------
                return redirect()->route('admin.materia-criterios.index', ['materia' => $materiaId])
                                 ->with('success', 'Asignación de criterio eliminada correctamente.');
            } catch (\Illuminate\Database\QueryException $e) { // Captura si hay calificaciones (por restricción BD)
                 report($e);
                 // Redirige a la PÁGINA DE ASIGNACIÓN
                return redirect()->route('admin.materia-criterios.index', ['materia' => $materiaId])
                                 ->with('error', 'No se puede eliminar la asignación, probablemente porque ya existen calificaciones registradas.');
            } catch (\Exception $e) {
                report($e);
                 // Redirige a la PÁGINA DE ASIGNACIÓN
                return redirect()->route('admin.materia-criterios.index', ['materia' => $materiaId])
                                 ->with('error', 'Error inesperado al eliminar la asignación.');
            }
        }

        // 2. Si no era MateriaCriterio, intentar encontrar y borrar como CatalogoCriterio (Base)
        $catalogoCriterio = CatalogoCriterio::find($id);
        if ($catalogoCriterio) {

            // --- VERIFICACIÓN REINTRODUCIDA ---
            // Usamos la relación 'materiaCriterios' del modelo CatalogoCriterio
            if ($catalogoCriterio->materiaCriterios()->exists()) {
                // Si existe alguna asignación, redirigir con error
                // Redirige a la PÁGINA DEL CATÁLOGO BASE
                return redirect()->route('admin.materia-criterios.index') // Sin parámetro 'materia'
                                ->with('error', "No se puede eliminar el criterio base '{$catalogoCriterio->nombre}'. Está asignado a una o más materias.");
            }
            // --- FIN DE LA VERIFICACIÓN ---

            // Si pasó la verificación, intentar borrar
            try {
                $catalogoCriterio->delete();
                 // Redirige a la PÁGINA DEL CATÁLOGO BASE
                return redirect()->route('admin.materia-criterios.index') // Sin parámetro 'materia'
                                 ->with('success', 'Criterio base eliminado exitosamente.');
            } catch (\Illuminate\Database\QueryException $e) {
                 report($e);
                 // Redirige a la PÁGINA DEL CATÁLOGO BASE
                return redirect()->route('admin.materia-criterios.index') // Sin parámetro 'materia'
                                 ->with('error', "No se pudo eliminar el criterio base '{$catalogoCriterio->nombre}'. Hubo un error de base de datos.");
            } catch (\Exception $e) {
                report($e);
                 // Redirige a la PÁGINA DEL CATÁLOGO BASE
                return redirect()->route('admin.materia-criterios.index') // Sin parámetro 'materia'
                                 ->with('error', 'Error inesperado al eliminar el criterio base.');
            }
        }

        // 3. Si no se encontró ni como uno ni como otro
         // Redirige a la PÁGINA DEL CATÁLOGO BASE
        return redirect()->route('admin.materia-criterios.index') // Sin parámetro 'materia'
                         ->with('error', 'No se encontró el criterio o asignación a eliminar.');
    }

} // Fin de la clase controller