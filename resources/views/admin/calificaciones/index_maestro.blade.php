<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Captura de Calificaciones (Maestro)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            {{-- 
                El x-data ahora llama a la nueva función "maestroCalificacionesManager()"
                que definiremos en el script de abajo.
            --}}
            <div x-data="maestroCalificacionesManager()" 
                 class="bg-white p-6 shadow-sm rounded-lg">
                
                {{-- 
                    BLOQUE DE SELECTORES SIMPLIFICADO
                    Se eliminan "Nivel" y "Grado".
                    "Grupo" se puebla desde el controlador.
                    "Materia" se puebla dinámicamente con Alpine.
                --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="grupo" class="block text-sm font-medium text-gray-700">Mis Grupos</label>
                        <select id="grupo" x-model="selectedGrupo" @change="grupoChanged()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecciona un grupo</option>
                            
                            {{-- 
                                Poblamos la lista de grupos desde el controlador.
                                Usamos $gruposDelMaestro (el groupBy)
                            --}}
                            @forelse($gruposDelMaestro as $grupoId => $asignaciones)
                                {{-- Usamos la primera asignación para obtener los nombres --}}
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

                    <div>
                        <label for="materia" class="block text-sm font-medium text-gray-700">Mis Materias</label>
                        <select id="materia" x-model="selectedMateria" 
                                :disabled="!selectedGrupo || materias.length === 0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                            
                            {{-- Este selector es llenado dinámicamente por Alpine --}}
                            <option value="">
                                <span x-show="!selectedGrupo">Selecciona un grupo primero</span>
                                <span x-show="selectedGrupo && materias.length === 0">Sin materias</span>
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

                <div class="mb-6">
                    <button @click="cargarTabla()" 
                            {{-- La condición :disabled se simplifica --}}
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
                
                {{-- 
                    EL FORMULARIO Y LA TABLA
                    El HTML es idéntico al del admin, pero se eliminan los
                    inputs ocultos 'grado_id' y 'nivel_id'
                --}}
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase ...">
                                            Alumno
                                        </th>
                                        <template x-for="criterio in tabla.criterios" :key="criterio.id">
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase"
                                                x-text="criterio.nombre_criterio"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(alumno, index) in tabla.alumnos" :key="alumno.id">
                                        <tr :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                            <td class="px-6 py-4 ... sticky left-0 ..."
                                                x-text="`${alumno.apellido_paterno} ${alumno.apellido_materno} ${alumno.nombres}`">
                                            </td>
                                            
                                            <template x-for="criterio in tabla.criterios" :key="criterio.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <input type="number"
                                                           step="0.1" 
                                                           min="0" 
                                                           max="10"
                                                           :name="`calificaciones[${alumno.id}][${criterio.id}]`"
                                                           :value="tabla.calificaciones[alumno.id] && tabla.calificaciones[alumno.id][criterio.id] ? tabla.calificaciones[alumno.id][criterio.id] : ''"
                                                           class="w-24 rounded-md border-gray-300 ... text-center"
                                                           
                                                           :disabled="criterio.es_promedio || criterio.es_faltas"
                                                           :class="{ 'bg-gray-100 font-bold': criterio.es_promedio, 'bg-gray-100': criterio.es_faltas }"
                                                    >
                                                </td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                                {{-- Footer con Promedio (sin cambios) --}}
                                <tfoot x-show="tabla.promedioGrupo > 0" 
                                       class="bg-gray-100 border-t-2 border-gray-400">
                                    <tr>
                                        <td class="px-6 py-3 text-right text-sm font-bold text-gray-800 uppercase"
                                            :colspan="tabla.criterios.length">
                                            Promedio del Grupo
                                        </td>
                                        
                                        <td class="px-6 py-3 text-center text-sm font-bold text-gray-900">
                                            <span x-text="tabla.promedioGrupo.toFixed(2)"></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition"> Guardar Calificaciones </button>
                        </div>
                    </form>
                </div>

                {{-- Mensajes de "No encontrado" (sin cambios) --}}
                <div x-show="!loading.tabla && (tabla.alumnos.length === 0 || tabla.criterios.length === 0) && tabla.intentado"
                     class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
                     <p x-show="tabla.alumnos.length === 0">No se encontraron alumnos en el grupo seleccionado.</p>
                     <p x-show="tabla.criterios.length === 0">La materia seleccionada no tiene criterios de evaluación asignados.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- 
        SCRIPT DE ALPINE.JS SIMPLIFICADO
        Se elimina toda la lógica de Nivel/Grado y carga dinámica de JSON.
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
                
                // 3. Plantilla de URL (idéntica a la del admin)
                reportUrlTemplate: '{{ url("/admin/reportes/concentrado-periodo") }}/:grupoId/:periodoId/:materiaId',
                
                // 4. Datos para el dropdown de materias (inicialmente vacío)
                materias: [],

                // 5. Datos de la tabla (idéntico al admin)
                tabla: {
                    alumnos: [],
                    criterios: [],
                    calificaciones: {},
                    promedioGrupo: 0,
                    nombreMaestro: '',
                    intentado: false 
                },

                // 6. Estado de carga (simplificado)
                loading: {
                    tabla: false
                },

                // 7. Función de inicialización
                init() {
                // --- INICIO DE CORRECCIÓN ---
                // Leemos los parámetros de la URL (ej: ?grupo_id=3)
                const urlParams = new URLSearchParams(window.location.search);
                const grupoIdFromUrl = urlParams.get('grupo_id');
                
                // Obtenemos el valor 'old' (si falló una validación)
                const oldGrupo = '{{ old('grupo_id') }}';

                // 1. Determinar el grupo seleccionado
                // 'old' tiene prioridad, si no, usar el de la URL
                let initialGrupo = oldGrupo || grupoIdFromUrl;

                if (initialGrupo && this.asignaciones[initialGrupo]) {
                    this.selectedGrupo = initialGrupo;
                    
                    // 2. Cargar dependencias (poblar el dropdown de materias)
                    this.grupoChanged(); 
                    
                    this.$nextTick(() => {
                        // 3. Seleccionar la materia y periodo si vienen de 'old'
                        this.selectedMateria = '{{ old('materia_id') }}' || '';
                        this.selectedPeriodo = '{{ old('periodo_id') }}' || '';
                        
                        // Si es un 'old' (no un enlace de URL), recargar la tabla
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
                        this.materias = this.asignaciones[this.selectedGrupo];
                    }
                },

                // 9. Función Cargar Tabla (idéntica a la del admin)
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
                            this.loading.tabla = false;
                        })
                        .catch(err => {
                            console.error('Error al cargar la TABLA:', err);
                            alert('Hubo un error al cargar los datos de la tabla.');
                            this.resetTabla();
                            this.loading.tabla = false;
                        });
                },

                // 10. Función Resetear Tabla (idéntica a la del admin)
                resetTabla() {
                    this.tabla.alumnos = [];
                    this.tabla.criterios = [];
                    this.tabla.calificaciones = {};
                    this.tabla.promedioGrupo = 0;
                    this.tabla.nombreMaestro = '';
                    this.tabla.intentado = false;
                }
            }
        }
    </script>
</x-app-layout>