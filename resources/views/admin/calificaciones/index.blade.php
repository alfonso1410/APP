<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Captura de Calificaciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            <div x-data="calificacionesManager()" 
                 class="bg-white p-6 shadow-sm rounded-lg">
                
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                  <div>
                        <label for="nivel" class="block text-sm font-medium text-gray-700">Nivel</label>
                        <select id="nivel" x-model="selectedNivel" @change="nivelChanged()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecciona un nivel</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}" 
                                        @selected(old('nivel_id') == $nivel->id)>
                                    {{ $nivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="grado" class="block text-sm font-medium text-gray-700">Grado</label>
                        <select id="grado" x-model="selectedGrado" @change="gradoChanged()"
                                :disabled="loading.grados || !selectedNivel"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                            
                         <option value="">
                                <span x-show="loading.grados">Cargando grados...</span>
                                <span x-show="!loading.grados && !selectedNivel">Selecciona un nivel</span>
                                <span x-show="!loading.grados && selectedNivel && grados.length === 0">Sin grados</span>
                                <span x-show="!loading.grados && selectedNivel && grados.length > 0">Selecciona un grado</span>
                            </option>
                           <template x-for="grado in grados" :key="grado.id">
                                <option :value="grado.id" 
                                        x-text="grado.nombre"
                                        :selected="grado.id == selectedGrado">
                                </option>
                            </template>
                        </select>
                    </div>
                    
                     <div>
                        <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo</label>
                        <select id="grupo" x-model="selectedGrupo" :disabled="loading.grupos || !selectedGrado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">

                            <option value="">
                                <span x-show="loading.grupos">Cargando...</span>
                                <span x-show="!loading.grupos && !selectedGrado">Selecciona un grado</span>
                                <span x-show="!loading.grupos && selectedGrado && grupos.length === 0">Sin grupos</span>
                                <span x-show="!loading.grupos && selectedGrado && grupos.length > 0">Selecciona un grupo</span>
                            </option>
                            <template x-for="grupo in grupos" :key="grupo.id">
                                <option :value="grupo.id" 
            x-text="grupo.nombre_grupo"
            :selected="grupo.id == selectedGrupo">
    </option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="materia" class="block text-sm font-medium text-gray-700">Materia</label>
                        <select id="materia" x-model="selectedMateria" :disabled="loading.materias || !selectedGrado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">

                            <option value="">
                                <span x-show="loading.materias">Cargando...</span>
                                <span x-show="!loading.materias && !selectedGrado">Selecciona un grado</span>
                                <span x-show="!loading.materias && selectedGrado && materias.length === 0">Sin materias</span>
                                <span x-show="!loading.materias && selectedGrado && materias.length > 0">Selecciona una materia</span>
                            </option>
                            <template x-for="materia in materias" :key="materia.id">
                                <option :value="materia.id" 
            x-text="materia.nombre"
            :selected="materia.id == selectedMateria">
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
                            :disabled="!selectedGrado || !selectedGrupo || !selectedMateria || !selectedPeriodo || loading.tabla"
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

                    <a x-show="tabla.alumnos.length > 0"
                        :href="reportUrlTemplate.replace(':grupoId', selectedGrupo).replace(':periodoId', selectedPeriodo).replace(':materiaId', selectedMateria)"
                        target="_blank"
                        class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md hover:bg-teal-700 transition flex items-center gap-2">
                            
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            
                            Generar Reporte
                        </a>
                </div>
                {{-- INICIO: Mostrar Nombre del Maestro --}}
            <div x-show="tabla.nombreMaestro && tabla.alumnos.length > 0" class="mb-4 text-sm text-gray-700">
                Maestro asignado: <strong x-text="tabla.nombreMaestro"></strong>
            </div>
<div x-show="tabla.alumnos.length > 0" class="mt-6">
    <form action="{{ route('admin.calificaciones.store') }}" method="POST">
        @csrf
        <input type="hidden" name="periodo_id" :value="selectedPeriodo">
        <input type="hidden" name="materia_id" :value="selectedMateria">
        <input type="hidden" name="grado_id" :value="selectedGrado">
        <input type="hidden" name="grupo_id" :value="selectedGrupo">
        <input type="hidden" name="nivel_id" :value="selectedNivel">
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
                                    >
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
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

                <div x-show="!loading.tabla && (tabla.alumnos.length === 0 || tabla.criterios.length === 0) && tabla.intentado"
                     class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700">
                     <p x-show="tabla.alumnos.length === 0">No se encontraron alumnos en el grupo seleccionado.</p>
                     <p x-show="tabla.criterios.length === 0">La materia seleccionada no tiene criterios de evaluación asignados.</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        function calificacionesManager() {
            return {
                // IDs seleccionados
               selectedNivel:   '{{ old('nivel_id') }}' || '',
                selectedGrado:   '{{ old('grado_id') }}' || '',
                selectedGrupo:   '{{ old('grupo_id') }}' || '',
                selectedMateria: '{{ old('materia_id') }}' || '',
                selectedPeriodo: '{{ old('periodo_id') }}' || '',

                reportUrlTemplate: '{{ url("/admin/reportes/concentrado-periodo") }}/:grupoId/:periodoId/:materiaId',
                // --- FIN DE MODIFICACIÓN ---
                   // Datos para los dropdowns
                grados: [],
                grupos: [],
                materias: [],

                // Datos para la tabla
                tabla: {
                    alumnos: [],
                    criterios: [],
                    calificaciones: {},
                    promedioGrupo: 0,
                    nombreMaestro: '',
                    intentado: false 
                },

                // Indicadores de carga
                loading: {
                    grados: false,
                    grupos: false,
                    materias: false,
                    tabla: false
                },

            init() {
                if (this.selectedGrado) {
                    // Si 'selectedGrado' tiene un valor 'old',
                    // cargamos las dependencias y la tabla automáticamente.
                    this.autoLoadOnRefresh();
                }
            },

            // --- AÑADIR ESTA NUEVA FUNCIÓN async ---
            async autoLoadOnRefresh() {
                    if (!this.selectedNivel) return;
                    
                    this.loading.grados = true;
                    this.loading.grupos = true;
                    this.loading.materias = true;

                    try {
                        // --- 4. MODIFICAR AUTOLOAD ---
                        // Primero, esperamos a que carguen los grados
                        let gradosUrl = (this.selectedNivel === 'extra')
                            ? '{{ route("admin.json.grados.extra") }}'
                            : `{{ url('/admin/json/niveles') }}/${this.selectedNivel}/grados`;

                        const gradosData = await fetch(gradosUrl).then(res => res.json());
                        this.grados = gradosData;
                        this.loading.grados = false;
                        if (!this.grados.find(g => g.id == this.selectedGrado)) {
                            this.selectedGrado = '';
                        }
                        
                        // Si seguimos teniendo un grado, cargamos grupos y materias
                        if (this.selectedGrado) {
                            const [gruposData, materiasData] = await Promise.all([
                                fetch(`{{ url('/admin/json/grados') }}/${this.selectedGrado}/grupos`).then(res => res.json()),
                                fetch(`{{ url('/admin/json/grados') }}/${this.selectedGrado}/materias`).then(res => res.json())
                            ]);
                            
                            this.grupos = gruposData;
                            this.materias = materiasData;
                            
                            if (!this.grupos.find(g => g.id == this.selectedGrupo)) this.selectedGrupo = '';
                            if (!this.materias.find(m => m.id == this.selectedMateria)) this.selectedMateria = '';
                        }
                    
                    } catch (err) {
                        console.error('Error al autocargar dependencias:', err);
                        this.grados = []; this.grupos = []; this.materias = [];
                    } finally {
                        this.loading.grados = false;
                        this.loading.grupos = false;
                        this.loading.materias = false;
                        
                        if (this.selectedGrado && this.selectedGrupo && this.selectedMateria && this.selectedPeriodo) {
                            this.cargarTabla();
                        }
                    }
                },
             
                nivelChanged() {
                    // Resetea todo hacia abajo
                    this.selectedGrado = '';
                    this.selectedGrupo = '';
                    this.selectedMateria = '';
                    this.grados = [];
                    this.grupos = [];
                    this.materias = [];
                    this.resetTabla();

                    if (this.selectedNivel) {
                        this.loadGrados();
                    }
                },

                loadGrados() {
                    if (!this.selectedNivel) return;
                    
                    this.loading.grados = true;

                    let url = (this.selectedNivel === 'extra')
                        ? '{{ route("admin.json.grados.extra") }}'
                        : `{{ url('/admin/json/niveles') }}/${this.selectedNivel}/grados`;
                        
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.grados = data;
                            this.loading.grados = false;
                        })
                        .catch(err => {
                            console.error('Error cargando GRADOS:', err);
                            alert('Hubo un error al cargar grados.');
                            this.grados = [];
                            this.loading.grados = false;
                        });
                },
                // --- MÉTODOS ---

             gradoChanged() {
                // Esta función SÍ debe resetear, porque es una acción del usuario
                this.selectedGrupo = '';
                this.selectedMateria = '';
                this.grupos = [];
                this.materias = [];
                this.resetTabla();

                if (!this.selectedGrado) return;

                this.loadGrupos();
                this.loadMaterias();
            },

                loadGrupos() {
                    this.loading.grupos = true;
                    // Usamos la ruta que definimos en web.php
                    fetch(`{{ url('/admin/json/grados') }}/${this.selectedGrado}/grupos`)
                        .then(res => res.json())
                        .then(data => {
                            this.grupos = data;
                            this.loading.grupos = false;
                        })
                        // =============================================
                        // == INICIO DE CORRECCIÓN: AÑADIR .catch() ==
                        // =============================================
                        .catch(err => {
                            console.error('Error cargando GRUPOS:', err);
                            alert('Hubo un error en el servidor al cargar grupos.');
                            this.grupos = []; // Resetea a un array vacío
                            this.loading.grupos = false; // Desbloquea el select
                        });
                        // =============================================
                        // == FIN DE CORRECCIÓN                       ==
                        // =============================================
                },

                loadMaterias() {
                    this.loading.materias = true;
                    fetch(`{{ url('/admin/json/grados') }}/${this.selectedGrado}/materias`)
                        .then(res => res.json())
                        .then(data => {
                            this.materias = data;
                            this.loading.materias = false;
                        })
                        // =============================================
                        // == INICIO DE CORRECCIÓN: AÑADIR .catch() ==
                        // =============================================
                        .catch(err => {
                            console.error('Error cargando MATERIAS:', err);
                            alert('Hubo un error en el servidor al cargar materias.');
                            this.materias = []; // Resetea a un array vacío
                            this.loading.materias = false; // Desbloquea el select
                        });
                        // =============================================
                        // == FIN DE CORRECCIÓN                       ==
                        // =============================================
                },

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
                            // Añadimos una validación extra
                            if (!res.ok) {
                                // Lanza un error si la respuesta no es 200-299
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
                            // Este es el alert que ya veías
                            alert('Hubo un error al cargar los datos de la tabla.');
                            this.resetTabla(); // Resetea la tabla
                            this.loading.tabla = false;
                        });
                },

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