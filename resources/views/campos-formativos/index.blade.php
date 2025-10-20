<x-app-layout>
    <x-slot name="header">
        {{-- Contenedor principal del header: vertical y con espacio --}}
        <div class="flex flex-col space-y-4">
            {{-- PRIMERA LÍNEA: Título vs. Botones de Acción --}}
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Campos Formativos
                </h2>
                <div class="flex items-center space-x-4">
                    <x-secondary-link-button href="{{ route('materias.index') }}">
                        + Ver materias
                    </x-secondary-link-button>
                    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-campo')">
                        + Crear campo formativo
                    </x-primary-button>
                </div>
            </div>
            {{-- SEGUNDA LÍNEA: Botones de Filtro --}}
            <div class="flex items-center space-x-2">
                @php
                    $baseClass = 'inline-flex items-center px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150';
                    $activeClass = 'bg-gray-800 text-white shadow-sm';
                    $inactiveClass = 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50';
                @endphp
                @foreach ($niveles as $nivel)
                    <a href="{{ route('campos-formativos.index', ['nivel' => $nivel->nivel_id]) }}"
                       class="{{ $baseClass }} {{ $nivel->nivel_id == $activeNivelId ? $activeClass : $inactiveClass }}">
                        {{ $nivel->nombre }}
                    </a>
                @endforeach
                <a href="{{ route('campos-formativos.index', ['nivel' => 0]) }}"
                   class="{{ $baseClass }} {{ $activeNivelId == '0' ? $activeClass : $inactiveClass }}">
                    Sin Asignar
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Contenido Principal - Quitamos allMaterias del x-data --}}
    <div class="py-12" x-data="{ currentCampo: {}, selectedCampo: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($camposFormativos as $campo)
                    {{-- Tarjeta --}}
                    <div class="bg-gray-800 text-white rounded-lg shadow-lg p-6 flex flex-col justify-between min-h-[160px] relative">
                        {{-- Título --}}
                        <h3 class="text-xl font-semibold mb-4">{{ $campo->nombre }}</h3>

                        {{-- Iconos --}}
                        <div class="flex justify-end items-center space-x-3 mt-auto">
                            {{-- Botón: Ver Materias --}}
                             <button
                                 type="button"
                                 class="text-gray-400 hover:text-green-400 p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500"
                                 title="Ver Materias"
                                 x-on:click.prevent="selectedCampo = {{ $campo->toJson() }}; $dispatch('open-modal', 'materias-modal')"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h7.5M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </button>
                            {{-- Botón Editar --}}
                            <button
                                x-on:click.prevent="$dispatch('open-modal', 'edit-campo'); currentCampo = {{ $campo }};"
                                class="text-gray-400 hover:text-blue-400 p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                                title="Editar"
                            >
                               <svg class="w-5 h-5"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
                            </button>
                            {{-- Formulario Eliminar --}}
                            <form action="{{ route('campos-formativos.destroy', $campo) }}" method="POST" onsubmit="return confirm('¿Eliminar campo formativo \'{{ $campo->nombre }}\'?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-400 p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500" title="Eliminar">
                                   <svg class="w-5 h-5"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($camposFormativos->isEmpty())
                <div class="bg-white rounded-lg shadow-lg p-6 text-center text-gray-500 mt-6">
                    No se encontraron campos formativos para el filtro seleccionado.
                </div>
            @endif
        </div>

        {{-- Modales Create y Edit --}}
        <x-modal name="create-campo" :show="false" focusable>
            <form method="post" action="{{ route('campos-formativos.store') }}" class="p-6">
                @csrf
                <h2 class="text-lg font-medium text-gray-900">Crear Nuevo Campo Formativo</h2>
                <div class="mt-6">
                    <x-input-label for="nombre_create" value="Nombre" />
                    <x-text-input id="nombre_create" name="nombre" type="text" class="mt-1 block w-full" required autofocus />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button class="ml-3">Guardar</x-primary-button>
                </div>
            </form>
        </x-modal>

        <x-modal name="edit-campo" :show="false" focusable>
             <form method="post" x-bind:action="currentCampo ? `{{ url('campos-formativos') }}/${currentCampo.campo_id}` : ''" class="p-6">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-medium text-gray-900">Editar Campo Formativo</h2>
                <div class="mt-6">
                    <x-input-label for="nombre_edit" value="Nombre" />
                     <x-text-input id="nombre_edit" name="nombre" type="text" class="mt-1 block w-full" x-bind:value="currentCampo?.nombre" required />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button class="ml-3">Actualizar</x-primary-button>
                </div>
            </form>
        </x-modal>

        {{-- Modal Ver Materias --}}
        <x-modal name="materias-modal" :show="false" focusable>
            <div class="p-6">
                {{-- --- CORRECCIÓN: Botón Añadir Materia ELIMINADO --- --}}
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900" x-show="selectedCampo">
                        Materias en <span x-text="selectedCampo?.nombre"></span>
                    </h2>
                    {{-- <x-secondary-button ...>+ Añadir Materia</x-secondary-button> --}}
                </div>
                {{-- --- FIN CORRECCIÓN --- --}}

                <div x-show="selectedCampo">
                    <p x-show="!selectedCampo?.materias || selectedCampo?.materias.length === 0" class="text-gray-500">
                        No hay materias asignadas directamente a este campo.
                    </p>
                    <div x-show="selectedCampo?.materias && selectedCampo?.materias.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                           <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materia</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesor Asignado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="materia in selectedCampo?.materias" :key="materia.materia_id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="materia.nombre"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span x-text="materia.asignaciones_grupo && materia.asignaciones_grupo.length > 0 ? (materia.asignaciones_grupo[0].maestro ? materia.asignaciones_grupo[0].maestro.name : 'N/A') : 'Sin Asignar'"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span x-text="materia.asignaciones_grupo && materia.asignaciones_grupo.length > 0 ? (materia.asignaciones_grupo[0].grupo && materia.asignaciones_grupo[0].grupo.grado ? materia.asignaciones_grupo[0].grupo.grado.nombre : 'N/A') : 'N/A'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">Cerrar</x-secondary-button>
                </div>
            </div>
        </x-modal>

        {{-- --- CORRECCIÓN: Modal assign-subjects-modal ELIMINADO --- --}}
        {{-- <x-modal name="assign-subjects-modal" :show="false" focusable> ... </x-modal> --}}
        {{-- --- FIN CORRECCIÓN --- --}}

    </div> {{-- Fin del div py-12 --}}
</x-app-layout>