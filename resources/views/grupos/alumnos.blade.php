<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignar Alumnos a: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             @if (session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
           <form action="{{ route('admin.grupos.alumnos.store', $grupo) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6 border-b">
                        <p class="text-gray-600">
                            Selecciona los alumnos que deseas inscribir en este grupo.
                        </p>
                    @if ($grupo->tipo_grupo === 'REGULAR')
    <p class="mt-2 text-sm text-yellow-800 bg-yellow-100 p-3 rounded-md">
        <strong>Aviso:</strong> Solo se muestran alumnos que no tienen un grupo regular activo.
    </p>
@else
    <p class="mt-2 text-sm text-blue-800 bg-blue-100 p-3 rounded-md">
        <strong>Aviso:</strong> Solo se muestran alumnos que ya pertenecen a un grupo regular y no tienen otra actividad extracurricular.
    </p>
@endif
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 w-12"></th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre Completo</th>
            {{-- <th class="px-6 py-3 text-center ...">Promedio</th> --}} {{-- <-- COLUMNA ELIMINADA --}}
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Extracurricular</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($alumnosDisponibles as $alumno)
            @php
                $extra = $alumno->grupos->firstWhere('tipo_grupo', 'EXTRA');
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-center">
                    <input type="checkbox" name="alumnos[]" value="{{ $alumno->alumno_id }}" 
                           class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                           @checked(in_array($alumno->alumno_id, $idsAlumnosAsignados))>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}, {{ $alumno->nombres }}
                </td>
                
               <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
    {{ $alumno->materia_extracurricular ?: 'Ninguna' }}
</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if ($alumno->estado_alumno === 'ACTIVO')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                    @endif
                </td>
            </tr>
        @empty
            {{-- Ajustamos el colspan a 4 columnas --}}
            <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No hay alumnos disponibles para asignar.</td></tr>
        @endforelse
    </tbody>
</table>
                    </div>

                    <div class="p-6 border-t flex justify-end gap-4">
                        <a href="{{ route('admin.grupos.alumnos.index', $grupo) }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-princeton text-white text-sm font-semibold rounded-md">Guardar Asignaciones</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>