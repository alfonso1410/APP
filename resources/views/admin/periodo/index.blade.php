<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4"> {{-- Div para agrupar flecha y título --}}
                {{-- INICIO: Botón de Regreso --}}
                <a href="{{ route('admin.ciclo-escolar.index') }}" class="p-2 bg-gray-200 rounded-full hover:bg-gray-300 transition" title="Volver a Ciclos Escolares">
                    <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
                {{-- FIN: Botón de Regreso --}}

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Periodos</h2>
            </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-flash-messages />

            {{-- 1. Botón Flotante para Crear --}}
            {{-- Solo se muestra si hay un ciclo activo --}}
            @if($cicloActivo)
                <div class="fixed bottom-8 right-8 z-50">
                    <button
                        x-data=""
                        @click.prevent="$dispatch('open-modal', 'agregar-periodo')"
                        class="bg-princeton hover:bg-blue-700 text-white font-bold p-4 rounded-full shadow-lg transition-transform hover:scale-105"
                        title="Agregar Periodo al Ciclo Activo ({{ $cicloActivo->nombre }})"
                    >
                        <svg class="size-6"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-add"></use></svg>
                    </button>
                </div>
            @else
                 <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 rounded-md border border-yellow-300">
                    No hay un Ciclo Escolar ACTIVO. Debes activar uno para poder crear nuevos periodos.
                 </div>
            @endif

            {{-- 2. Tabla de Periodos --}}
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciclo Escolar</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Periodo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($periodos as $periodo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">{{ $periodo->cicloEscolar->nombre ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $periodo->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                         <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                'bg-green-100 text-green-800': '{{ $periodo->estado }}' === 'ABIERTO',
                                                'bg-red-100 text-red-800': '{{ $periodo->estado }}' !== 'ABIERTO'
                                              }">
                                            {{ $periodo->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                      <div class="flex items-center justify-center gap-x-2">
                                            {{-- Botón Editar (abre modal) --}}
                                            <button
                                                x-data=""
                                                x-on:click.prevent="$dispatch('open-modal', 'editar-periodo-{{ $periodo->periodo_id }}')"
                                                class="text-indigo-600 hover:text-indigo-900 mx-1 p-1 rounded-full hover:bg-indigo-100"
                                                title="Editar Periodo"
                                            >
                                                <svg class="size-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
                                            </button>

                                            {{-- Botón Eliminar (usa formulario con confirmación) --}}
                                            <form action="{{ route('admin.periodos.destroy', $periodo) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar el periodo \'{{ $periodo->nombre }}\'? Esta acción no se puede deshacer y fallará si tiene datos asociados.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900 mx-1 p-1 rounded-full hover:bg-red-100"
                                                        title="Eliminar Periodo">
                                                    <svg class="size-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        {{ $cicloFiltradoId ? 'No hay periodos registrados para este ciclo escolar.' : 'No hay periodos registrados.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- INICIO MODALES EDITAR --}}
    @foreach ($periodos as $periodo)
        <x-modal
            :name="'editar-periodo-' . $periodo->periodo_id"
            :show="$errors->any() && old('periodo_id') == $periodo->periodo_id"
            focusable
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Editar Periodo: {{ $periodo->nombre }}
                    <span class="block text-sm text-gray-500">Ciclo: {{ $periodo->cicloEscolar->nombre ?? 'N/A' }}</span>
                </h2>
                {{-- Llamamos al componente del formulario de edición --}}
                <x-periodo.edit-form :periodo="$periodo" :isFiltered="$cicloFiltradoId" />
            </div>
        </x-modal>
        @endforeach
    {{-- 3. Modal para Crear Periodo (solo si hay ciclo activo) --}}
    @if($cicloActivo)
        <x-modal name="agregar-periodo" :show="$errors->hasAny() && old('form_type') === 'periodo'" focusable>
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Crear Nuevo Periodo para Ciclo: <span class="font-bold">{{ $cicloActivo->nombre }}</span>
                </h2>
                {{-- Llamamos al componente del formulario --}}
                <x-periodo.create-form :cicloActivo="$cicloActivo" />
            </div>
        </x-modal>
    @endif

</x-app-layout>