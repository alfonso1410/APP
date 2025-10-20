<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mantenimiento de Grupos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex justify-end mb-4">
                <a href="{{ route('grados.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    &larr; Regresar a la Gestión Principal
                </a>
            </div>

            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 bg-green-100 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg">{{ session('error') }}</div>
            @endif
            
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">Nombre del Grupo</th>
                                <th class="px-6 py-3 text-left">Grado Asociado</th>
                                <th class="px-6 py-3 text-left">Tipo</th>
                                <th class="px-6 py-3 text-left">Estado</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($gruposArchivados as $grupo)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{-- Convertimos el nombre en un botón para abrir la modal --}}
                                        <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'view-group-{{ $grupo->grupo_id }}')" class="text-indigo-600 hover:underline">
                                            {{ $grupo->nombre_grupo }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">{{ $grupo->grado->nombre ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $grupo->tipo_grupo === 'REGULAR' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                    {{ $grupo->tipo_grupo }}
                </span>
            </td>
                                    <td class="px-6 py-4">{{ $grupo->estado }}</td>
                                    <td class="px-6 py-4 text-center">
                                         <x-grupos.delete-form
                                            :action="route('grupos.destroy', $grupo)"
                                            confirm-message="¿Seguro que quieres eliminar el grupo '{{ $grupo->nombre_grupo }}'? Esta acción es permanente."
                                            class="bg-red-100 text-red-800"
                                        >
                                            <svg class="w-5 h-5"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
                                        </x-grupos.delete-form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 text-center">No hay grupos para mostrar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $gruposArchivados->links() }}</div>
            </div>

            {{-- AÑADIMOS EL BUCLE QUE GENERA LAS MODALES DE DETALLE --}}
            @foreach ($gruposArchivados as $grupo)
                <x-modal :name="'view-group-' . $grupo->grupo_id" maxWidth="2xl">
                    <x-grupos.detail-card :grupo="$grupo" />
                </x-modal>
            @endforeach
        </div>
    </div>
</x-app-layout>