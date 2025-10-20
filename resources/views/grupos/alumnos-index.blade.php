<x-app-layout>
  <x-slot name="header">
    {{-- Lógica para construir la URL de regreso --}}
    @php
        $backUrl = route('grados.index'); // URL por defecto
        if ($grupo->tipo_grupo === 'EXTRA') {
            $backUrl = route('grados.index', ['view_mode' => 'extracurricular']);
        } elseif ($grupo->tipo_grupo === 'REGULAR' && $grupo->grado->nivel_id) {
            $backUrl = route('grados.index', ['nivel' => $grupo->grado->nivel_id]);
        }
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        
        <div class="flex items-center gap-4">
            <a href="{{ $backUrl }}" class="p-2 bg-gray-200 rounded-full hover:bg-gray-300 transition" title="Volver a Grados y Grupos">
                <svg class="w-5 h-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Alumnos en: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Ciclo Escolar: {{ $grupo->ciclo_escolar }}</p>
            </div>
        </div>
        
        <a href="{{ route('grupos.alumnos.create', $grupo) }}" 
           class="px-5 py-2 bg-princeton text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition">
            Asignar / Desvincular Alumnos
        </a>
    </div>
</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 1. INICIALIZAMOS ALPINE.JS --}}
            {{-- x-data: Define el estado del componente.
                 - 'search': Guardará lo que el usuario escriba.
                 - 'alumnos': Contiene la lista COMPLETA de alumnos en formato JSON.
            --}}
            <div x-data="{ search: '', alumnos: {{ $alumnos->toJson() }} }">
                
                <div class="mb-4">
                    <input type="text" x-model="search" placeholder="Filtrar alumno por nombre o apellido..." 
                           class="w-full sm:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre Completo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CURP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Materia Extracurricular</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Promedio General</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            
                            {{-- 3. CAMBIAMOS EL CUERPO DE LA TABLA --}}
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- <template x-for="...">: Es el bucle de Alpine, similar al @foreach de Blade. --}}
                                <template x-for="alumno in alumnos.filter(a => 
                                    (a.nombres + ' ' + a.apellido_paterno + ' ' + a.apellido_materno).toLowerCase().includes(search.toLowerCase())
                                )" :key="alumno.alumno_id">
                                    <tr class="hover:bg-gray-50">
                                        {{-- x-text: Muestra el valor de una variable de Alpine. --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" 
                                            x-text="`${alumno.apellido_paterno} ${alumno.apellido_materno}, ${alumno.nombres}`">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="alumno.curp"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span x-text="alumno.materia_extracurricular"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">
                                            {{-- Calculamos el promedio aquí. Usamos parseFloat para asegurar que es un número. --}}
                                            <span x-text="parseFloat(alumno.promedio_general) > 0 ? parseFloat(alumno.promedio_general).toFixed(2) : 'N/A'"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="{
                            'bg-green-100 text-green-800': alumno.estado_alumno === 'ACTIVO',
                            'bg-red-100 text-red-800': alumno.estado_alumno !== 'ACTIVO'
                          }"
                          x-text="alumno.estado_alumno === 'ACTIVO' ? 'Activo' : 'Inactivo'">
                    </span>
                </td>
                                        
                                    </tr>
                                </template>

                                <tr x-show="alumnos.filter(a => (a.nombres + ' ' + a.apellido_paterno + ' ' + a.apellido_materno).toLowerCase().includes(search.toLowerCase())).length === 0">
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <p class="font-semibold" x-text="search ? 'No se encontraron alumnos que coincidan con la búsqueda.' : 'No hay alumnos en este grupo.'"></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> </div>
    </div>
</x-app-layout>