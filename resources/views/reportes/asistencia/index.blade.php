<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reporte de Asistencia
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-lg">
                <form action="{{ route('admin.reportes.asistencia.generar') }}" method="GET" target="_blank">
                    {{-- NO incluyas @csrf aquí porque es GET --}}
    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Grupo</label>
                        <select name="grupo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Selecciona un grupo</option>
                            @foreach($grupos as $g)
                                <option value="{{ $g->grupo_id }}">
                                    {{ $g->grado->nombre ?? 'Sin grado' }} - {{ $g->nombre_grupo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Mes</label>
                        <select name="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Selecciona un mes</option>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Año</label>
                        <select name="anio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Selecciona un año</option>
                            @for($year = 2020; $year <= 2030; $year++)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tipo de reporte</label>
                        <div class="mt-2 space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="tipo" value="idioma" class="text-indigo-600" checked>
                                <span class="ml-2">Idioma (Maestro titular)</span>
                            </label>
                            <label class="inline-flex items-center ml-6">
                                <input type="radio" name="tipo" value="materia" class="text-indigo-600">
                                <span class="ml-2">Materia específica (Complementario)</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Valor (Materia o Idioma)</label>
                        <select name="valor" id="valor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </select>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-md shadow-sm hover:bg-indigo-700">
                        Generar Reporte PDF
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Recibimos los datos del controlador (Asegúrate de haber hecho el cambio en el Controlador primero)
            const idiomas = @json($opcionesIdiomas ?? ['ESPAÑOL', 'INGLES']); 
            const materias = @json($opcionesMaterias ?? ['COMPUTACION']);

            const selectValor = document.getElementById('valor');
            const radios = document.querySelectorAll('input[name="tipo"]');

            // Función para llenar el select
            function actualizarSelect(opciones) {
                selectValor.innerHTML = ''; // Limpiar
                opciones.forEach(opcion => {
                    const opt = document.createElement('option');
                    opt.value = opcion;
                    opt.textContent = opcion;
                    selectValor.appendChild(opt);
                });
            }

            // Detectar cambios en los radio buttons
            radios.forEach(radio => {
                radio.addEventListener('change', (e) => {
                    if (e.target.value === 'idioma') {
                        actualizarSelect(idiomas);
                    } else {
                        actualizarSelect(materias);
                    }
                });
            });

            // Carga inicial (revisar cuál está marcado al abrir la página)
            const seleccionado = document.querySelector('input[name="tipo"]:checked');
            if (seleccionado && seleccionado.value === 'materia') {
                actualizarSelect(materias);
            } else {
                actualizarSelect(idiomas);
            }
        });
    </script>
</x-app-layout>