<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Materias
            </h2>
            <div class="flex space-x-4">
                 <x-secondary-link-button href="{{ route('campos-formativos.index') }}">
                    Ver Campos Formativos
                </x-secondary-link-button>
                <x-primary-button-link href="{{ route('materias.create') }}">
                    + Nueva Materia
                </x-primary-button-link>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> {{-- Corregido: sm:px-6 --}}
            <x-flash-messages />
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materia</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campo Formativo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quién La Imparte</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($materias as $materia)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $materia->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $materia->camposFormativos->first()?->nombre ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $materia->asignacionesGrupo->first()?->maestro?->name ?? 'Sin Asignar' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $materia->asignacionesGrupo->first()?->grupo?->grado?->nombre ?? 'N/A' }}
                                        </td>
                                        
                                        {{-- --- INICIO DE LA CORRECCIÓN: Botones de Icono --- --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2"> {{-- Contenedor Flex para alinear iconos --}}
                                                
                                                {{-- Botón Editar (como enlace estilizado) --}}
                                                <a href="{{ route('materias.edit', $materia) }}"
                                                   class="bg-blue-100 text-blue-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-125 transition-transform" {{-- Ajustado tamaño y hover --}}
                                                   title="Editar Materia">
                                                   <svg class="size-4"> {{-- Ajustado tamaño SVG --}}
                                                       <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use>
                                                   </svg>
                                                </a>

                                                {{-- Botón Eliminar (dentro de su form) --}}
                                                <form action="{{ route('materias.destroy', $materia) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar la materia \'{{ $materia->nombre }}\'?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="bg-red-100 text-red-800 p-1 flex size-6 items-center justify-center rounded-full hover:scale-125 transition-transform" {{-- Ajustado tamaño y hover --}}
                                                            title="Eliminar Materia">
                                                        <svg class="size-4"> {{-- Ajustado tamaño SVG --}}
                                                            {{-- Asume que tienes un icono 'icon-delete' o similar en tu sprite --}}
                                                            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use> 
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        {{-- --- FIN DE LA CORRECCIÓN --- --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay materias registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                         
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>