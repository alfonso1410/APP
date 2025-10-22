<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Alumnos') }}
        </h2>
    </x-slot>

    {{-- Añadido x-data para el modal de edición --}}
    <div class="py-12" x-data="{ currentAlumno: {} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Alerta de éxito --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
             {{-- Alerta de errores generales --}}
             @if ($errors->any() && !$errors->store->any() && !$errors->update->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <ul class="mt-3 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <div class="flex items-center justify-between mb-4 gap-4">
                {{-- Barra de Búsqueda --}}
                <div class="w-full sm:w-2/3">
                    <form action="{{ route('admin.alumnos.index') }}" method="GET">
                        <input type="hidden" name="nivel" value="{{ $nivel_id }}">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por nombre, apellidos o CURP..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </form>
                </div>
                {{-- Botón para abrir modal de agregar --}}
                <button x-data x-on:click.prevent="$dispatch('open-modal', 'agregar-alumno')" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                    Agregar Alumno
                </button>
            </div>

            {{-- Componente de filtro --}}
            <x-level-filter :route="'admin.alumnos.index'" :selectedNivel="$nivel_id" />

            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg mt-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CURP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extracurricular</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($alumnos as $alumno)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $alumno->curp }}</td>
                                    {{-- ===== LÍNEA 61 CORREGIDA ===== --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $alumno->grupos->where('pivot.es_actual', 1)->where('tipo_grupo', 'REGULAR')->first()?->grado?->nombre ?? 'Sin asignar' }}
                                    </td>
                                    {{-- ============================== --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $alumno->grupos->where('pivot.es_actual', 1)->where('tipo_grupo', 'REGULAR')->first()?->nombre_grupo ?? 'Sin asignar' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $alumno->grupos->where('pivot.es_actual', 1)->where('tipo_grupo', 'EXTRA')->first()?->nombre_grupo ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($alumno->estado_alumno === 'ACTIVO')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-2">
                                            {{-- Botón Editar (Estilo Original + Modal) --}}
                                            <button
                                                type="button"
                                                x-on:click.prevent='$dispatch("open-modal", "editar-alumno-{{ $alumno->alumno_id }}"); currentAlumno = @json($alumno)'
                                                class="p-1 flex size-4 sm:size-6 items-center justify-center rounded-full bg-blue-100 text-blue-800 hover:scale-150 transition-transform"
                                                title="Editar Alumno"
                                            >
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
                                            </button>

                                            {{-- Botón Eliminar (Estilo Original) --}}
                                            <form method="POST" action="{{ route('admin.alumnos.destroy', $alumno) }}" onsubmit="return confirm('¿Estás seguro de que deseas INACTIVAR a este alumno?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="p-1 flex size-4 sm:size-6 items-center justify-center rounded-full bg-red-100 text-red-800 hover:scale-150 transition-transform"
                                                    title="Inactivar Alumno"
                                                >
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal de Edición DENTRO del bucle --}}
                                <x-modal :name="'editar-alumno-'.$alumno->alumno_id" :show="$errors->update->isNotEmpty() && old('alumno_id_error_key') == $alumno->alumno_id" focusable>
                                    <form method="POST" action="{{ route('admin.alumnos.update', $alumno) }}" class="p-6">
                                        @method('PUT')
                                        @csrf
                                        <input type="hidden" name="current_nivel_id" value="{{ request()->input('nivel', 0) }}">
                                        <input type="hidden" name="alumno_id_error_key" value="{{ $alumno->alumno_id }}">
                                        <h2 class="text-lg font-medium text-gray-900 mb-4">Editar Alumno: {{ $alumno->nombres }} {{ $alumno->apellido_paterno }}</h2>
                                        {{-- Nombres --}}
                                        <div class="mt-4">
                                            <x-input-label for="edit_{{ $alumno->alumno_id }}_nombres" :value="__('Nombre(s)')" />
                                            <x-text-input id="edit_{{ $alumno->alumno_id }}_nombres" class="block mt-1 w-full" type="text" name="nombres" :value="old('nombres', $alumno->nombres)" required autofocus />
                                            @if(old('alumno_id_error_key') == $alumno->alumno_id)<x-input-error :messages="$errors->update->get('nombres')" class="mt-2" />@endif
                                        </div>
                                        {{-- Apellido Paterno --}}
                                        <div class="mt-4">
                                             <x-input-label for="edit_{{ $alumno->alumno_id }}_apellido_paterno" :value="__('Apellido Paterno')" />
                                             <x-text-input id="edit_{{ $alumno->alumno_id }}_apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $alumno->apellido_paterno)" required />
                                             @if(old('alumno_id_error_key') == $alumno->alumno_id)<x-input-error :messages="$errors->update->get('apellido_paterno')" class="mt-2" />@endif
                                        </div>
                                        {{-- Apellido Materno --}}
                                        <div class="mt-4">
                                             <x-input-label for="edit_{{ $alumno->alumno_id }}_apellido_materno" :value="__('Apellido Materno')" />
                                             <x-text-input id="edit_{{ $alumno->alumno_id }}_apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $alumno->apellido_materno)" required />
                                             @if(old('alumno_id_error_key') == $alumno->alumno_id)<x-input-error :messages="$errors->update->get('apellido_materno')" class="mt-2" />@endif
                                        </div>
                                        {{-- Fecha Nacimiento --}}
                                         <div class="mt-4">
                                             <x-input-label for="edit_{{ $alumno->alumno_id }}_fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                                             <x-text-input id="edit_{{ $alumno->alumno_id }}_fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento', $alumno->fecha_nacimiento)" required />
                                             @if(old('alumno_id_error_key') == $alumno->alumno_id)<x-input-error :messages="$errors->update->get('fecha_nacimiento')" class="mt-2" />@endif
                                        </div>
                                        {{-- CURP --}}
                                        <div class="mt-4">
                                             <x-input-label for="edit_{{ $alumno->alumno_id }}_curp" :value="__('CURP')" />
                                             <x-text-input id="edit_{{ $alumno->alumno_id }}_curp" class="block mt-1 w-full uppercase" type="text" name="curp" :value="old('curp', $alumno->curp)" required pattern="[A-Z]{4}[0-9]{6}[H,M][A-Z]{5}[A-Z0-9]{2}" title="Formato CURP inválido" maxlength="18" />
                                             @if(old('alumno_id_error_key') == $alumno->alumno_id)<x-input-error :messages="$errors->update->get('curp')" class="mt-2" />@endif
                                        </div>
                                        {{-- Estado --}}
                                        <div class="mt-4">
                                             <x-input-label for="edit_{{ $alumno->alumno_id }}_estado_alumno" :value="__('Estado del Alumno')" />
                                             <select name="estado_alumno" id="edit_{{ $alumno->alumno_id }}_estado_alumno" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                 <option value="ACTIVO" {{ old('estado_alumno', $alumno->estado_alumno) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                                                 <option value="INACTIVO" {{ old('estado_alumno', $alumno->estado_alumno) === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                                             </select>
                                             @if(old('alumno_id_error_key') == $alumno->alumno_id)<x-input-error :messages="$errors->update->get('estado_alumno')" class="mt-2" />@endif
                                        </div>
                                        {{-- Botones --}}
                                        <div class="flex items-center justify-end mt-6">
                                            <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                            <x-primary-button class="ms-4">Actualizar Alumno</x-primary-button>
                                        </div>
                                    </form>
                                </x-modal>

                            @empty
                                <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No se encontraron alumnos...</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="bg-white px-4 py-3 border-t">
                    {{ $alumnos->appends(['nivel' => $nivel_id, 'search' => $search])->links() }}
                </div>
            </div>

            {{-- Modal AGREGAR ALUMNO (Fuera del bucle) --}}
            <x-modal name="agregar-alumno" :show="$errors->store->isNotEmpty()" focusable>
                 <form method="POST" action="{{ route('admin.alumnos.store') }}" class="p-6">
                     @csrf
                     <input type="hidden" name="current_nivel_id" value="{{ request()->input('nivel', 0) }}">
                     <h2 class="text-lg font-medium text-gray-900 mb-4">Agregar Nuevo Alumno</h2>
                     {{-- Nombres --}}
                     <div class="mt-4">
                         <x-input-label for="create_nombres" :value="__('Nombre(s)')" />
                         <x-text-input id="create_nombres" class="block mt-1 w-full" type="text" name="nombres" :value="old('nombres')" required autofocus />
                         @if(!$errors->update->any())<x-input-error :messages="$errors->store->get('nombres')" class="mt-2" />@endif
                     </div>
                     {{-- Apellido Paterno --}}
                     <div class="mt-4">
                         <x-input-label for="create_apellido_paterno" :value="__('Apellido Paterno')" />
                         <x-text-input id="create_apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required />
                          @if(!$errors->update->any())<x-input-error :messages="$errors->store->get('apellido_paterno')" class="mt-2" />@endif
                     </div>
                    {{-- Apellido Materno --}}
                    <div class="mt-4">
                         <x-input-label for="create_apellido_materno" :value="__('Apellido Materno')" />
                         <x-text-input id="create_apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" required />
                         @if(!$errors->update->any())<x-input-error :messages="$errors->store->get('apellido_materno')" class="mt-2" />@endif
                     </div>
                     {{-- Fecha Nacimiento --}}
                     <div class="mt-4">
                         <x-input-label for="create_fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                         <x-text-input id="create_fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento')" required />
                          @if(!$errors->update->any())<x-input-error :messages="$errors->store->get('fecha_nacimiento')" class="mt-2" />@endif
                     </div>
                     {{-- CURP --}}
                     <div class="mt-4">
                         <x-input-label for="create_curp" :value="__('CURP')" />
                         <x-text-input id="create_curp" class="block mt-1 w-full uppercase" type="text" name="curp" :value="old('curp')" required pattern="[A-Z]{4}[0-9]{6}[H,M][A-Z]{5}[A-Z0-9]{2}" title="Formato CURP inválido" maxlength="18" />
                         @if(!$errors->update->any())<x-input-error :messages="$errors->store->get('curp')" class="mt-2" />@endif
                     </div>
                     {{-- Estado (Oculto) --}}
                     <input type="hidden" name="estado_alumno" value="ACTIVO">
                     {{-- Botones --}}
                     <div class="flex items-center justify-end mt-6">
                         <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                         <x-primary-button class="ms-4">Guardar Alumno</x-primary-button>
                     </div>
                 </form>
            </x-modal>

        </div>
    </div>
</x-app-layout>