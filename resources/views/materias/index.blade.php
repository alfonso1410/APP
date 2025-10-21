<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Materias
            </h2>
            <div class="flex space-x-4">
                 <x-secondary-link-button href="{{ route('campos-formativos.index') }}">
                     Ver Campos Formativos
                 </x-secondary-link-button>

                 {{-- ✅ BOTÓN GLOBAL: Navega al Dashboard del Catálogo de Criterios --}}
                 <x-secondary-link-button href="{{ route('materia-criterios.index') }}" title="Gestionar el Catálogo de Criterios de Evaluación Base">
                     Ver Criterios
                 </x-secondary-link-button>

                 <x-primary-button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'create-materia')">
                     + Nueva Materia
                 </x-primary-button>
            </div>
        </div>
    </x-slot>

    {{-- Inicializamos currentMateria para Alpine.js --}}
    <div class="py-12" x-data="{ currentMateria: {} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materia</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campo Formativo</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($materias as $materia)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $materia->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $materia->camposFormativos->first()?->nombre ?? 'N/A' }}
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">

                                                {{-- ✅ BOTÓN POR FILA: Asignar Criterios (apunta a materia-criterios.index con parámetro) --}}
                                                <a href="{{ route('materia-criterios.index', ['materia' => $materia->materia_id]) }}" 
                                                   class="bg-yellow-100 text-yellow-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-125 transition-transform" 
                                                   title="Añadir/Gestionar Criterios por Grado">
                                                     <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </a>

                                                {{-- Botón Editar Materia --}}
                                                <button
                                                     x-on:click.prevent='
                                                         $dispatch("open-modal", "edit-materia"); 
                                                         currentMateria = @json($materia);
                                                      '
                                                     class="bg-blue-100 text-blue-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-125 transition-transform"
                                                     title="Editar Materia">
                                                     <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                 </button>

                                                {{-- Botón Eliminar --}}
                                                <form action="{{ route('materias.destroy', $materia) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar la materia \'{{ $materia->nombre }}\'?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                             class="bg-red-100 text-red-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-125 transition-transform"
                                                             title="Eliminar Materia">
                                                         <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                     </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay materias registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>

        {{-- Modal: CREAR Materia --}}
        <x-modal name="create-materia" :show="$errors->store->isNotEmpty()" focusable>
            <form method="post" action="{{ route('materias.store') }}" class="p-6">
                @csrf
                <h2 class="text-lg font-medium text-gray-900">
                    Crear Nueva Materia
                </h2>
                
                <div class="mt-6">
                    <x-input-label for="nombre_create" value="Nombre de la Materia" />
                    <x-text-input id="nombre_create" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required autofocus />
                    <x-input-error :messages="$errors->store->get('nombre')" class="mt-2" />
                </div>

                {{-- Radio Buttons para TIPO --}}
                <div class="mt-6">
                    <x-input-label value="Tipo de Materia" />
                    <div class="mt-2 space-y-2">
                        {{-- Opción REGULAR (Valor por defecto) --}}
                        <div class="flex items-center">
                            <input id="tipo_regular_create" name="tipo" type="radio" value="REGULAR" 
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                {{ old('tipo', 'REGULAR') === 'REGULAR' ? 'checked' : '' }} required>
                            <label for="tipo_regular_create" class="ml-3 block text-sm font-medium text-gray-700">Regular (Aplica para Boletas)</label>
                        </div>
                        {{-- Opción EXTRA --}}
                        <div class="flex items-center">
                            <input id="tipo_extra_create" name="tipo" type="radio" value="EXTRA" 
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                {{ old('tipo') === 'EXTRA' ? 'checked' : '' }} required>
                            <label for="tipo_extra_create" class="ml-3 block text-sm font-medium text-gray-700">Extracurricular (Solo Registro de Asistencia/Notas)</label>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->store->get('tipo')" class="mt-2" />
                </div>
                
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancelar
                    </x-secondary-button>
                    <x-primary-button class="ml-4">
                        Guardar Materia
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        {{-- Modal: EDITAR Materia --}}
        <x-modal name="edit-materia" :show="$errors->update->isNotEmpty()" focusable>
             <form method="post" x-bind:action="currentMateria ? `{{ url('materias') }}/${currentMateria.materia_id}` : ''" class="p-6">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-medium text-gray-900">
                    Editar Materia
                </h2>
                
                <div class="mt-6">
                    <x-input-label for="nombre_edit" value="Nombre de la Materia" />
                    <x-text-input
                        id="nombre_edit"
                        name="nombre"
                        type="text"
                        class="mt-1 block w-full"
                        x-bind:value="currentMateria?.nombre"
                        required />
                    <x-input-error :messages="$errors->update->get('nombre')" class="mt-2" />
                </div>

                {{-- Radio Buttons para TIPO (Edición) --}}
                <div class="mt-6">
                    <x-input-label value="Tipo de Materia" />
                    <div class="mt-2 space-y-2">
                        {{-- Opción REGULAR --}}
                        <div class="flex items-center">
                            <input id="tipo_regular_edit" name="tipo" type="radio" value="REGULAR" 
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                x-bind:checked="currentMateria.tipo === 'REGULAR'" required>
                            <label for="tipo_regular_edit" class="ml-3 block text-sm font-medium text-gray-700">Regular</label>
                        </div>
                        {{-- Opción EXTRA --}}
                        <div class="flex items-center">
                            <input id="tipo_extra_edit" name="tipo" type="radio" value="EXTRA" 
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                x-bind:checked="currentMateria.tipo === 'EXTRA'" required>
                            <label for="tipo_extra_edit" class="ml-3 block text-sm font-medium text-gray-700">Extracurricular</label>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->update->get('tipo')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancelar
                    </x-secondary-button>
                    <x-primary-button class="ml-4">
                        Actualizar Materia
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

    </div> {{-- Fin del div py-12 --}}
</x-app-layout>