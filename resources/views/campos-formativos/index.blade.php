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
            </div>
        </div>
    </x-slot>

    {{-- Contenido Principal --}}
    <div class="py-12" x-data="{ currentCampo: {}, selectedCampo: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($camposFormativos as $campo)
                    {{-- Tarjeta --}}
                    <div class="bg-gray-800 text-white rounded-lg shadow-lg p-6 flex flex-col justify-between min-h-[160px] relative">
                        <h3 class="text-xl font-semibold mb-4">{{ $campo->nombre }}</h3>

                        <div class="flex justify-end items-center space-x-3 mt-auto">

                            {{-- Botón: Ver Materias --}}
                             <button
                                 type="button"
                                 class="text-gray-400 hover:text-green-400 p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500"
                                 title="Ver Materias"
                                 {{-- Usamos @json y comillas simples/dobles correctas --}}
                                 x-on:click.prevent='selectedCampo = @json($campo); $dispatch("open-modal", "materias-modal")'
                             >
                                {{-- Icono SVG simplificado --}}
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                            </button>

                            {{-- Botón Editar --}}
                            <button
                                {{-- Usamos @json y comillas simples/dobles correctas --}}
                                x-on:click.prevent='$dispatch("open-modal", "edit-campo"); currentCampo = @json($campo);'
                                class="text-gray-400 hover:text-blue-400 p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                                title="Editar"
                            >
                               {{-- Icono SVG simplificado --}}
                               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>

                            {{-- Formulario Eliminar --}}
                            <form action="{{ route('campos-formativos.destroy', $campo) }}" method="POST" onsubmit="return confirm('¿Eliminar campo formativo \'{{ $campo->nombre }}\'?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-400 p-1 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500" title="Eliminar">
                                    {{-- Icono SVG simplificado --}}
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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

        {{-- Modales CREAR y EDITAR (sin cambios) --}}
        <x-modal name="create-campo" :show="$errors->store->isNotEmpty()" focusable>
             <form method="post" action="{{ route('campos-formativos.store') }}" class="p-6">
                @csrf
                <h2 class="text-lg font-medium text-gray-900">Crear Nuevo Campo Formativo</h2>

                <div class="mt-6">
                    <x-input-label for="nivel_id_create" value="Nivel Educativo" />
                    <select id="nivel_id_create" name="nivel_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                        <option value="" disabled>Seleccione un nivel...</option>
                        @foreach ($niveles as $nivel)
                            <option value="{{ $nivel->nivel_id }}"
                                {{ old('nivel_id', $activeNivelId) == $nivel->nivel_id ? 'selected' : '' }}>
                                {{ $nivel->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->store->get('nivel_id')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="nombre_create" value="Nombre" />
                    <x-text-input id="nombre_create" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required autofocus />
                    <x-input-error :messages="$errors->store->get('nombre')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button class="ml-3">Guardar</x-primary-button>
                </div>
            </form>
        </x-modal>
        <x-modal name="edit-campo" :show="$errors->update->isNotEmpty()" focusable>
             <form method="post" x-bind:action="currentCampo ? `{{ url('campos-formativos') }}/${currentCampo.campo_id}` : ''" class="p-6">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-medium text-gray-900">Editar Campo Formativo</h2>

                <div class="mt-6">
                    <x-input-label for="nivel_id_edit" value="Nivel Educativo" />
                    <select id="nivel_id_edit" name="nivel_id"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                            x-bind:value="currentCampo?.nivel_id"
                            required>
                        <option value="" disabled>Seleccione un nivel...</option>
                        @foreach ($niveles as $nivel)
                            <option value="{{ $nivel->nivel_id }}">{{ $nivel->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->update->get('nivel_id')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="nombre_edit" value="Nombre" />
                    <x-text-input id="nombre_edit" name="nombre" type="text" class="mt-1 block w-full" x-bind:value="currentCampo?.nombre" required />
                    <x-input-error :messages="$errors->update->get('nombre')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button class="ml-3">Actualizar</x-primary-button>
                </div>
            </form>
        </x-modal>


        {{-- Modal Ver Materias (CORREGIDO con <template x-if> y asignaciones_grupo) --}}
        <x-modal name="materias-modal" :show="false" focusable>
            <div class="p-6">
                {{-- Usamos <template x-if> para envolver TODO el contenido dependiente de selectedCampo --}}
                <template x-if="selectedCampo">
                    <div> {{-- Div contenedor necesario dentro del template --}}
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-medium text-gray-900">
                                Materias en <span x-text="selectedCampo.nombre"></span>
                            </h2>
                        </div>

                        <div class="mt-4">
                            <p x-show="!selectedCampo.materias || selectedCampo.materias.length === 0" class="text-gray-500">
                                No hay materias asignadas directamente a este campo.
                            </p>

                            {{-- Usamos <template x-if> también aquí por seguridad --}}
                            <template x-if="selectedCampo.materias && selectedCampo.materias.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materia</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesor Asignado</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            {{-- El :key ahora debería funcionar si se eliminaron duplicados --}}
                                            <template x-for="materia in selectedCampo.materias" :key="materia.materia_id">
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="materia.nombre"></td>

                                                    {{-- CORREGIDO a asignaciones_grupo --}}
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
                            </template> {{-- Fin template x-if materias.length > 0 --}}
                        </div>
                    </div> {{-- Fin div contenedor --}}
                </template> {{-- Fin template x-if selectedCampo --}}

                {{-- Mensaje si selectedCampo aún no está listo --}}
                <p x-show="!selectedCampo" class="text-gray-500">Cargando...</p>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">Cerrar</x-secondary-button>
                </div>
            </div>
        </x-modal>

    </div> {{-- Fin del div py-12 --}}
</x-app-layout>