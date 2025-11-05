<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Niveles Educativos</h2>
            <a href="{{ route('admin.grados.index') }}"
               class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg shadow-md hover:bg-gray-300 transition">
                &larr; Volver a Grados y Grupos
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Nivel</th>
                                {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orden</th> --}} {{-- <-- ELIMINADO --}}
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($niveles as $nivel)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $nivel->nombre }}</td>
                                    {{-- <td class="px-6 py-4 text-sm text-gray-500">{{ $nivel->orden }}</td> --}} {{-- <-- ELIMINADO --}}
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-x-4">
                                            {{-- Botón Editar --}}
                                            <button 
                                                x-data 
                                                x-on:click.prevent="$dispatch('open-modal', 'editar-nivel-{{ $nivel->nivel_id }}')"
                                                class="bg-blue-100 text-blue-800 p-1 flex size-4 sm:size-6 items-center justify-center rounded-full hover:scale-150 transition-transform"
                                                title="Editar Nivel"
                                            >
                                                <svg class="size-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
                                            </button>

                                            {{-- Botón Eliminar --}}
                                            <form method="POST" action="{{ route('admin.niveles.destroy', $nivel) }}" onsubmit="return confirm('¿Seguro que quieres eliminar el nivel \'{{ $nivel->nombre }}\'? No se podrá si tiene grados asociados.');">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit"
                                                    class="bg-red-100 text-red-800 p-1 flex size-4 sm:size-6 items-center justify-center rounded-full hover:scale-150 transition-transform"
                                                    title="Eliminar Nivel"
                                                >
                                                    <svg class="size-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- CAMBIO AQUÍ: Colspan es 2 ahora --}}
                                    <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                                        No se encontraron niveles educativos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALES PARA EDITAR --}}
    @foreach ($niveles as $nivel)
        <x-modal 
            :name="'editar-nivel-' . $nivel->nivel_id" 
            :show="$errors->any() && old('nivel_id') == $nivel->nivel_id" 
            focusable
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Editar Nivel: {{ $nivel->nombre }}</h2>
                
                <form method="POST" action="{{ route('admin.niveles.update', $nivel) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    <input type="hidden" name="nivel_id" value="{{ $nivel->nivel_id }}">

                    <div>
                        <x-input-label for="nombre_{{ $nivel->nivel_id }}" value="Nombre del Nivel" />
                        <x-text-input 
                            id="nombre_{{ $nivel->nivel_id }}" 
                            name="nombre" 
                            type="text" 
                            class="mt-1 block w-full" 
                            :value="old('nombre', $nivel->nombre)" 
                            required 
                        />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                    </div>

                

                    <div class="flex justify-end gap-4 mt-6">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            Cancelar
                        </x-secondary-button>
                        <x-primary-button>
                            Guardar Cambios
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endforeach
    
</x-app-layout>