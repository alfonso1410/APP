{{-- resources/views/materia-criterios/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Definir Nuevo Criterio Base
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                {{-- Formulario de Creación --}}
                <form method="POST" action="{{ route('admin.materia-criterios.store') }}" class="pb-6 border-b border-gray-200 mb-6">
                    @csrf
                    
                    <div>
                        <x-input-label for="nombre" value="Nombre del Criterio" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" placeholder="Ej: Examen Escrito, Tareas, Participación en Clase" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>

                    {{-- ❌ CAMPO ELIMINADO: Se ha quitado el bloque de 'descripcion' --}}

                    <div class="flex items-center justify-end mt-6">
                        <x-secondary-link-button :href="route('admin.materias.index')" class="mr-4">
                            Cancelar
                        </x-secondary-link-button>
                        <x-primary-button>
                            Guardar Criterio
                        </x-primary-button>
                    </div>
                </form>
                
                {{-- LISTA DE CRITERIOS EXISTENTES --}}
                <h3 class="text-lg font-semibold mb-4">Criterios Existentes ({{ \App\Models\CatalogoCriterio::count() }})</h3>
                <div class="mt-4">
                    @php
                        // Obtenemos la lista para mostrarla directamente en esta vista
                        $criteriosExistentes = \App\Models\CatalogoCriterio::orderBy('nombre')->get();
                    @endphp
                    
                    @if ($criteriosExistentes->isEmpty())
                         <p class="text-gray-500">No hay criterios base registrados. Utiliza el formulario superior para añadir el primero.</p>
                    @else
                        <div class="overflow-x-auto">
                            <ul class="divide-y divide-gray-200">
                                @foreach ($criteriosExistentes as $criterio)
                                    <li class="py-2 text-sm text-gray-700 font-medium flex justify-between items-center">
                                        <span>{{ $criterio->nombre }}</span>
                                        {{-- Aquí puedes añadir botones de acción como Editar o Eliminar --}}
                                        {{-- <span class="text-xs text-gray-400">Acciones</span> --}}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>