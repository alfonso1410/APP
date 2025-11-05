<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Generar Boletas de Calificaciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            {{-- Usamos Alpine.js para manejar los selectores dinámicos --}}
            <div x-data="boletasManager()" 
                 class="bg-white p-6 shadow-sm rounded-lg">
                
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    
                    {{-- 1. Selector de Nivel --}}
                    <div>
                        <label for="nivel" class="block text-sm font-medium text-gray-700">Nivel</label>
                        <select id="nivel" x-model="selectedNivel" @change="nivelChanged()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Selecciona un nivel</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Selector de Grado --}}
                    <div>
                        <label for="grado" class="block text-sm font-medium text-gray-700">Grado</label>
                        <select id="grado" x-model="selectedGrado" @change="gradoChanged()"
                                :disabled="loading.grados || !selectedNivel"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                            
                           <option value="">
                                <span x-show="loading.grados">Cargando...</span>
                                <span x-show="!loading.grados && !selectedNivel">Selecciona un nivel</span>
                                <span x-show="!loading.grados && selectedNivel && grados.length === 0">Sin grados</span>
                                <span x-show="!loading.grados && selectedNivel && grados.length > 0">Selecciona un grado</span>
                            </option>
                           <template x-for="grado in grados" :key="grado.id">
                                <option :value="grado.id" x-text="grado.nombre"></option>
                            </template>
                        </select>
                    </div>
                    
                    {{-- 3. Selector de Grupo --}}
                    <div>
                        <label for="grupo" class="block text-sm font-medium text-gray-700">Grupo</label>
                        <select id="grupo" x-model="selectedGrupo" @change="grupoChanged()"
                                :disabled="loading.grupos || !selectedGrado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                            <option value="">
                                <span x-show="loading.grupos">Cargando...</span>
                                <span x-show="!loading.grupos && !selectedGrado">Selecciona un grado</span>
                                <span x-show="!loading.grupos && selectedGrado && grupos.length === 0">Sin grupos</span>
                                <span x-show="!loading.grupos && selectedGrado && grupos.length > 0">Selecciona un grupo</span>
                            </option>
                            <template x-for="grupo in grupos" :key="grupo.id">
                                <option :value="grupo.id" x-text="grupo.nombre_grupo"></option>
                            </template>
                        </select>
                    </div>

                    {{-- 4. Selector de Alumno --}}
                    <div>
                        <label for="alumno" class="block text-sm font-medium text-gray-700">Alumno</label>
                        <select id="alumno" x-model="selectedAlumno"
                                :disabled="loading.alumnos || !selectedGrupo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                            <option value="">
                                <span x-show="loading.alumnos">Cargando...</span>
                                <span x-show="!loading.alumnos && !selectedGrupo">Selecciona un grupo</span>
                                <span x-show="!loading.alumnos && selectedGrupo && alumnos.length === 0">Sin alumnos</span>
                                <span x-show="!loading.alumnos && selectedGrupo && alumnos.length > 0">Selecciona un alumno</span>
                            </option>
                            <template x-for="alumno in alumnos" :key="alumno.id">
                                <option :value="alumno.id" x-text="alumno.nombre_completo"></option>
                            </template>
                        </select>
                    </div>

                </div>

                {{-- Botón de Generar Boleta --}}
                <div class="flex justify-end mt-6">
                    <a :href="reportUrl" 
                       target="_blank"
                       {{-- El botón se activa solo si todos los campos están llenos --}}
                       :class="{
                           'opacity-50 cursor-not-allowed': !selectedAlumno,
                           'hover:bg-teal-700': selectedAlumno
                       }"
                       class="px-5 py-2 bg-teal-600 text-white font-semibold rounded-lg shadow-md transition flex items-center gap-2">
                        
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Generar Boleta
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- Script de Alpine.js para los selectores dinámicos --}}
    <script>
        function boletasManager() {
            return {
                // IDs seleccionados
                selectedNivel: '',
                selectedGrado: '',
                selectedGrupo: '',
                selectedAlumno: '',

                // Datos para los dropdowns
                grados: [],
                grupos: [],
                alumnos: [],

                // Indicadores de carga
                loading: {
                    grados: false,
                    grupos: false,
                    alumnos: false
                },
                
                // Propiedad computada para la URL del reporte
                get reportUrl() {
                    if (!this.selectedGrupo || !this.selectedAlumno) {
                        return '#'; // URL por defecto si no hay selección
                    }
                    let template = '{{ route("admin.reportes.boleta.alumno", ["grupo" => ":grupoId", "alumno" => ":alumnoId"]) }}';
                    return template
                        .replace(':grupoId', this.selectedGrupo)
                        .replace(':alumnoId', this.selectedAlumno);
                },

                // 1. Nivel cambia: Cargar Grados
                nivelChanged() {
                    this.selectedGrado = '';
                    this.selectedGrupo = '';
                    this.selectedAlumno = '';
                    this.grados = [];
                    this.grupos = [];
                    this.alumnos = [];
                    
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
                        });
                },

                // 2. Grado cambia: Cargar Grupos
                gradoChanged() {
                    this.selectedGrupo = '';
                    this.selectedAlumno = '';
                    this.grupos = [];
                    this.alumnos = [];

                    if (!this.selectedGrado) return;

                    this.loading.grupos = true;
                    fetch(`{{ url('/admin/json/grados') }}/${this.selectedGrado}/grupos`)
                        .then(res => res.json())
                        .then(data => {
                            this.grupos = data;
                            this.loading.grupos = false;
                        });
                },
                
                // 3. Grupo cambia: Cargar Alumnos
                grupoChanged() {
                    this.selectedAlumno = '';
                    this.alumnos = [];

                    if (!this.selectedGrupo) return;

                    this.loading.alumnos = true;
                    // Usamos la nueva ruta que creamos
                    fetch(`{{ route('admin.json.grupo.alumnos', ['grupo' => ':grupoId']) }}`.replace(':grupoId', this.selectedGrupo))
                        .then(res => res.json())
                        .then(data => {
                            this.alumnos = data;
                            this.loading.alumnos = false;
                        });
                }
            }
        }
    </script>
</x-app-layout>