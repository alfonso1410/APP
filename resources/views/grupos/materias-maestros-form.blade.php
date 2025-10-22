<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignar Maestros a Materias: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            
            {{-- Mensaje de advertencia si no hay maestros en el pool --}}
            @if($maestrosDelPool->isEmpty())
                <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-100" role="alert">
                    <span class="font-medium">¡Atención!</span> No hay maestros titulares asignados a este grupo. 
                    <a href="{{ route('admin.grupos.maestros.create', $grupo) }}" class="font-bold underline">Asigna maestros titulares primero</a>.
                </div>
            @endif

            <form action="{{ route('admin.grupos.materias-maestros.store', $grupo) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-gray-600 mb-6">
                        Selecciona un maestro de la lista para cada materia.
                    </p>

                    {{-- Lista de Materias con Selects --}}
                    <div class="space-y-6">
                        
                        @forelse ($materiasDelGrupo as $materia)
                            <div>
                                <x-input-label :value="$materia->nombre" class="font-bold text-lg text-gray-800" />
                                <select name="materias[{{ $materia->materia_id }}]" 
                                        class="border-gray-300 rounded-md shadow-sm block mt-2 w-full"
                                        {{-- Deshabilitamos si no hay maestros en el pool --}}
                                        @if($maestrosDelPool->isEmpty()) disabled @endif>
                                    
                                    <option value="">-- Sin Asignar --</option>
                                    
                                    @foreach ($maestrosDelPool as $maestro)
                                        <option value="{{ $maestro->id }}"
                                            {{-- Pre-seleccionamos el maestro guardado --}}
                                            @selected($asignacionesActuales->get($materia->materia_id) == $maestro->id)
                                        >
                                            {{ $maestro->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @empty
                            <p class="text-gray-500">No hay materias asignadas a este grupo.</p>
                        @endforelse
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="mt-8 flex justify-end gap-4">
                        <a href="{{ route('admin.grupos.materias.index', $grupo) }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700
                                       @if($maestrosDelPool->isEmpty()) opacity-50 cursor-not-allowed @endif"
                                @if($maestrosDelPool->isEmpty()) disabled @endif>
                            Guardar Asignaciones
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>