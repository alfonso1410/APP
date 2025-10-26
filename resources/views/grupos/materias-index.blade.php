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
        
        {{-- Título y botón de Volver (Esto estaba bien) --}}
        <div class="flex items-center gap-4">
            <a href="{{ $backUrl }}" class="p-2 bg-gray-200 rounded-full hover:bg-gray-300 transition" title="Volver a Grados y Grupos">
                <svg class="w-5 h-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Materias en: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
                </h2>
            </div>
        </div>
        
        {{-- 
          --- INICIO DE LA CORRECCIÓN --- 
          Agregamos un div para agrupar los botones y cambiamos la lógica del @if
        --}}
        <div class="flex items-center gap-3">
            
            @if ($grupo->tipo_grupo === 'REGULAR')
                {{-- Para grupos regulares, SOLO se asignan maestros --}}
                <a href="{{ route('admin.grupos.materias-maestros.create', $grupo) }}"
                   class="px-5 py-2 bg-orange-500 text-white font-semibold rounded-lg shadow-md hover:bg-orange-600 transition">
                   Asignar Maestros a Materias
                </a>
            @else
                {{-- Para grupos extra, se muestran AMBOS botones --}}

                {{-- Botón 1: Para agregar/quitar materias --}}
                <a href="{{ route('admin.grupos.materias.create', $grupo) }}" 
                   class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition">
                   Editar Materias del Grupo
                </a>
                
                {{-- Botón 2: Para asignar maestros a esas materias --}}
                <a href="{{ route('admin.grupos.materias-maestros.create', $grupo) }}"
                   class="px-5 py-2 bg-orange-500 text-white font-semibold rounded-lg shadow-md hover:bg-orange-600 transition">
                   Asignar Maestros a Materias
                </a>
            @endif
        </div>
        {{-- --- FIN DE LA CORRECCIÓN --- --}}

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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre de la Materia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campo Formativo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Maestro Asignado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($materias as $materia)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $materia->nombre }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $materia->camposFormativos->first()->nombre ?? 'N/A (Extracurricular)' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{-- El controlador ya precargó el maestro correcto para este grupo --}}
                                        {{ $materia->maestros->first() 
                                            ? $materia->maestros->first()->name . ' ' . $materia->maestros->first()->apellido_paterno . ' ' . $materia->maestros->first()->apellido_materno 
                                            : 'Sin Asignar' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <p class="font-semibold">
                                            @if($grupo->tipo_grupo === 'REGULAR')
                                                No hay materias definidas en la estructura curricular de este grado.
                                            @else
                                                Aún no se ha asignado una materia a este grupo.
                                            @endif
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