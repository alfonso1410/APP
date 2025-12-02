<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Captura de Calificaciones (Maestro)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            {{-- x-data llama a la función maestroCalificacionesManager() --}}
            <div x-data="maestroCalificacionesManager()" 
                 class="bg-white p-6 shadow-sm rounded-lg">
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    
                    {{-- Selector de Grupo (pre-cargado desde el controlador) --}}
                    <div>
                        <label for="grupo" class="block text-sm font-medium text-gray-700">Mis Grupos</label>
                        <select id="grupo" x-model="selectedGrupo" @change="grupoChanged()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecciona un grupo</option>
                            
                            @forelse($gruposDelMaestro as $grupoId => $asignaciones)
                                @php $primera = $asignaciones->first(); @endphp
                                <option value="{{ $grupoId }}" 
                                        @selected(old('grupo_id') == $grupoId)>
                                    {{ $primera->nombre_grado }} - {{ $primera->nombre_grupo }}
                                </option>
                            @empty
                                <option disabled>No tienes grupos asignados</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Selector de Materia (poblado dinámicamente por Alpine) --}}
                    <div>
                        <label for="materia" class="block text-sm font-medium text-gray-700">Mis Materias</label>
                        <select id="materia" x-model="selectedMateria" 
                                :disabled="!selectedGrupo || materias.length === 0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                            
                            <option value="" disabled selected>
                                <span x-show="!selectedGrupo">Selecciona un grupo primero</span>
                                <span x-show="selectedGrupo && materias.length === 0">Sin materias asignadas</span>
                                <span x-show="selectedGrupo && materias.length > 0">Selecciona una materia</span>
                            </option>
                            <template x-for="materia in materias" :key="materia.materia_id">
                                <option :value="materia.materia_id" 
                                        x-text="materia.nombre_materia"
                                        :selected="materia.materia_id == selectedMateria">
                                </option>
                            </template>
                        </select>
                    </div>

                    {{-- Selector de Periodo --}}
                    <div>
                        <label for="periodo" class="block text-sm font-medium text-gray-700">Periodo</label>
                        <select id="periodo" x-model="selectedPeriodo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecciona un periodo</option>
                            @foreach($periodos as $periodo)
                               <option value="{{ $periodo->id }}" 
                                        @selected(old('periodo_id') == $periodo->id)>
                                        {{ $periodo->nombre }}
                               </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Botón de Cargar Alumnos --}}
                <div class="mb-6">
                    <button @click="cargarTabla()" 
                            :disabled="!selectedGrupo || !selectedMateria || !selectedPeriodo || loading.tabla"
                            class="px-5 py-2 bg-princeton text-white font-semibold rounded-lg shadow-md hover:bg-slate-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading.tabla">Cargar Alumnos</span>
                        <span x-show="loading.tabla">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Cargando...
                        </span>
                    </button>
                </div>
                
                <hr class="mb-4">
                
                {{-- Nombre del Maestro (siempre será el usuario actual, pero se mantiene por consistencia de JSON) --}}
                <div x-show="tabla.nombreMaestro && tabla.alumnos.length > 0" class="mb-4 text-sm text-gray-700">
                    Maestro asignado: <strong x-text="tabla.nombreMaestro"></strong>
                </div>

                {{-- Advertencia de Configuración --}}
                <div x-show="tabla.setup_warning" 
                     class="my-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800"
                     role="alert">
                    <p class="font-bold">Advertencia de Configuración</p>
                    <p x-text="tabla.setup_warning"></p>
                </div>

                {{-- Inicio de la Tabla --}}
                <div x-show="tabla.alumnos.length > 0" class="mt-6">
                    <form action="{{ route('admin.calificaciones.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="periodo_id" :value="selectedPeriodo">
                        <input type="hidden" name="materia_id" :value="selectedMateria">
                        <input type="hidden" name="grupo_id" :value="selectedGrupo">
                        
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-64 min-w-[16rem] sticky left-0 z-10 bg-gray-50">
                                            Alumno
                                        </th>
                                        <template x-for="criterio in tabla.criterios" :key="criterio.id">
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase min-w-[8rem]"
                                                x-text="criterio.nombre_criterio"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(alumno, index) in tabla.alumnos" :key="alumno.id">
                                        <tr :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 z-10"
                                                :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                                                x-text="`${alumno.apellido_paterno} ${alumno.apellido_materno} ${alumno.nombres}`">
                                            </td>
                                            
                                            <template x-for="criterio in tabla.criterios" :key="criterio.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    <input type="number"
                                                            step="0.1" 
                                                            min="0" 
                                                            max="10"
                                                            :name="`calificaciones[${alumno.id}][${criterio.id}]`"
                                                            :value="tabla.calificaciones[alumno.id] && tabla.calificaciones[alumno.id][criterio.id] ? tabla.calificaciones[alumno.id][criterio.id] : ''"
                                                            class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center"
                                                            
                                                            :disabled="criterio.es_promedio || criterio.es_faltas || criterio.es_calculado"
                                                            :class="{ 'bg-gray-100 font-bold': criterio.es_promedio, 'bg-gray-100': criterio.es_faltas }"
                                                    >
                                                </td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                                {{-- Footer con Promedio --}}
                                <tfoot x-show="tabla.promedioGrupo > 0" 
                                        class="bg-gray-100 border-t-2 border-gray-400">
                                    <tr>
                                        <td class="px-6 py-3 text-right text-sm font-bold text-gray-800 uppercase sticky left-0 z-10 bg-gray-100"
                                            :colspan="tabla.criterios.length">
                                            Promedio del Grupo
                                        </td>
                                        
                                        <template x-for="criterio in tabla.criterios">
                                            <td class="px-6 py-3 text-center text-sm font-bold text-gray-900">
                                                <span x-show="criterio.es_promedio" x-text="tabla.promedioGrupo.toFixed(2)"></span>
                                            </td>
                                        </template>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="mt-6 flex justify-end gap-4">
                            
                            {{-- Botón de Guardar --}}
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition"> Guardar Calificaciones </button>
                            
                            {{-- Botón de Generar Reporte --}}
                            <a x-show="tabla.alumnos.length > 0"
                               :href="reportUrlTemplate.replace(':grupoId', selectedGrupo).replace(':periodoId', selectedPeriodo).replace(':materiaId', selectedMateria)"
                               target="_blank"
                               class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 transition flex items-center gap-2">
                                
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                
                                Generar Reporte
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Mensajes de "No encontrado" --}}
                <div x-show="!loading.tabla && (tabla.alumnos.length === 0 || tabla.criterios.length === 0) && tabla.intentado"
                     class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
                    <p x-show="tabla.alumnos.length === 0">No se encontraron alumnos en el grupo seleccionado.</p>
                    <p x-show="tabla.criterios.length === 0">La materia seleccionada no tiene criterios de evaluación asignados.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- 
        SCRIPT DE ALPINE.JS SIMPLIFICADO Y CORREGIDO
        Usa la variable $gruposDelMaestro pre-cargada para poblar el select de Materias.
    --}}
    <script>
        function maestroCalificacionesManager() {
            return {
                // 1. Obtenemos las asignaciones pre-cargadas desde el controlador
                asignaciones: @json($gruposDelMaestro),

                // 2. IDs seleccionados
                selectedGrupo: '{{ old('grupo_id') }}' || '',
                selectedMateria: '{{ old('materia_id') }}' || '',
                selectedPeriodo: '{{ old('periodo_id') }}' || '',
                
                // 3. Plantilla de URL 
                reportUrlTemplate: '{{ url("/admin/reportes/concentrado-periodo") }}/:grupoId/:periodoId/:materiaId',
                
                // 4. Datos para el dropdown de materias (inicialmente vacío)
                materias: [],

                // 5. Datos de la tabla 
                tabla: {
                    alumnos: [],
                    criterios: [],
                    calificaciones: {},
                    promedioGrupo: 0,
                    nombreMaestro: '',
                    intentado: false,
                    setup_warning: '' 
                },

                // 6. Estado de carga 
                loading: {
                    tabla: false
                },

                // 7. Función de inicialización
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const grupoIdFromUrl = urlParams.get('grupo_id');
                    const oldGrupo = '{{ old('grupo_id') }}';

                    let initialGrupo = oldGrupo || grupoIdFromUrl;

                    if (initialGrupo && this.asignaciones[initialGrupo]) {
                        this.selectedGrupo = initialGrupo;
                        
                        // 2. Cargar dependencias (poblar el dropdown de materias)
                        this.grupoChanged(); 
                        
                        this.$nextTick(() => {
                            // 3. Seleccionar la materia y periodo si vienen de 'old'
                            this.selectedMateria = '{{ old('materia_id') }}' || '';
                            this.selectedPeriodo = '{{ old('periodo_id') }}' || '';
                            
                            // Si es un 'old' (recarga por fallo de validación), recargar la tabla
                            if (oldGrupo && this.selectedMateria && this.selectedPeriodo) {
                                this.cargarTabla();
                            }
                        });
                    }
                },

                // 8. Función de cambio de Grupo
                grupoChanged() {
                    // Resetea la materia y la tabla
                    this.selectedMateria = '';
                    this.materias = [];
                    this.resetTabla();

                    // Si el grupo seleccionado es válido,
                    // busca sus materias en el objeto 'asignaciones'
                    if (this.selectedGrupo && this.asignaciones[this.selectedGrupo]) {
                        // Las asignaciones ya tienen el formato {materia_id: 1, nombre_materia: 'Yoga'}
                        this.materias = this.asignaciones[this.selectedGrupo];
                    }
                },

                // 9. Función Cargar Tabla (llama a la misma ruta JSON que el admin)
                cargarTabla() {
                    if (!this.selectedGrupo || !this.selectedMateria || !this.selectedPeriodo) {
                        return;
                    }

                    this.loading.tabla = true;
                    this.tabla.intentado = true; 

                    const params = new URLSearchParams({
                        grupo_id: this.selectedGrupo,
                        materia_id: this.selectedMateria,
                        periodo_id: this.selectedPeriodo
                    });
                    
                    fetch(`{{ route('admin.json.tabla.calificaciones') }}?${params.toString()}`)
                        .then(res => {
                            if (!res.ok) {
                                throw new Error(`Error del servidor: ${res.status}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            this.tabla.alumnos = data.alumnos;
                            this.tabla.criterios = data.criterios;
                            this.tabla.calificaciones = data.calificaciones;
                            this.tabla.promedioGrupo = data.promedioGrupo;
                            this.tabla.nombreMaestro = data.nombreMaestro;
                            this.tabla.setup_warning = data.setup_warning || '';
                            this.loading.tabla = false;
                        })
                        .catch(err => {
                            console.error('Error al cargar la TABLA:', err);
                            alert('Hubo un error al cargar los datos de la tabla.');
                            this.resetTabla();
                            this.loading.tabla = false;
                        });
                },

                // 10. Función Resetear Tabla 
                resetTabla() {
                    this.tabla.alumnos = [];
                    this.tabla.criterios = [];
                    this.tabla.calificaciones = {};
                    this.tabla.promedioGrupo = 0;
                    this.tabla.nombreMaestro = '';
                    this.tabla.intentado = false;
                    this.tabla.setup_warning = '';
                }
            }
        }
    </script>
</x-app-layout>