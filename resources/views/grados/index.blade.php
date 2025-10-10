<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Grados y Grupos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
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
                    <x-level-filter :route="'grados.index'" :selectedNivel="$nivel_id" />
                </div>
            </div>

            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupos Activos</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($grados as $grado)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $grado->nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center gap-2">
                                            @foreach ($grado->grupos as $grupo)
                                                <a href="#" class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-xs font-semibold hover:bg-gray-300">
                                                    {{ $grupo->nombre }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        {{-- El enlace de este botón ha sido activado --}}
                                        <a href="{{ route('grupos.create', ['grado' => $grado->grado_id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                            Nuevo Grupo
                                        </a>
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
</x-app-layout>