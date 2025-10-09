<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Alumnos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between mb-4 gap-4">
                
                <div class="w-full sm:w-2/3">
                    <x-search-bar 
                        action="{{ route('alumnos.index') }}" 
                        :value="$search ?? ''"
                        placeholder="Buscar por nombre, apellidos o CURP..."
                    />
                </div>
                <a href="{{ route('alumnos.create') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                    Agregar Alumno
                </a>
            </div>

            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo (por Apellido)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CURP</th>
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
                                
                                {{-- 🔥 CORRECCIÓN AQUÍ: Comparar con la cadena 'ACTIVO' --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if ($alumno->estado_alumno === 'ACTIVO')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                    @endif
                                </td>
                                {{-- 🔥 FIN DE LA CORRECCIÓN --}}

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('alumnos.edit', $alumno) }}" 
                                           class="p-2 flex items-center justify-center rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 hover:scale-150 transition-transform" 
                                           title="Editar Alumno">
                                            <svg class="size-5"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
                                        </a>
                                        <form method="POST" action="{{ route('alumnos.destroy', $alumno) }}" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas INACTIVAR a este alumno?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 flex items-center justify-center rounded-full bg-red-100 text-red-800 hover:bg-red-200 hover:scale-150 transition-transform" 
                                                    title="Inactivar Alumno">
                                                <svg class="size-5"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron alumnos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="bg-white px-4 py-3 border-t">
                    {{ $alumnos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>