<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Evaluación PDA (Maestros)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="pdaMaestroManager()" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                {{-- SECCIÓN DE FILTROS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Mis Grupos</label>
                        <select x-model="selectedGrupo" @change="onGrupoChange()"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                            <option value="">-- Seleccionar Grupo --</option>
                            <template x-for="grupo in misGrupos" :key="grupo.id">
                                <option :value="grupo.id" x-text="grupo.nombre"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700">Tipo de Tabla</label>
                        <select x-model="selectedTipo" :disabled="!selectedGrupo"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1 disabled:bg-gray-100">
                            <option value="">-- Seleccionar Tipo --</option>
                            <template x-for="opcion in opcionesDisponibles" :key="opcion.val">
                                <option :value="opcion.val" x-text="opcion.label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700">Trimestre / Periodo</label>
                        <select x-model="selectedPeriodo"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1">
                            <option value="">-- Seleccionar Periodo --</option>
                            @foreach ($periodos as $periodo)
                                <option value="{{ $periodo->periodo_id }}">{{ $periodo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Botón Cargar --}}
                <div class="flex justify-end mb-4 border-b pb-4">
                    <button @click="cargarDatos()"
                            :disabled="!selectedGrupo || !selectedTipo || !selectedPeriodo || loading"
                            class="bg-princeton hover:bg-blue-900 text-white font-bold py-2 px-4 rounded disabled:opacity-50 flex items-center">
                        <span x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </span>
                        <span x-text="loading ? 'Cargando...' : 'Cargar Tabla'"></span>
                    </button>
                </div>

                {{-- ÁREA DE TABLA --}}
                <div x-show="alumnos.length > 0" x-transition class="mt-4">
                    
                    {{-- BARRA DE CONTROL --}}
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4 bg-gray-50 p-3 rounded-lg border">
                        
                        {{-- Estado --}}
                        <div class="flex items-center">
                            <span class="text-sm font-bold text-gray-700 mr-2">Estado:</span>
                            <span :class="periodoEstado === 'ABIERTO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                  class="px-2 py-1 rounded-full text-xs font-semibold"
                                  x-text="periodoEstado">
                            </span>
                        </div>

                        {{-- Toggle Habilitar Edición (SE OCULTA SI ESTAMOS EDITANDO) --}}
                        <div class="flex items-center" x-show="periodoEstado === 'ABIERTO' && !modoEdicion">
                            <label for="toggleEdit" class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" id="toggleEdit" class="sr-only" x-model="modoEdicion">
                                    <div class="w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow -left-1 -top-1 transition"
                                         :class="modoEdicion ? 'transform translate-x-full bg-blue-600' : ''"></div>
                                </div>
                                <div class="ml-3 text-gray-700 font-medium">Habilitar Edición</div>
                            </label>
                        </div>

                        {{-- Mensaje de solo lectura --}}
                        <div x-show="periodoEstado !== 'ABIERTO'" class="text-xs text-red-600 font-bold">
                            Periodo Cerrado - Solo Lectura
                        </div>

                        {{-- Botón Guardar (SOLO SE MUESTRA SI ESTAMOS EDITANDO) --}}
                        <button @click="guardarCambios()"
                                x-show="modoEdicion && periodoEstado === 'ABIERTO'"
                                :disabled="saving"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow flex items-center">
                            <span x-text="saving ? 'Guardando...' : 'Guardar Cambios'"></span>
                        </button>
                    </div>

                    <div class="overflow-x-auto border rounded-lg shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-100 z-10 w-64 shadow-r border-b">Alumno</th>
                                    <template x-for="col in columnas" :key="col.id">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[200px] border-l border-b" x-text="col.nombre"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(alumno, index) in alumnos" :key="alumno.alumno_id">
                                    <tr :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 z-10 w-64 border-r"
                                            :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                                            x-text="`${alumno.apellido_paterno} ${alumno.apellido_materno} ${alumno.nombres}`">
                                        </td>
                                        <template x-for="col in columnas" :key="col.id">
                                            <td class="px-2 py-2 align-top border-l border-gray-100">
                                                <textarea rows="3"
                                                    class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                                                    :disabled="!modoEdicion || periodoEstado !== 'ABIERTO'"
                                                    :class="(!modoEdicion || periodoEstado !== 'ABIERTO') ? 'bg-gray-100 text-gray-500' : 'bg-white'"
                                                    x-model="getEvaluacionModel(alumno.alumno_id, col.id).texto"
                                                    placeholder="Observación..."></textarea>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="!loading && alumnos.length === 0 && intentado" class="mt-8 text-center text-gray-500">
                    No se encontraron datos para la selección actual.
                </div>
            </div>
        </div>
    </div>

    <script>
        function pdaMaestroManager() {
            return {
                misGrupos: @json($misGrupos),
                selectedGrupo: '',
                selectedTipo: '',
                selectedPeriodo: '',
                opcionesDisponibles: [],
                alumnos: [],
                columnas: [],     
                evaluaciones: {}, 
                periodoEstado: '',
                loading: false,
                saving: false,
                intentado: false,
                modoEdicion: false,

                onGrupoChange() {
                    const grupo = this.misGrupos.find(g => g.id == this.selectedGrupo);
                    this.opcionesDisponibles = grupo ? grupo.opciones : [];
                    this.selectedTipo = ''; 
                    this.alumnos = [];      
                    this.columnas = [];
                },

                getEvaluacionModel(alumnoId, colId) {
                    if (!this.evaluaciones[alumnoId]) this.evaluaciones[alumnoId] = {};
                    if (!this.evaluaciones[alumnoId][colId]) this.evaluaciones[alumnoId][colId] = { texto: '' }; 
                    return this.evaluaciones[alumnoId][colId];
                },

                cargarDatos() {
                    this.loading = true;
                    this.intentado = true;
                    this.alumnos = [];
                    this.columnas = [];
                    this.modoEdicion = false; 

                    const params = new URLSearchParams({
                        grupo_id: this.selectedGrupo,
                        periodo_id: this.selectedPeriodo,
                        tipo: this.selectedTipo
                    });

                    fetch(`{{ route('admin.json.pda.data') }}?${params.toString()}`)
                        .then(response => response.json())
                        .then(data => {
                            this.alumnos = data.alumnos;
                            this.periodoEstado = data.periodo_estado;

                            if (this.selectedTipo === 'campos_formativos') {
                                this.columnas = data.campos.map(c => ({ 
                                    id: c.campo_formativo_id || c.id || c.campo_id, 
                                    nombre: c.nombre 
                                }));
                            } else {
                                this.columnas = data.materias.map(m => ({ 
                                    id: m.materia_id || m.id, 
                                    nombre: m.nombre 
                                }));
                            }

                            this.evaluaciones = {};
                            this.alumnos.forEach(alum => {
                                this.evaluaciones[alum.alumno_id] = {}; 
                            });

                            data.evaluaciones.forEach(ev => {
                                const aId = ev.alumno_id;
                                const cId = (this.selectedTipo === 'campos_formativos') ? ev.campo_formativo_id : ev.materia_id;
                                if (!this.evaluaciones[aId]) this.evaluaciones[aId] = {};
                                this.evaluaciones[aId][cId] = { texto: ev.observacion };
                            });
                            this.loading = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Hubo un error al cargar los datos.');
                            this.loading = false;
                        });
                },

                guardarCambios() {
                    if (!confirm('¿Estás seguro de guardar los cambios?')) return;
                    this.saving = true;
                    let payload = [];

                    this.alumnos.forEach(alum => {
                        this.columnas.forEach(col => {
                            const evalData = this.getEvaluacionModel(alum.alumno_id, col.id); 
                            const texto = evalData.texto ? evalData.texto.trim() : '';

                            let item = {
                                alumno_id: alum.alumno_id,
                                texto: texto
                            };

                            if (this.selectedTipo === 'campos_formativos') {
                                item.campo_formativo_id = col.id;
                            } else {
                                item.materia_id = col.id;
                            }
                            
                            payload.push(item);
                        });
                    });

                    fetch('{{ route("admin.pda.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            evaluaciones: payload,
                            periodo_id: this.selectedPeriodo
                        })
                    })
                    .then(response => {
                        if (!response.ok) return response.json().then(err => Promise.reject(err));
                        return response.json();
                    })
                    .then(data => {
                        alert('Datos guardados correctamente.');
                        this.modoEdicion = false; // ESTO HACE QUE REAPAREZCA EL BOTÓN HABILITAR
                        this.saving = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const msg = error.error || 'Error al guardar.';
                        alert(msg);
                        this.saving = false;
                    });
                }
            }
        }
    </script>
</x-app-layout>