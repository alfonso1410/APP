{{-- 
    Asumo que usas el layout 'app.blade.php' (x-app-layout).
    Si usas un layout de admin diferente (ej. 'admin.blade.php'),
    ajusta el @extends o <x-admin-layout> como corresponda.
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Ponderaciones por Campo Formativo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Componente para mostrar mensajes 'success' o 'error' --}}
            <x-flash-messages />

            {{-- 1. TARJETA DE FILTROS --}}
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Seleccionar Parámetros</h3>
                    
                    {{-- Formulario de Filtros (GET) --}}
                    <form id="formFiltros" method="GET" action="{{ route('admin.ponderaciones.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Selector de Ciclo Escolar --}}
                            <div>
                                <label for="ciclo_escolar_id" class="block text-sm font-medium text-gray-700">Ciclo Escolar:</label>
                                <select name="ciclo_escolar_id" id="ciclo_escolar_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" onchange="this.form.submit()">
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->ciclo_escolar_id }}" 
                                            {{ $ciclo->ciclo_escolar_id == $cicloSeleccionadoId ? 'selected' : '' }}>
                                            {{ $ciclo->nombre }} ({{ $ciclo->estado }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            {{-- Selector de Grado --}}
                            <div>
                                <label for="grado_id" class="block text-sm font-medium text-gray-700">Grado:</label>
                                <select name="grado_id" id="grado_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" onchange="this.form.submit()">
                                    <option value="">-- Seleccione un grado --</option>
                                    
                                    {{-- Agrupamos los grados por Nivel --}}
                                    @foreach($grados->groupBy('nivel.nombre') as $nivelNombre => $gradosNivel)
                                        <optgroup label="{{ $nivelNombre }}">
                                            @foreach($gradosNivel as $grado)
                                                <option value="{{ $grado->grado_id }}"
                                                    {{ $grado->grado_id == $gradoSeleccionadoId ? 'selected' : '' }}>
                                                    {{ $grado->nombre }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 
                2. TARJETA DE PONDERACIONES
                Esta tarjeta solo se muestra si el usuario ha seleccionado un grado.
            --}}
            @if ($gradoSeleccionadoId && $cicloSeleccionadoId && $gradoSeleccionado)
            <div class="bg-white shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Ponderaciones para: {{ $gradoSeleccionado->grado_completo ?? $gradoSeleccionado->nombre }}
                    </h3>

                    @if($campos->isEmpty())
                        {{-- Mensaje si el Nivel de ese Grado no tiene campos formativos asignados --}}
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg">
                            <p>No se encontraron campos formativos para el nivel '{{ $gradoSeleccionado->nivel->nombre }}'. 
                               Vaya a la sección "Materias y Criterios" para configurarlos.
                            </p>
                        </div>
                    @else
                        {{-- Formulario para Guardar (POST) --}}
                        <form action="{{ route('admin.ponderaciones.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="ciclo_escolar_id" value="{{ $cicloSeleccionadoId }}">
                            <input type="hidden" name="grado_id" value="{{ $gradoSeleccionadoId }}">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($campos as $campo)
                                <div class="form-group">
                                    <label for="pondera_{{ $campo->id }}" class="block text-sm font-medium text-gray-700">
                                        {{ $campo->nombre }} (%):
                                    </label>
                                    <input type="number" 
                                           name="ponderaciones[{{ $campo->id }}]" 
                                           id="pondera_{{ $campo->id }}" 
                                           class="pondera-input mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
                                           value="{{ old('ponderaciones.'.$campo->id, $campo->ponderacion) }}"
                                           step="0.01" min="0" max="100" required>
                                </div>
                                @endforeach
                            </div>
                            
                            <hr class="my-6">
                            
                            <div class="flex justify-between items-center">
                                <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition">
                                    Guardar Ponderaciones
                                </button>
                                {{-- Aquí es donde nuestro JS mostrará la suma total --}}
                                <h4 class="m-0 text-lg font-semibold">
                                    Total: <span id="totalPonderacion" class="font-bold text-gray-800">0.00%</span>
                                </h4>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Script de JavaScript para sumar el total en tiempo real --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.pondera-input');
        const totalDisplay = document.getElementById('totalPonderacion');

        function calcularTotal() {
            let total = 0;
            inputs.forEach(input => {
                let valor = parseFloat(input.value);
                if (!isNaN(valor)) {
                    total += valor;
                }
            });
            
            totalDisplay.textContent = total.toFixed(2) + '%';
            
            // Damos retroalimentación visual si la suma es 100
            if (total.toFixed(2) == 100.00) {
                totalDisplay.classList.remove('text-red-600', 'text-yellow-600');
                totalDisplay.classList.add('text-green-600');
            } else if (total > 100) {
                totalDisplay.classList.remove('text-green-600', 'text-yellow-600');
                totalDisplay.classList.add('text-red-600');
            } else {
                totalDisplay.classList.remove('text-green-600', 'text-red-600');
                totalDisplay.classList.add('text-yellow-600');
            }
        }

        inputs.forEach(input => {
            input.addEventListener('input', calcularTotal);
        });

        // Calcular el total al cargar la página
        if(inputs.length > 0) {
            calcularTotal();
        }
    });
    </script>
</x-app-layout>