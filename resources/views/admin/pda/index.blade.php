<x-app-layout>
    {{-- CONTENIDO DE LA PÁGINA --}}
    <div class="p-6" x-data="pdaManager()">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Evaluación PDA (Preescolar)</h2>

        {{-- SELECTORES --}}
        {{-- Cambiamos a grid-cols-5 para acomodar el nuevo campo --}}
        <div class="bg-white p-4 rounded-lg shadow mb-6 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            
            {{-- 1. CICLO ESCOLAR (NUEVO) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Ciclo Escolar</label>
                <select x-model="selectedCiclo" @change="cambioCiclo()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Seleccione Ciclo</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 2. NIVEL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Nivel</label>
                <select x-model="selectedNivel" @change="loadGrados()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Seleccione Nivel</option>
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id ?? $nivel->nivel_id }}">{{ $nivel->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 3. GRADO --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Grado</label>
                <select x-model="selectedGrado" @change="loadGrupos()" :disabled="!selectedNivel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Seleccione Grado</option>
                    <template x-for="grado in grados" :key="grado.id || grado.grado_id">
                        <option :value="grado.id || grado.grado_id" x-text="grado.nombre"></option>
                    </template>
                </select>
            </div>

            {{-- 4. GRUPO --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Grupo</label>
                <select x-model="selectedGrupo" @change="resetData()" :disabled="!selectedGrado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Seleccione Grupo</option>
                    <template x-for="grupo in grupos" :key="grupo.id || grupo.grupo_id">
                        <option :value="grupo.id || grupo.grupo_id" x-text="grupo.nombre_grupo"></option>
                    </template>
                </select>
            </div>

            {{-- 5. PERIODO (Modificado para usar Alpine x-for) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">Periodo</label>
                <select x-model="selectedPeriodo" @change="cargarDatos()" :disabled="!selectedGrupo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Seleccione Periodo</option>
                    {{-- Usamos template porque los periodos cambian según el ciclo --}}
                    <template x-for="periodo in periodos" :key="periodo.id || periodo.periodo_id">
                        <option :value="periodo.id || periodo.periodo_id" x-text="periodo.nombre"></option>
                    </template>
                </select>
            </div>
        </div>

        {{-- ZONA DE TABLAS --}}
        <div x-show="loaded" style="display: none;">
            
            {{-- CONTROLES DE EDICIÓN --}}
            <div class="flex justify-end mb-4 space-x-2">
                <button @click="editing = !editing" 
                    :class="editing ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700'"
                    class="text-white px-4 py-2 rounded shadow">
                    <span x-text="editing ? 'Deshabilitar Edición' : 'Habilitar Edición'"></span>
                </button>
                <button @click="guardarCambios()" x-show="editing" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow flex items-center">
                    <span x-show="saving">Guardando...</span>
                    <span x-show="!saving">Guardar Todo</span>
                </button>
            </div>

            {{-- TABLA A: CAMPOS FORMATIVOS (FILTRADOS) --}}
            <div class="bg-white rounded-lg shadow overflow-x-auto mb-8">
                <h3 class="p-4 font-bold bg-gray-50 border-b">Campos Formativos</h3>
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 sticky left-0 bg-gray-100 z-10">Alumno</th>
                            <template x-for="campo in data.campos" :key="campo.id || campo.campo_id">
                                <th class="px-4 py-3 min-w-[200px]" x-text="campo.nombre"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="alumno in data.alumnos" :key="alumno.id || alumno.alumno_id">
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900 sticky left-0 bg-white z-10" x-text="alumno.apellido_paterno + ' ' + alumno.apellido_materno + ' ' + alumno.nombres"></td>
                                <template x-for="campo in data.campos" :key="campo.id || campo.campo_id">
                                    <td class="px-2 py-2 min-w-[200px]">
                                        <textarea 
                                            x-model="getValor(alumno.id || alumno.alumno_id, 'campo', campo.id || campo.campo_id).texto" 
                                            :disabled="!editing"
                                            class="w-full text-xs border-gray-200 rounded focus:ring-blue-500 focus:border-blue-500" 
                                            rows="4"></textarea>
                                    </td>
                                </template>
                            </tr>
                        </template>
                        {{-- Mensaje si no hay alumnos --}}
                        <tr x-show="data.alumnos.length === 0">
                            <td colspan="100%" class="px-4 py-3 text-center text-gray-500">
                                No se encontraron alumnos en este grupo.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- TABLA B: MATERIAS ESPECIFICAS --}}
            <template x-for="materia in data.materias" :key="materia.id || materia.materia_id">
                <div class="bg-white rounded-lg shadow overflow-x-auto mb-8">
                    <h3 class="p-4 font-bold bg-gray-50 border-b" x-text="'Materia: ' + materia.nombre"></h3>
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 w-1/4">Alumno</th>
                                <th class="px-4 py-3">Observaciones / PDA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="alumno in data.alumnos" :key="alumno.id || alumno.alumno_id">
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900" x-text="alumno.apellido_paterno + ' ' + alumno.apellido_materno + ' ' + alumno.nombres"></td>
                                    <td class="px-2 py-2">
                                        <textarea 
                                            x-model="getValor(alumno.id || alumno.alumno_id, 'materia', materia.id || materia.materia_id).texto"
                                            :disabled="!editing"
                                            class="w-full text-xs border-gray-200 rounded focus:ring-blue-500 focus:border-blue-500" 
                                            rows="3"></textarea>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

        </div>
    </div>

    {{-- SCRIPTS ALPINE.JS --}}
    <script>
    function pdaManager() {
        return {
            // Inicialización con variables de Blade (Ciclo Activo)
            selectedCiclo: '{{ $cicloActivo->id ?? "" }}',
            selectedNivel: '',
            selectedGrado: '',
            selectedGrupo: '',
            selectedPeriodo: '',
            
            // Periodos iniciales (del ciclo activo)
            periodos: @json($cicloActivo->periodos ?? []),
            grados: [],
            grupos: [],
            
            loaded: false,
            editing: false, 
            saving: false,
            
            data: {
                alumnos: [],
                campos: [],
                materias: [],
                valores: {} 
            },

            camposPermitidos: [
                "Lenguajes",
                "Saberes y Pensamiento Científico",
                "Ética, Naturaleza y Sociedad",
                "De lo Humano a lo Comunitario"
            ],

            // NUEVA FUNCIÓN: Cambio de Ciclo
            cambioCiclo() {
                // Reset de cascada hacia abajo
                this.selectedGrupo = '';
                this.selectedPeriodo = '';
                this.grupos = [];
                this.loaded = false;

                if(!this.selectedCiclo) {
                    this.periodos = [];
                    return;
                }

                // Obtener periodos del nuevo ciclo
                // NOTA: Asegúrate de tener la ruta en web.php: Route::get('/json/ciclos/{ciclo}/periodos', ...)
                fetch(`{{ url('/admin/json/ciclos') }}/${this.selectedCiclo}/periodos`)
                    .then(res => res.json())
                    .then(data => {
                        this.periodos = data;
                    })
                    .catch(err => console.error("Error cargando periodos:", err));

                // Si ya hay grado seleccionado, recargar grupos porque dependen del ciclo
                if(this.selectedGrado) {
                    this.loadGrupos();
                }
            },

            loadGrados() {
                this.selectedGrado = '';
                this.selectedGrupo = '';
                this.grados = [];
                this.grupos = [];
                this.loaded = false;

                if(!this.selectedNivel) return;
                
                let url = `{{ url('/admin/json/niveles') }}/${this.selectedNivel}/grados`;
                
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        this.grados = data;
                    })
                    .catch(err => console.error("Error cargando grados:", err));
            },

            loadGrupos() {
                this.selectedGrupo = '';
                this.grupos = [];
                this.loaded = false;

                if(!this.selectedGrado) return;

                // MODIFICADO: Añadimos ?ciclo_id=... para filtrar grupos del año correcto
                let url = `{{ url('/admin/json/grados') }}/${this.selectedGrado}/grupos?ciclo_id=${this.selectedCiclo}`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        this.grupos = data;
                    })
                    .catch(err => console.error("Error cargando grupos:", err));
            },

            resetData() {
                this.loaded = false;
            },

            cargarDatos() {
                if(!this.selectedGrupo || !this.selectedPeriodo) return;
                
                this.loaded = false; 
                this.data.alumnos = []; 

                const params = new URLSearchParams({
                    grupo_id: this.selectedGrupo,
                    periodo_id: this.selectedPeriodo
                });

                fetch(`{{ route('admin.json.pda.data') }}?${params.toString()}`)
                    .then(res => {
                        if (!res.ok) throw new Error("Error del servidor");
                        return res.json();
                    })
                    .then(resp => {
                        this.data.alumnos = resp.alumnos;

                        this.data.campos = resp.campos.filter(c => {
                            return this.camposPermitidos.some(permitido => 
                                c.nombre.trim().toLowerCase() === permitido.toLowerCase() ||
                                c.nombre.includes(permitido)
                            );
                        });
                        
                        const uniqueCampos = [];
                        const mapCampos = new Map();
                        for (const item of this.data.campos) {
                            if(!mapCampos.has(item.nombre)){
                                mapCampos.set(item.nombre, true);
                                uniqueCampos.push(item);
                            }
                        }
                        this.data.campos = uniqueCampos;

                        this.data.materias = resp.materias;
                        this.data.valores = {}; 

                        resp.evaluaciones.forEach(ev => {
                            let key = '';
                            if(ev.campo_formativo_id) {
                                key = `al_${ev.alumno_id}_cf_${ev.campo_formativo_id}`;
                            } else if (ev.materia_id) {
                                key = `al_${ev.alumno_id}_mat_${ev.materia_id}`;
                            }
                            
                            if(key) {
                                this.data.valores[key] = { 
                                    texto: ev.observacion,
                                    id: ev.id 
                                };
                            }
                        });

                        this.loaded = true;
                    })
                    .catch(err => {
                        console.error("Error cargando tabla:", err);
                        alert("Error al cargar los datos.");
                    });
            },

            getValor(alumnoId, tipo, idTipo) {
                let key = '';
                if(tipo === 'campo') key = `al_${alumnoId}_cf_${idTipo}`;
                else key = `al_${alumnoId}_mat_${idTipo}`;

                if (!this.data.valores[key]) {
                    this.data.valores[key] = { 
                        texto: '', 
                        alumno_id: alumnoId, 
                        campo_formativo_id: (tipo === 'campo' ? idTipo : null),
                        materia_id: (tipo === 'materia' ? idTipo : null)
                    };
                }
                return this.data.valores[key];
            },

            guardarCambios() {
                this.saving = true;
                const payload = Object.values(this.data.valores);

                fetch('{{ route("admin.pda.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        periodo_id: this.selectedPeriodo,
                        grupo_id: this.selectedGrupo,
                        evaluaciones: payload
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert('Información guardada correctamente');
                    this.saving = false;
                    this.editing = false;
                })
                .catch(err => {
                    console.error(err);
                    alert('Error al guardar');
                    this.saving = false;
                });
            }
        }
    }
    </script>
</x-app-layout>