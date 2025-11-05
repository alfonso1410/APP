<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Definir Estructura Curricular para: <span class="text-indigo-600">{{ $grado->nombre }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <x-flash-messages />

    {{-- =============================================== --}}
    {{-- =========== INICIO DE CORRECCIÓN 1 ============ --}}
    {{-- =============================================== --}}
    @php
        // $asignacionesActuales ya viene del controlador (como una Colección de objetos)
        
        // Preparamos los arrays para Alpine, dando prioridad a 'old' input
        $finalAsignaciones = [];
        $finalPonderaciones = [];
        // Si hay 'old' input (por un error de validación), esos son los checkboxes
        // Si no (primera carga), los 'seleccionados' son los que ya estaban en la BD
        $finalSeleccionados = old('seleccionados', $asignacionesActuales->keys()->toArray());

        // Usamos las materias disponibles como base para iterar
        foreach ($materiasDisponibles as $materia) {
            $materiaId = $materia->materia_id;
            // Buscamos si esta materia ya tenía una asignación en la BD
            $asignacion = $asignacionesActuales->get($materiaId); // Get the object

            // Damos prioridad a OLD (lo que el usuario ya escribió), 
            // luego al valor de la BD, y si no, un valor por defecto.
            
            // Aquí extraemos solo el 'campo_id' para el dropdown
            $finalAsignaciones[$materiaId] = (string) old('materias.' . $materiaId, $asignacion->campo_id ?? '');
            
            // Aquí extraemos solo la 'ponderacion_materia' para el input
            $finalPonderaciones[$materiaId] = (string) old('ponderaciones.' . $materiaId, $asignacion->ponderacion_materia ?? '0.00');
        }
    @endphp
    {{-- =============================================== --}}
    {{-- ============= FIN DE CORRECCIÓN 1 ============= --}}
    {{-- =============================================== --}}

            <form action="{{ route('admin.grados.estructura.update', $grado) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6 border-b">
                        <p class="text-gray-600 mb-4">
                            Selecciona las materias de este grado, asigna su campo formativo y su ponderación.
                        </p>
                        {{-- Mensaje de advertencia sobre la suma de 100% --}}
                        <p class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <strong>Nota Importante:</strong> La suma de las ponderaciones (%) de todas las materias dentro de un mismo campo formativo (ej. "Lenguajes") debe ser exactamente 100%.
                        </p>
                        
                        {{-- Errores de validación genéricos (ej. la suma de 100%) --}}
                        @if ($errors->has('total'))
                            <div class="p-4 mt-4 text-sm text-red-800 rounded-lg bg-red-100" role="alert">
                                <span class="font-medium">¡Error de Ponderación!</span> {{ $errors->first('total') }}
                            </div>
                        @endif
                        
                        {{-- Errores de validación de 'materias.*' --}}
                        @if ($errors->has('materias.*'))
                            <div class="p-4 mt-4 text-sm text-red-800 rounded-lg bg-red-100" role="alert">
                                <span class="font-medium">¡Error de validación!</span> {{ $errors->first('materias.*') }}
                            </div>
                        @endif
                        
    {{-- =============================================== --}}
    {{-- =========== INICIO DE CORRECCIÓN 2 ============ --}}
    {{-- =============================================== --}}
    <div x-data="{ 
        search: '', 
        materias: {{ $materiasDisponibles->toJson() }},
        
        // Usamos los arrays PHP que pre-procesamos arriba
        asignacionesOriginales: {{ json_encode($finalAsignaciones) }},
        asignaciones: { ...{{ json_encode($finalAsignaciones) }} },
        
        // 'ponderaciones' ahora solo tiene los valores [materia_id => valor]
        ponderacionesOriginales: {{ json_encode($finalPonderaciones) }},
        ponderaciones: { ...{{ json_encode($finalPonderaciones) }} },
        
        // Convertimos los IDs a string para que la comparación de Alpine 'includes' funcione
        seleccionados: {{ json_encode(array_map('strval', $finalSeleccionados)) }},

        handleCampoChange(materia, event) {
            const materiaId = String(materia.materia_id);
            const materiaNombre = materia.nombre;
            const originalCampoId = String(this.asignacionesOriginales[materiaId] || '');
            const newCampoId = String(event.target.value);
            const wasAssigned = originalCampoId !== '';
            const isChanging = newCampoId !== originalCampoId;

            if (wasAssigned && isChanging) {
                const userConfirmed = confirm(
                    'Confirmar Cambio\n\nMateria: ' + materiaNombre + 
                    '\n\nEstás cambiando su campo formativo. ¿Deseas continuar?'
                );
                if (!userConfirmed) {
                    // Revertimos el cambio en el select
                    this.asignaciones[materiaId] = originalCampoId;
                }
            }
        }
    }">
    {{-- =============================================== --}}
    {{-- ============= FIN DE CORRECCIÓN 2 ============= --}}
    {{-- =============================================== --}}
                            
                            <input type="text" x-model.debounce.300ms="search" placeholder="Buscar materia por nombre..." 
                                   class="w-full sm:w-2/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 my-6">

                            <div class="space-y-4">
                                <template x-for="materia in materias.filter(m => m.nombre.toLowerCase().includes(search.toLowerCase()))" :key="materia.materia_id">
                                    
                                    {{-- =============================================== --}}
                                    {{-- =========== INICIO DE CORRECCIÓN 3 ============ --}}
                                    {{-- =============================================== --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 p-4 border rounded-lg hover:bg-gray-50 transition">
                                        
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox"
                                                name="seleccionados[]"
                                                :value="materia.materia_id"
                                                x-model="seleccionados"
                                                class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <span class="ml-4 text-gray-800 font-medium" x-text="materia.nombre"></span>
                                        </div>

                                        <div>
                                            <select 
                                                :name="'materias[' + materia.materia_id + ']'" 
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring-opacity-50 text-sm"
                                                x-model="asignaciones[String(materia.materia_id)]"
                                                @change="handleCampoChange(materia, $event)"
                                                :disabled="!seleccionados.includes(String(materia.materia_id))"
                                                :class="{ 'bg-gray-100 opacity-70': !seleccionados.includes(String(materia.materia_id)) }"
                                            >
                                                <option value="">-- Selecciona un Campo Formativo --</option>
                                                @foreach ($camposFormativos as $campo)
                                                   <option value="{{ $campo->campo_id }}">{{ $campo->nombre }}</option>
                                                @endforeach
                                            </select>
                                            @error('materias.' . $materia->materia_id)
                                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <div class="relative">
                                                <input 
                                                    type="number" 
                                                    step="0.01" min="0" max="100"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring-opacity-50 text-sm"
                                                    placeholder="Pond. %"
                                                    :name="'ponderaciones[' + materia.materia_id + ']'"
                                                    x-model="ponderaciones[String(materia.materia_id)]"
                                                    :disabled="!seleccionados.includes(String(materia.materia_id))"
                                                    :class="{ 'bg-gray-100 opacity-70': !seleccionados.includes(String(materia.materia_id)) }"
                                                >
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">%</span>
                                                </div>
                                            </div>
                                            @error('ponderaciones.' . $materia->materia_id)
                                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- =============================================== --}}
                                    {{-- ============= FIN DE CORRECCIÓN 3 ============= --}}
                                    {{-- =============================================== --}}
                                </template>

                                <div x-show="materias.filter(m => m.nombre.toLowerCase().includes(search.toLowerCase())).length === 0" class="text-center p-4 border rounded-lg text-gray-500">
                                    No se encontraron materias que coincidan con la búsqueda.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 border-t flex justify-end gap-4">
                        <a href="{{ route('admin.grados.index', ['nivel' => $grado->nivel_id]) }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">
                            Guardar Estructura
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>