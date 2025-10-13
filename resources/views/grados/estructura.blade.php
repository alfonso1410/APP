<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Definir Estructura Curricular para: <span class="text-indigo-600">{{ $grado->nombre }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <x-flash-messages />
        @php
    // Combina las asignaciones de la BD con el input 'old' para repoblar los selects
    $finalAsignaciones = array_merge(
        $asignacionesActuales->toArray(),
        old('materias', [])
    );

    // Obtiene los checkboxes marcados del input 'old'
    // Si no hay 'old' input (primera carga), usa las claves de las asignaciones de la BD
    $finalSeleccionados = old('seleccionados', $asignacionesActuales->keys()->toArray());
@endphp
            <form action="{{ route('grados.estructura.update', $grado) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6 border-b">
                        <p class="text-gray-600 mb-4">
                            Selecciona las materias de este grado y asigna su campo formativo.
                        </p>
                        @if ($errors->has('materias.*'))
                            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100" role="alert">
                                <span class="font-medium">¡Error de validación!</span> {{ $errors->first('materias.*') }}
                            </div>
                        @endif
                        
                <div x-data="{ 
                            search: '', 
                            materias: {{ $materiasDisponibles->toJson() }},
                            asignaciones: {{ json_encode($finalAsignaciones) }},
                            seleccionados: {{ json_encode(array_map('strval', $finalSeleccionados)) }}
                        }">
                                <input type="text" x-model.debounce.300ms="search" placeholder="Buscar materia por nombre..." 
                                   class="w-full sm:w-2/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mb-6">

                            <div class="space-y-4">
                                <template x-for="materia in materias.filter(m => m.nombre.toLowerCase().includes(search.toLowerCase()))" :key="materia.materia_id">
                                    <div class="grid grid-cols-3 items-center gap-4 p-4 border rounded-lg hover:bg-gray-50 transition">
                                        <!-- Checkbox -->
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox"
                                                name="seleccionados[]"
                                                :value="materia.materia_id"
                                                :checked="seleccionados.includes(String(materia.materia_id))"
                                                class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            >
                                            <span class="ml-4 text-gray-800 font-medium" x-text="materia.nombre"></span>
                                        </div>

                                        <!-- Select de Campo Formativo -->
                                        <div class="col-span-2">
                                         <select 
                                                :name="'materias[' + materia.materia_id + ']'" 
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring-opacity-50 text-sm"
                                                {{-- Usamos el `String()` para asegurar la compatibilidad con las claves del JSON --}}
                                                x-model="asignaciones[String(materia.materia_id)]">
                                                <option value="">-- Selecciona un Campo Formativo --</option>
                                                @foreach ($camposFormativos as $campo)
                                                    <option value="{{ $campo->campo_id }}">{{ $campo->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="materias.filter(m => m.nombre.toLowerCase().includes(search.toLowerCase())).length === 0" class="text-center p-4 border rounded-lg text-gray-500">
                                    No se encontraron materias que coincidan con la búsqueda.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 border-t flex justify-end gap-4">
                        <a href="{{ route('grados.index', ['nivel' => $grado->nivel_id]) }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">
                            Guardar Estructura
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>