<x-app-layout>
    <x-slot name="header">
        @php
            $backUrl = route('admin.grados.index');
            if ($grupo->tipo_grupo === 'EXTRA') {
                $backUrl = route('admin.grados.index', ['view_mode' => 'extracurricular']);
            } elseif ($grupo->tipo_grupo === 'REGULAR' && $grupo->grado->nivel_id) {
                $backUrl = route('admin.grados.index', ['nivel' => $grupo->grado->nivel_id]);
            }
        @endphp
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                {{-- Botón de Volver --}}
                <a href="{{ $backUrl }}" class="p-2 bg-gray-200 rounded-full hover:bg-gray-300 transition" title="Volver a Grados y Grupos">
                    <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.5L8.25 12l7.5-7.5" /></svg>
                </a>
                
                {{-- Título --}}
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Maestros Titulares en: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
                    </h2>
                </div>
            </div>
            
            {{-- Botón de Acción Principal --}}
            {{-- Asumimos que la ruta para el formulario es 'grupos.maestros.create' o similar --}}
            <a href="{{ route('admin.grupos.maestros.create', $grupo) }}"
               class="px-5 py-2 bg-orange-500 text-white font-semibold rounded-lg shadow-md hover:bg-orange-600 transition">
                Asignar Maestros
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Maestro</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- 
                              Asumimos que el controlador pasa $maestros 
                              (ej: $maestros = $grupo->maestrosTitulares; )
                            --}}
                            @forelse ($maestros as $maestro)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $maestro->name }} {{ $maestro->apellido_paterno }} {{ $maestro->apellido_materno }}</td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Ajustamos el colspan a 1 --}}
                                    <td colspan="1" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <p class="font-semibold">
                                            Aún no se ha asignado un maestro titular a este grupo.
                                        </p>
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