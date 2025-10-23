{{-- resources/views/materia-criterios/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center" x-data="{ currentCriterio: {} }"> {{-- x-data movido aquí --}}
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Catálogo de Criterios de Evaluación Base
            </h2>
            
            <div class="flex space-x-4">
                {{-- Botón Volver --}}
                <x-secondary-link-button :href="route('admin.materias.index')" title="Volver a la Gestión de Materias">
                    ← Volver
                </x-secondary-link-button>

                {{-- Botón Crear Criterio --}}
                <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'create-criterio')">
                    + Crear Criterio
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    {{-- Inicializamos currentCriterio aquí --}}
    <div class="py-12" x-data="{ currentCriterio: {} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if ($criterios->isEmpty())
                        <p class="text-center text-gray-500">No hay criterios de evaluación base registrados.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Criterio</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($criterios as $criterio)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $criterio->nombre }}</td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center space-x-2">
                                                    
                                                    {{-- Botón Editar --}}
                                                    <button
                                                        x-on:click.prevent='
                                                            $dispatch("open-modal", "edit-criterio");
                                                            currentCriterio = {{ json_encode($criterio->only('catalogo_criterio_id', 'nombre')) }};
                                                         '
                                                        class="bg-blue-100 text-blue-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-125 transition-transform"
                                                        title="Editar Criterio">
                                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    </button>
                                                    
                                                    {{-- --- CORRECCIÓN EN EL FORMULARIO ELIMINAR --- --}}
                                                    {{-- Formulario Eliminar Criterio Base --}}
                                                    {{-- Usa la ruta 'admin.materia-criterios.destroy' y pasa el ID --}}
                                                    <form action="{{ route('admin.materia-criterios.destroy', $criterio->catalogo_criterio_id) }}" method="POST" onsubmit="return confirm('¿Eliminar el criterio \'{{ $criterio->nombre }}\'? Esto puede afectar a las materias asignadas.');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="bg-red-100 text-red-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-125 transition-transform"
                                                                title="Eliminar Criterio">
                                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                     {{-- --- FIN DE LA CORRECCIÓN --- --}}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- MODAL: CREAR CRITERIO BASE (CRUD) --}}
        <x-modal name="create-criterio" :show="$errors->store->isNotEmpty()" focusable>
            <form method="POST" action="{{ route('admin.materia-criterios.store') }}" class="p-6"> {{-- Store usa la ruta genérica --}}
                @csrf
                <h2 class="text-lg font-medium text-gray-900 mb-4">Crear Nuevo Criterio Base</h2>
                
                <div>
                    <x-input-label for="nombre_create" value="Nombre del Criterio" />
                    <x-text-input id="nombre_create" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->store->get('nombre')" />
                </div>
                
                <div class="flex items-center justify-end mt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button class="ml-3">Guardar Criterio</x-primary-button>
                </div>
            </form>
        </x-modal>
        
        {{-- MODAL: EDITAR CRITERIO BASE (CRUD) --}}
        {{-- Usa la ruta genérica admin.materia-criterios.update con el ID --}}
        <x-modal name="edit-criterio" :show="$errors->update->isNotEmpty()" focusable>
             <form method="post"
                  x-bind:action="currentCriterio.catalogo_criterio_id ? `{{ url('admin/materia-criterios') }}/${currentCriterio.catalogo_criterio_id}` : ''"
                  class="p-6">
                @csrf
                @method('PATCH') {{-- O PUT, según tu ruta --}}

                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Editar Criterio Base
                </h2>

                <div>
                    <x-input-label for="nombre_edit" value="Nombre del Criterio" />
                    <x-text-input id="nombre_edit" name="nombre" type="text" class="mt-1 block w-full" x-bind:value="currentCriterio.nombre" required />
                    <x-input-error class="mt-2" :messages="$errors->update->get('nombre')" />
                </div>
                
                <div class="flex items-center justify-end mt-6">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button class="ml-3">Actualizar Criterio</x-primary-button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>