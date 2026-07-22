<x-app-layout>
 <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Calificaciones: {{ $grupo->grado->nombre }} - {{ $grupo->nombre_grupo }}
            </h2>
            <div class="flex items-center gap-4">
                {{-- Selector de Periodo --}}
                <form action="{{ route('admin.reportes.resumen', $grupo->grupo_id) }}" method="GET" class="flex items-center">
                    <select name="periodo_id" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-princeton focus:ring-princeton">
                        @foreach($periodos as $p)
                            <option value="{{ $p->periodo_id }}" {{ $periodo_id == $p->periodo_id ? 'selected' : '' }}>
                                {{ $p->nombre }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('admin.reportes.resumen.pdf', ['grupo' => $grupo->grupo_id, 'periodo_id' => $periodo_id]) }}" 
                   target="_blank"
                   class="bg-princeton text-white px-4 py-2 rounded-lg text-xs font-bold uppercase">
                    Descargar PDF ({{ $periodos->firstWhere('periodo_id', $periodo_id)->nombre ?? 'Periodo no encontrado' }})
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="text-sm text-gray-600 mb-4 italic">
                        * Calificaciones mostradas con 3 decimales sin redondeo.
                    </p>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Pos.</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
                                    @foreach($camposSep as $campo)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $campo }}
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center text-xs font-bold text-blue-600 uppercase tracking-wider bg-blue-50">
                                        Promedio Final
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($alumnos as $index => $alumno)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-700 text-center">
                                            {{ $index + 1 }}°
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $alumno['nombre'] }}
                                        </td>
                                        @foreach($camposSep as $campo)
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                                {{ $alumno['campos'][$campo]['valor'] }}
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-black bg-blue-50 text-blue-700 border-l border-blue-100">
                                            {{ $alumno['promedio_final'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($camposSep) + 3 }}" class="px-6 py-4 text-center text-gray-500">
                                            No hay calificaciones registradas para este grupo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Botón de retorno --}}
           <div class="mt-4">
    <a href="{{ route('admin.reportes.resumen.index') }}" class="text-sm text-gray-600 hover:text-princeton underline flex items-center">
        <svg class="size-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
        </svg>
        Volver a Selección de Grupos
    </a>
</div>
        </div>
    </div>
</x-app-layout>