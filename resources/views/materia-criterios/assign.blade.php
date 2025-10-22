{{-- resources/views/materia-criterios/assign.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignar Criterios a: <span class="font-bold">{{ $materia->nombre }}</span>
        </h2>
    </x-slot>
    
    {{-- Inicializamos la variable de estado para edición --}}
    <div class="py-12" x-data="{ currentCriterio: {} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            
            <div class="bg-white shadow-xl sm:rounded-lg p-6">
                
                <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                    Criterios configurados para la materia
                </h3>
                
                <p class="text-gray-500 mb-6">
                    Criterios Base disponibles: {{ $criteriosBase->pluck('nombre')->join(', ') }}
                </p>

                {{-- Lista de Criterios Asignados --}}
                @if ($criteriosAsignados->isEmpty())
                    <p class="text-gray-500 mb-6">No hay criterios asignados para esta materia.</p>
                @else
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criterio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ponderación (0.00-1.00)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incluye Promedio</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($criteriosAsignados as $criterio)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $criterio->catalogoCriterio->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $criterio->ponderacion }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $criterio->incluido_en_promedio ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $criterio->incluido_en_promedio ? 'Sí (1)' : 'No (0)' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        
                                        {{-- ✅ SECCIÓN DE ACCIONES CORREGIDA CON ESTILO MAESTROS --}}
                                        <div class="flex gap-2 justify-end">
                                            
                                            {{-- ✅ BOTÓN EDITAR (Abre Modal) --}}
                                            <button
                                                type="button"
                                                x-on:click.prevent='
                                                    $dispatch("open-modal", "edit-criterio"); 
                                                    // Usamos json_encode() para serializar la data de PHP a un objeto JavaScript válido
                                                    currentCriterio = {{ json_encode([
                                                        'materia_criterio_id' => $criterio->materia_criterio_id,
                                                        'ponderacion' => $criterio->ponderacion,
                                                        'incluido_en_promedio' => $criterio->incluido_en_promedio,
                                                        'nombre_criterio' => $criterio->catalogoCriterio->nombre,
                                                        'materia_id' => $criterio->materia_id,
                                                        'catalogo_criterio_id' => $criterio->catalogo_criterio_id,
                                                    ]) }};
                                                '
                                                class="bg-blue-100 text-blue-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-150 transition-transform"
                                                title="Editar Ponderación">
                                                <svg class="size-6"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
                                            </button>
                                            
                                            {{-- Formulario para eliminar --}}
                                            <form method="POST" action="{{ route('admin.materia-criterios.destroy', $criterio->materia_criterio_id) }}" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este criterio asignado?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    class="bg-red-100 text-red-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-150 transition-transform"
                                                    title="Eliminar Criterio">
                                                    <svg class="size-6"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                
                {{-- Formulario para Añadir Nuevo Criterio (POST simple) --}}
                <form method="POST" action="{{ route('admin.materia-criterios.store') }}" class="bg-gray-50 p-4 rounded-lg border mt-8" x-data="{ includedInAverage: '1' }">
                    @csrf
                    <input type="hidden" name="materia_id" value="{{ $materia->materia_id }}">
                    
                    <h4 class="text-md font-semibold mb-4">Añadir Nuevo Criterio</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        
                        {{-- Criterio Base Selector --}}
                        <div>
                            <label for="catalogo_criterio_id" class="block text-sm font-medium text-gray-700">Criterio Base</label>
                            <select name="catalogo_criterio_id" id="catalogo_criterio_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Selecciona un criterio...</option>
                                @foreach ($criteriosBase as $criterioBase)
                                    @php
                                        // Verificar si el criterio ya está asignado a la materia
                                        $isAssigned = $criteriosAsignados->contains('catalogo_criterio_id', $criterioBase->catalogo_criterio_id);
                                    @endphp
                                    @if (!$isAssigned)
                                        <option value="{{ $criterioBase->catalogo_criterio_id }}" {{ old('catalogo_criterio_id') == $criterioBase->catalogo_criterio_id ? 'selected' : '' }}>
                                            {{ $criterioBase->nombre }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('catalogo_criterio_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        {{-- Ponderación (0.00-1.00) --}}
                        <div>
                            <label for="ponderacion" class="block text-sm font-medium text-gray-700">Ponderación (0.00-1.00)</label>
                            <input type="number" name="ponderacion" id="ponderacion" min="0.01" max="1.00" step="0.01" 
                                   value="{{ old('ponderacion') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
                                   x-bind:disabled="includedInAverage === '0'" 
                                   x-bind:required="includedInAverage === '1'">
                            @error('ponderacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        {{-- Incluido en Promedio (Radio Buttons) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Incluir Promedio</label>
                            <div class="flex space-x-4 mt-1.5">
                                <div class="flex items-center">
                                    <input type="radio" x-model="includedInAverage" id="incluir_si" name="incluido_en_promedio" value="1" 
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                        {{ old('incluido_en_promedio', '1') == '1' ? 'checked' : '' }} required>
                                    <label for="incluir_si" class="ml-2 text-sm font-medium text-gray-700">Sí</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" x-model="includedInAverage" id="incluir_no" name="incluido_en_promedio" value="0" 
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                        {{ old('incluido_en_promedio') == '0' ? 'checked' : '' }} required>
                                    <label for="incluir_no" class="ml-2 text-sm font-medium text-gray-700">No</label>
                                </div>
                            </div>
                            @error('incluido_en_promedio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        {{-- Botón Agregar --}}
                        <div class="flex items-center">
                            <x-primary-button type="submit">AGREGAR CRITERIO</x-primary-button>
                        </div>
                    </div>
                </form>

            </div>
            
            <div class="mt-8">
                <x-secondary-link-button :href="route('admin.materias.index')">
                    ← Volver a Materias
                </x-secondary-link-button>
            </div>
        </div>

        {{-- MODAL: EDITAR CRITERIO ASIGNADO --}}
        <x-modal name="edit-criterio" :show="$errors->update->isNotEmpty()" focusable>
             <form method="post" 
                   x-bind:action="currentCriterio.materia_criterio_id ? `{{ url('admin/materia-criterios') }}/${currentCriterio.materia_criterio_id}` : ''" 
                   class="p-6">
                @csrf
                @method('PATCH')

                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Editar Ponderación: 
                    <span x-text="currentCriterio.nombre_criterio" class="font-bold"></span>
                </h2>

                {{-- Campos Ocultos Necesarios para el Controller --}}
                <input type="hidden" name="materia_id" x-bind:value="currentCriterio.materia_id" />
                <input type="hidden" name="catalogo_criterio_id" x-bind:value="currentCriterio.catalogo_criterio_id" />

                {{-- Ponderación --}}
                <div>
                    <x-input-label for="ponderacion_edit" value="Ponderación (0.00-1.00)" />
                    <input type="number" name="ponderacion" id="ponderacion_edit" min="0.01" max="1.00" step="0.01" 
                           x-bind:value="currentCriterio.ponderacion" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    @error('ponderacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                {{-- Incluido en Promedio (Radio Buttons) --}}
                <div class="mt-4">
                    <x-input-label value="Incluir en Promedio" />
                    <div class="flex space-x-4 mt-1.5">
                        <div class="flex items-center">
                            <input type="radio" id="incluir_si_edit" name="incluido_en_promedio" value="1" 
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                x-bind:checked="currentCriterio.incluido_en_promedio == 1" required>
                            <label for="incluir_si_edit" class="ml-2 text-sm font-medium text-gray-700">Sí</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="incluir_no_edit" name="incluido_en_promedio" value="0" 
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                x-bind:checked="currentCriterio.incluido_en_promedio == 0" required>
                            <label for="incluir_no_edit" class="ml-2 text-sm font-medium text-gray-700">No</label>
                        </div>
                    </div>
                    @error('incluido_en_promedio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end mt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button class="ml-3">Actualizar Ponderación</x-primary-button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>