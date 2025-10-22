<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignar Maestros Titulares a: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            <form action="{{ route('admin.grupos.maestros.store', $grupo) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-gray-600 mb-6">
                        Selecciona los maestros que estarán a cargo de este grupo.
                    </p>

                    <div class="space-y-3">
                        @forelse ($maestrosDisponibles as $maestro)
                            <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="maestros[]" 
                                    value="{{ $maestro->id }}" 
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    @checked(in_array($maestro->id, $idsMaestrosAsignados))
                                >
                                <span class="ml-4 text-gray-800 font-medium">{{ $maestro->name }}</span>
                                <span class="ml-auto text-sm text-gray-500">{{ $maestro->email }}</span>
                            </label>
                        @empty
                            <div class="text-center p-4 border rounded-lg text-gray-500">
                                No hay usuarios con el rol "maestro" en el sistema.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        {{-- Redirige a la vista de detalle de la card --}}
                        <a href="{{ route('admin.grupos.maestros.index', $grupo) }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Volver al Grupo</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">
                            Guardar Maestros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>