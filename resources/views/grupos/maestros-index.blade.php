<x-app-layout>
    <x-slot name="header">
        {{-- Lógica para construir la URL de regreso --}}
        @php
            $backUrl = route('admin.grados.index'); // URL por defecto
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
                    <svg class="w-5 h-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
                {{-- Título --}}
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Maestros Titulares en: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Ciclo Escolar: {{ $grupo->cicloEscolar->nombre ?? 'N/A' }}</p>
                </div>
            </div>
            
            {{-- Botón de Asignar --}}
            <a href="{{ route('admin.grupos.maestros.create', $grupo) }}" 
               class="px-5 py-2 bg-orange-500 text-white font-semibold rounded-lg shadow-md hover:bg-orange-600 transition">
                Asignar Maestros
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            
            {{-- Contenedor de la tabla --}}
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                {{-- Los encabezados cambian ligeramente --}}
                                @if ($grupo->tipo_grupo == 'REGULAR')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Idioma</th>
                                @else
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Puesto</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maestro Titular</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maestro Auxiliar</th>
                            </tr>
                        </thead>
                        
                        <tbody class="bg-white divide-y divide-gray-200">

                            {{-- =================================== --}}
                            {{--  CASO 1: GRUPO REGULAR (Bilingüe)   --}}
                            {{-- =================================== --}}
                            @if ($grupo->tipo_grupo == 'REGULAR')
                                
                                @php
                                    $asignacionEspanol = $asignaciones->get('ESPAÑOL');
                                    $asignacionIngles = $asignaciones->get('INGLES');
                                @endphp

                                {{-- Fila de ESPAÑOL --}}
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ESPAÑOL
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($asignacionEspanol?->titular)
                                            {{ $asignacionEspanol->titular->name }} {{ $asignacionEspanol->titular->apellido_paterno }} {{ $asignacionEspanol->titular->apellido_materno }}
                                        @else
                                            <span class="text-gray-400">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($asignacionEspanol?->auxiliar)
                                            {{ $asignacionEspanol->auxiliar->name }} {{ $asignacionEspanol->auxiliar->apellido_paterno }} {{ $asignacionEspanol->auxiliar->apellido_materno }}
                                        @else
                                            <span class="text-gray-400">Sin asignar</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Fila de INGLÉS --}}
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        INGLÉS
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($asignacionIngles?->titular)
                                            {{ $asignacionIngles->titular->name }} {{ $asignacionIngles->titular->apellido_paterno }} {{ $asignacionIngles->titular->apellido_materno }}
                                        @else
                                            <span class="text-gray-400">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($asignacionIngles?->auxiliar)
                                            {{ $asignacionIngles->auxiliar->name }} {{ $asignacionIngles->auxiliar->apellido_paterno }} {{ $asignacionIngles->auxiliar->apellido_materno }}
                                        @else
                                            <span class="text-gray-400">Sin asignar</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Estado vacío para REGULAR --}}
                                @if(!$asignacionEspanol && !$asignacionIngles)
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <p class="font-semibold">No se han asignado maestros a este grupo.</p>
                                        </td>
                                    </tr>
                                @endif

                            {{-- =================================== --}}
                            {{--  CASO 2: GRUPO EXTRA (General)    --}}
                            {{-- =================================== --}}
                            @else

                                @php
                                    $asignacionGeneral = $asignaciones->get('GENERAL');
                                @endphp

                                {{-- Fila de MAESTRO PRINCIPAL --}}
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        MAESTRO PRINCIPAL
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($asignacionGeneral?->titular)
                                            {{ $asignacionGeneral->titular->name }} {{ $asignacionGeneral->titular->apellido_paterno }} {{ $asignacionGeneral->titular->apellido_materno }}
                                        @else
                                            <span class="text-gray-400">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($asignacionGeneral?->auxiliar)
                                            {{ $asignacionGeneral->auxiliar->name }} {{ $asignacionGeneral->auxiliar->apellido_paterno }} {{ $asignacionGeneral->auxiliar->apellido_materno }}
                                        @else
                                            <span class="text-gray-400">Sin asignar</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Estado vacío para EXTRA --}}
                                @if(!$asignacionGeneral)
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">
                                            <p class="font-semibold">No se han asignado maestros a este grupo.</p>
                                        </td>
                                    </tr>
                                @endif

                            @endif 
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>