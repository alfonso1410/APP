<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Grados y Grupos</h2>
    </x-slot>

    <div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                {{-- 1. SECCIÓN DE FILTROS Y BÚSQUEDA RESTAURADA --}}
                <div class="flex items-center justify-between mb-4">
                    {{-- Barra de búsqueda --}}
                    <div class="w-1/3">
                        <form action="{{ route('grados.index') }}" method="GET">
                            <input type="hidden" name="nivel" value="{{ $nivel_id }}">
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar grado por nombre..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        </form>
                    </div>

                    {{-- Componente de Filtro por Nivel --}}
                    <div>
                        <x-level-filter :route="'grados.index'" :selectedNivel="$nivel_id" :show-unassigned="false" />
                    </div>
                </div>

                <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            {{-- ... El contenido de tu tabla (thead, tbody) va aquí sin cambios ... --}}
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupos Activos</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
    @forelse ($grados as $grado)
        <tr>
            {{-- Celda para la columna "Grado" --}}
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ $grado->nombre }}
            </td>

            {{-- Celda para la columna "Grupos Activos" --}}
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center gap-2">
                    @foreach ($grado->grupos as $grupo)
                        <a href="#" class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-xs font-semibold hover:bg-gray-300">
                            {{ $grupo->nombre_grupo }}
                        </a>
                    @endforeach
                </div>
            </td>

            {{-- Celda para la columna "Acciones" con ambos botones --}}
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                {{-- Usamos un div con flex para alinear y espaciar los botones --}}
                <div class="flex items-center justify-center gap-x-6">
                    
                    

                    {{-- 1. Botón para Nuevo Grupo (el que faltaba) --}}
                    <a href="{{ route('grupos.create', ['grado' => $grado->grado_id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Nuevo Grupo
                    </a>
                    {{-- 2. Botón para Editar Grado (abre la modal) --}}
                    <button 
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'editar-grado-{{ $grado->grado_id }}')"
                         class="bg-blue-100 text-blue-800 p-1 flex size-4 sm:size-6 items-center justify-center rounded-full hover:scale-150 transition-transform"
                        title="Editar Grado"
                    >
                        <svg class="size-6">
            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use>
        </svg>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                No se encontraron grados para el nivel seleccionado.
            </td>
        </tr>
    @endforelse
</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. BOTÓN "AGREGAR GRADO" MOVIDO ABAJO A LA DERECHA --}}
        <div class="fixed bottom-8 right-8 z-50">
            <button 
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'agregar-grado')"
                class="bg-princeton hover:bg-blue-700 text-white font-bold py-3 px-5 rounded-full shadow-lg transition-transform hover:scale-105"
                title="Agregar Nuevo Grado"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </button>
        </div>

        {{-- Modales (fuera del flujo principal, no cambian) --}}
        @foreach ($grados as $grado)
            <x-modal 
                :name="'editar-grado-' . $grado->grado_id" 
                :show="$errors->any() && old('grado_id') == $grado->grado_id"
                focusable
            >
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Editar Grado: {{ $grado->nombre }}</h2>
                    <x-grados.edit-form :grado="$grado" :niveles="$niveles" />
                </div>
            </x-modal>
        @endforeach

        <x-modal name="agregar-grado" :show="false" focusable>
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Crear Nuevo Grado</h2>
                <x-grados.create-form :niveles="$niveles" />
            </div>
        </x-modal>
    </div>
</x-app-layout>