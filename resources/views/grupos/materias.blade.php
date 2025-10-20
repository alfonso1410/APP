<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignar Materias a: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('grupos.materias.store', $grupo) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-gray-600 mb-6">
                        Selecciona las materias que se impartirán en este grupo.
                    </p>

                    <div class="space-y-3">
                        @forelse ($materiasDisponibles as $materia)
                            <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="materias[]" 
                                    value="{{ $materia->materia_id }}" 
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    @checked(in_array($materia->materia_id, $idsMateriasAsignadas))
                                    {{-- Para grupos regulares, las materias son obligatorias --}}
                                    @if($grupo->tipo_grupo === 'REGULAR') disabled @endif
                                >
                                <span class="ml-4 text-gray-800 font-medium">{{ $materia->nombre }}</span>
                            </label>
                        @empty
                            <div class="text-center p-4 border rounded-lg text-gray-500">
                                No hay materias disponibles para asignar.
                            </div>
                        @endforelse
                    </div>

                    {{-- Nota informativa para grupos regulares --}}
                    @if($grupo->tipo_grupo === 'REGULAR')
                        <p class="mt-4 text-sm text-yellow-800 bg-yellow-100 p-3 rounded-md">
                            <strong>Nota:</strong> Las materias para grupos regulares se definen en la Estructura Curricular del grado y no pueden modificarse aquí.
                        </p>
                    @endif

                    <div class="mt-8 flex justify-end gap-4">
                        <a href="{{ route('grados.index') }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700"
                            @if($grupo->tipo_grupo === 'REGULAR') disabled @endif
                        >
                            Guardar Materias
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>