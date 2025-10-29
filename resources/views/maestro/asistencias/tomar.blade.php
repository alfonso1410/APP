@php use Carbon\Carbon; @endphp
<x-app-layout>
    {{-- Alpine.js: 'habilitado' controla si los radios están activos --}}
    <div class="py-12" x-data="{ habilitado: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Mensaje de éxito al guardar --}}
            @if (session('status'))
                <div classs="mb-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            {{-- Mensaje de error --}}
            @if ($errors->any())
                <div class="mb-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">Error</p>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="p-6 sm:px-8 bg-white border-b border-gray-200">
                    
                    {{-- 1. HEADER: Título y Selector de Semana --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-800">
                                Asistencia para: {{ $grupo->grado->nombre ?? '' }} {{ $grupo->nombre }}
                            </h2>
                          <p class="text-gray-600">
                Ciclo Escolar {{ $cicloActivo ? $cicloActivo->nombre : 'N/A' }}
                <span class="font-bold text-blue-600">({{ $idiomaDelMaestro }})</span>
            </p>
                        </div>

                        <form method="GET" action="{{ route('maestro.asistencias.tomar', $grupo) }}" class="mt-4 md:mt-0 flex items-end space-x-2">
                          
                        <div>
                <x-input-label for="periodo_id" :value="__('Seleccionar Periodo')" />
                <select id="periodo_id" name="periodo_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                    @foreach ($periodosDisponibles as $periodo)
                        <option value="{{ $periodo->periodo_id }}" @selected($periodo->periodo_id == $periodoSeleccionadoId)>
                            {{ $periodo->nombre }} ({{ Carbon::parse($periodo->fecha_inicio)->format('d/m') }} - {{ Carbon::parse($periodo->fecha_fin)->format('d/m') }})
                        </option>
                    @endforeach
                </select>
            </div>
                            {{-- Selector de Semana --}}
                            <div>
                                <x-input-label for="semana" :value="__('Seleccionar Semana')" />
                                <select id="semana" name="semana" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="this.form.submit()">
                                   @forelse ($semanasDisponibles as $valorLunes => $textoSemana)
                        <option value="{{ $valorLunes }}" @selected($lunesSeleccionado->isSameDay(Carbon::parse($valorLunes)))>
                            {{ $textoSemana }}
                        </option>
                    @empty
                        <option value="">No hay semanas en este periodo</option>
                    @endforelse
                                </select>
                            </div>
                            
                            </form>
                        </div>

                    {{-- Formulario POST para guardar la asistencia --}}
                    <form method="POST" action="{{ route('maestro.asistencias.guardar', $grupo) }}">
                        @csrf
                        <input type="hidden" name="periodo_id" value="{{ $periodoSeleccionadoId }}">
        <input type="hidden" name="semana" value="{{ $lunesSeleccionado->format('Y-m-d') }}">

                        {{-- 2. TABLA DE ASISTENCIA (Esta parte no necesita cambios) --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 mt-6 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">No.</th>
                                        <th scope="col" class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
                                        
                                        @foreach ($diasDeLaSemana as $fecha)
                                            <th scope="col" colspan="3" class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider border-l">
                                                {{ Carbon::parse($fecha)->translatedFormat('l') }}
                                                <span class="block font-normal">{{ Carbon::parse($fecha)->format('d/m') }}</span>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="px-3 py-2"></th>
                                        <th class="px-6 py-2"></th>
                                        @foreach ($diasDeLaSemana as $fecha)
                                            <th class="px-2 py-2 text-center font-medium text-gray-500 border-l" title="Presente">P</th>
                                            <th class="px-2 py-2 text-center font-medium text-gray-500" title="Retardo">R</th>
                                            <th class="px-2 py-2 text-center font-medium text-gray-500" title="Falta">F</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($alumnos as $alumno)
                                        <tr>
                                            <td class="px-3 py-3 whitespace-nowrap text-gray-500">{{ $loop->iteration }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap font-medium text-gray-900">
                                                {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}
                                            </td>
                                            
                                            @foreach ($diasDeLaSemana as $fecha)
                                                @php
                                                    // $asistencias ya viene filtrada por idioma desde el controlador
                                                    $registro = $asistencias[$alumno->alumno_id][$fecha] ?? null;
                                                    $tipo = $registro ? $registro->tipo_asistencia : null;
                                                @endphp
                                                
                                                @php $name = "asistencia[{$alumno->alumno_id}][{$fecha}]"; @endphp
                                                
                                                {{-- PRESENTE --}}
                                                <td class="px-2 py-3 text-center border-l">
                                                    <input type="radio" name="{{ $name }}" value="PRESENTE" 
                                                           :disabled="!habilitado" 
                                                           @checked($tipo === 'PRESENTE')
                                                           class="rounded-full text-blue-600 focus:ring-blue-500 disabled:opacity-50">
                                                </td>
                                                {{-- RETARDO --}}
                                                <td class="px-2 py-3 text-center">
                                                    <input type="radio" name="{{ $name }}" value="RETARDO" 
                                                           :disabled="!habilitado" 
                                                           @checked($tipo === 'RETARDO')
                                                           class="rounded-full text-yellow-600 focus:ring-yellow-500 disabled:opacity-50">
                                                </td>
                                                {{-- FALTA (FALTA) --}}
                                                <td class="px-2 py-3 text-center">
                                                    <input type="radio" name="{{ $name }}" value="FALTA" 
                                                           :disabled="!habilitado" 
                                                           @checked($tipo === 'FALTA')
                                                           class="rounded-full text-red-600 focus:ring-red-500 disabled:opacity-50">
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- 3. BOTONES DE ACCIÓN (Habilitar / Guardar) --}}
                        <div class="flex justify-end mt-6">
                            
                            <button type="button" 
                                    x-show="!habilitado" 
                                    @click.prevent="habilitado = true"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Habilitar Edición
                            </button>

                            <button type="submit" 
                                    x-show="habilitado"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>