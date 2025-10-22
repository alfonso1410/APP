<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\CatalogoCriterio;
use App\Models\MateriaCriterio; 
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Grado; // Se mantiene por si se usa en otros métodos o si se requiere en la vista

class MateriaCriterioController extends Controller
{
    /**
     * Muestra la tabla del catálogo (si no hay materiaId) O la vista de asignación.
     */
    public function index(Request $request)
    {
        $materiaId = $request->query('materia');

        if ($materiaId) {
            // Caso 1: VISTA DE ASIGNACIÓN (Clic en "Añadir Criterios")
            $materia = Materia::findOrFail($materiaId);
            $criteriosBase = CatalogoCriterio::orderBy('nombre')->get(); 
            
            // Cargar criterios asignados SOLO por materia (sin filtro de grado)
            $criteriosAsignados = MateriaCriterio::where('materia_id', $materiaId)
                                                  ->with('catalogoCriterio')
                                                  ->get();
            
            return view('materia-criterios.assign', compact('materia', 'criteriosBase', 'criteriosAsignados'));
        }

        // Caso 2: VISTA DE CATÁLOGO (Clic en "Ver Criterios")
        $criterios = CatalogoCriterio::orderBy('nombre')->get();
        return view('materia-criterios.index', compact('criterios'));
    }

    /**
     * Muestra la vista para crear un nuevo Criterio base. (Redirige a index)
     */
    public function create()
    {
        return redirect()->route('materia-criterios.index');
    }

    /**
     * Almacena un nuevo CRITERIO BASE (Catálogo) O un CRITERIO ASIGNADO (MateriaCriterio).
     */
    public function store(Request $request)
    {
        // 1. Verificación para ASIGNAR CRITERIO A MATERIA
        if ($request->has('materia_id') && $request->has('catalogo_criterio_id')) {
            return $this->storeMateriaCriterio($request);
        }

        // 2. Acción por defecto: CREAR CRITERIO BASE (Catálogo)
        $validatedData = $request->validateWithBag('store', [
            'nombre' => 'required|string|max:150|unique:catalogo_criterios,nombre',
        ], [
            'nombre.unique' => 'Ya existe un criterio base con este nombre.',
        ]);

        CatalogoCriterio::create($validatedData);

        return redirect()->route('materia-criterios.index')->with('success', 'Criterio base creado exitosamente.');
    }
    
    /**
     * Lógica para guardar la asignación de un criterio a una materia (Reglas de Ponderación).
     */
    protected function storeMateriaCriterio(Request $request)
    {
        // 1. Pre-validación para determinar si el campo 'ponderacion' es obligatorio
        $includesAverage = $request->input('incluido_en_promedio') == '1';

        $rules = [
            'materia_id' => 'required|exists:materias,materia_id',
            'catalogo_criterio_id' => [
                'required',
                'exists:catalogo_criterios,catalogo_criterio_id',
                Rule::unique('materia_criterios')->where(function ($query) use ($request) {
                    return $query->where('materia_id', $request->materia_id);
                }),
            ],
            // PONDERACIÓN CONDICIONAL: Requerida solo si se incluye en promedio
            'ponderacion' => [
                $includesAverage ? 'required' : 'nullable', 
                'numeric', 
                'min:0.01', 
                'max:1.00'
            ],
            'incluido_en_promedio' => 'required|in:1,0', 
        ];

        $messages = [
            'catalogo_criterio_id.unique' => 'Este criterio ya está asignado a esta materia.',
            'ponderacion.required' => 'La ponderación es obligatoria si el criterio se incluye en el promedio.',
            'ponderacion.min' => 'La ponderación mínima es 0.01.',
            'ponderacion.max' => 'La ponderación máxima es 1.00.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // 2. REGLA DE NEGOCIO: La suma no puede exceder 1.00 (SOLO si se incluye)
        if ($includesAverage) {
            $currentTotalPonderacion = MateriaCriterio::where('materia_id', $request->materia_id)->sum('ponderacion');

            $validator->after(function ($validator) use ($request, $currentTotalPonderacion) {
                if ($validator->errors()->has('ponderacion')) {
                    return; 
                }

                $newPonderacion = (float)$request->input('ponderacion');
                $sumAfterAddition = $currentTotalPonderacion + $newPonderacion;

                if ($sumAfterAddition > 1.00) {
                    $remaining = max(0, 1.00 - $currentTotalPonderacion);
                    $validator->errors()->add(
                        'ponderacion',
                        "La ponderación total ({$sumAfterAddition}) excede el 1.00 (100%). Solo puedes agregar {$remaining}."
                    );
                }
            });
        }
        
        $validated = $validator->validate(); 
        
        // 3. MANEJO DEL VALOR: Si no es requerido, la ponderación es 0.00 en la DB.
        $finalPonderacion = $includesAverage ? $validated['ponderacion'] : 0.00;

        $validated['incluido_en_promedio'] = (bool)($validated['incluido_en_promedio'] ?? 0);
        $validated['ponderacion'] = $finalPonderacion;
        
        MateriaCriterio::create($validated);

        return redirect()->route('materia-criterios.index', [
            'materia' => $request->materia_id, 
        ])->with('success', 'Criterio asignado correctamente.');
    }

    /**
     * Actualiza un criterio existente (del Catálogo) O un CRITERIO ASIGNADO.
     */
    public function update(Request $request, MateriaCriterio $materia_criterio)
    {
        // 1. Verificación para ASIGNAR CRITERIO A MATERIA
        if ($request->has('materia_id') && $request->has('catalogo_criterio_id')) {
            return $this->updateMateriaCriterio($request, $materia_criterio);
        }
        
        // 2. Acción por defecto: ACTUALIZAR CRITERIO BASE (Catálogo)
        $validatedData = $request->validateWithBag('update', [
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('catalogo_criterios', 'nombre')->ignore($materia_criterio->catalogo_criterio_id, 'catalogo_criterio_id'),
            ],
        ], [
            'nombre.unique' => 'Ya existe un criterio base con este nombre.',
        ]);

        $materia_criterio->update($validatedData);

        return redirect()->route('materia-criterios.index')->with('success', 'Criterio actualizado exitosamente.');
    }
    
    /**
     * Lógica para actualizar la asignación de un criterio a una materia.
     */
    protected function updateMateriaCriterio(Request $request, MateriaCriterio $materia_criterio)
    {
        $includesAverage = $request->input('incluido_en_promedio') == '1';

        // 1. Obtener la ponderación total actual de la materia, excluyendo el registro actual
        $currentTotalPonderacion = MateriaCriterio::where('materia_id', $materia_criterio->materia_id)
                                                 ->where('materia_criterio_id', '!=', $materia_criterio->materia_criterio_id)
                                                 ->sum('ponderacion');

        $rules = [
            'materia_id' => 'exists:materias,materia_id', 
            'catalogo_criterio_id' => 'exists:catalogo_criterios,catalogo_criterio_id',
            // PONDERACIÓN CONDICIONAL
            'ponderacion' => [
                $includesAverage ? 'required' : 'nullable',
                'numeric', 
                'min:0.01', 
                'max:1.00'
            ],
            'incluido_en_promedio' => 'required|in:1,0',
        ];
        
        $messages = [
            'ponderacion.required' => 'La ponderación es obligatoria si el criterio se incluye en el promedio.',
            'ponderacion.min' => 'La ponderación mínima es 0.01.',
            'ponderacion.max' => 'La ponderación máxima es 1.00.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // 2. REGLA DE NEGOCIO: La suma no puede exceder 1.00
        if ($includesAverage) {
            $validator->after(function ($validator) use ($request, $currentTotalPonderacion) {
                if ($validator->errors()->has('ponderacion')) {
                    return; 
                }
                
                $newPonderacion = (float)$request->input('ponderacion');
                $sumAfterUpdate = $currentTotalPonderacion + $newPonderacion;

                if ($sumAfterUpdate > 1.00) {
                    $remaining = max(0, 1.00 - $currentTotalPonderacion);
                    $validator->errors()->add(
                        'ponderacion',
                        "La ponderación total excede el 1.00 (100%). Solo puedes asignar {$remaining}."
                    );
                }
            });
        }
        
        $validated = $validator->validate(); 
        
        // 3. MANEJO DEL VALOR: Si no es requerido, la ponderación es 0.00
        $finalPonderacion = $includesAverage ? $validated['ponderacion'] : 0.00;

        $materia_criterio->update([
            'ponderacion' => $finalPonderacion,
            'incluido_en_promedio' => (bool)($validated['incluido_en_promedio'] ?? 0),
        ]);

        return redirect()->route('materia-criterios.index', [
            'materia' => $materia_criterio->materia_id, 
        ])->with('success', 'Criterio actualizado correctamente.');
    }

    /**
     * Elimina un CRITERIO ASIGNADO.
     */
    public function destroy(MateriaCriterio $materia_criterio)
    {
        $materiaId = $materia_criterio->materia_id;
        
        try {
            $materia_criterio->delete();
            return redirect()->route('materia-criterios.index', [
                'materia' => $materiaId, 
            ])->with('success', 'Asignación de criterio eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('materia-criterios.index', [
                'materia' => $materiaId, 
            ])->with('error', 'Error al eliminar la asignación.');
        }
    }
}