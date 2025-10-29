<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Ciclos Escolares</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-flash-messages />

            {{-- 1. Botón Flotante para Crear --}}
            <div class="fixed bottom-8 right-8 z-50">
                <button
                    x-data=""
                    @click.prevent="$dispatch('open-modal', 'agregar-ciclo')"
                    class="bg-princeton hover:bg-blue-700 text-white font-bold p-4 rounded-full shadow-lg transition-transform hover:scale-105"
                    title="Agregar Ciclo Escolar"
                >
                    <svg class="size-6"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-add"></use></svg>
                </button>
            </div>

            {{-- 2. Tabla de Ciclos Escolares --}}
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre (Ciclo)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($ciclos as $ciclo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ciclo->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($ciclo->fecha_fin)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                'bg-green-100 text-green-800': '{{ $ciclo->estado }}' === 'ACTIVO',
                                                'bg-red-100 text-red-800': '{{ $ciclo->estado }}' !== 'ACTIVO'
                                              }">
                                            {{ $ciclo->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                      <div class="flex items-center justify-center gap-x-2">
                                            {{-- Botón Editar (abre modal) --}}
                                            <button
                                                x-data=""
                                                x-on:click.prevent="$dispatch('open-modal', 'editar-ciclo-{{ $ciclo->ciclo_escolar_id }}')"
                                                class="text-indigo-600 hover:text-indigo-900 mx-1 p-1 rounded-full hover:bg-indigo-100"
                                                title="Editar"
                                            >
                                                <svg class="size-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
                                            </button>
                                            
                                            {{-- INICIO: Botón Ver Periodos --}}
        <a href="{{ route('admin.periodos.index', ['ciclo_escolar_id' => $ciclo->ciclo_escolar_id]) }}"
           class="text-green-600 hover:text-green-900 mx-1 p-1 rounded-full hover:bg-green-100"
           title="Ver Periodos de este Ciclo">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        </a>

                                            {{-- Botón Eliminar/Desactivar (usa un formulario) --}}
                                            {{-- Usaremos un componente similar al de grados para confirmación --}}
                                            <form action="{{ route('admin.ciclo-escolar.destroy', $ciclo) }}" method="POST" onsubmit="return confirm('¿Estás seguro? Si el ciclo tiene grupos o periodos, solo se marcará como CERRADO.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900 mx-1 p-1 rounded-full hover:bg-red-100"
                                                        title="Eliminar o Desactivar">
                                                    <svg class="size-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay ciclos escolares registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    {{-- INICIO MODALES EDITAR --}}
    @foreach ($ciclos as $ciclo)
        <x-modal
            :name="'editar-ciclo-' . $ciclo->ciclo_escolar_id"
            :show="$errors->any() && old('ciclo_escolar_id') == $ciclo->ciclo_escolar_id"
            focusable
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Editar Ciclo: {{ $ciclo->nombre }}</h2>
                {{-- Llamamos al componente del formulario de edición --}}
                <x-ciclo-escolar.edit-form :ciclo="$ciclo" />
            </div>
        </x-modal>
    @endforeach
    {{-- FIN MODALES EDITAR --}}

    {{-- 3. Modal para Crear Ciclo Escolar --}}
    {{-- :show="false" asegura que no se abra por errores de validación de OTROS modales --}}
    <x-modal name="agregar-ciclo" :show="$errors->hasAny() && old('form_type') === 'ciclo_escolar'" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Crear Nuevo Ciclo Escolar</h2>
            {{-- Llamamos al componente del formulario que crearemos a continuación --}}
            <x-ciclo-escolar.create-form />
        </div>
    </x-modal>

</x-app-layout>