<x-app-layout>
    <style>
        [x-cloak]{display:none!important}
        :root{--system-primary:#2E3E5C;--page-bg:#F5F6F8;--border-color:#E2E6EC;--text-primary:#1A2332;--text-secondary:#4A5A78;--text-muted:#7A8AA0}
        .estado-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
        .estado-al_dia{background:#E6F7ED;color:#2F855A}
        .estado-desincronizado{background:#FEF3C7;color:#B7791F}
        .estado-sin_actividades{background:#F3F4F6;color:#6B7280}
        .acordeon-header{cursor:pointer;transition:background .15s}
        .acordeon-header:hover{background:#F7F9FC}
    </style>

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="text-xl font-semibold text-[#1A2332] tracking-tight">Actividades Diarias - Supervisión</h2>
                <p class="text-sm text-[#4A5A78] mt-0.5">Panel de control y estado de sincronización</p>
            </div>
        </div>
    </x-slot>

    <div x-data="dashboardActividades()" x-cloak class="space-y-6">
        <!-- FILTROS EN CASCADA -->
        <div class="bg-white rounded-md p-4 border border-[#E2E6EC]">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <!-- Periodo -->
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Periodo</label>
                    <select x-model="selectedPeriodo" @change="cargarResumen()" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]">
                        <option value="">Selecciona periodo</option>
                        @foreach($periodos as $p)
                            <option value="{{ $p->periodo_id }}">{{ $p->nombre }} ({{ $p->estado }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Nivel (Regulares + Extracurriculares) -->
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Nivel (Opcional)</label>
                    <select x-model="selectedNivel" @change="nivelChanged()" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]">
                        <option value="">Todos los niveles</option>
                        @if(isset($niveles))
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->nivel_id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        @endif
                        <option value="extracurricular">Extracurriculares</option>
                    </select>
                </div>

                <!-- Grado -->
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Grado (Opcional)</label>
                    <select x-model="selectedGrado" @change="gradoChanged()" :disabled="loading.grados || !selectedNivel" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] disabled:opacity-50">
                        <option value="">Todos los grados</option>
                        <template x-for="g in grados" :key="g.id || g.grado_id">
                            <option :value="g.id || g.grado_id" x-text="g.nombre"></option>
                        </template>
                    </select>
                </div>

                <!-- Grupo -->
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Grupo (Opcional)</label>
                    <select x-model="selectedGrupo" @change="cargarResumen()" :disabled="loading.grupos || !selectedGrado" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] disabled:opacity-50">
                        <option value="">Todos los grupos</option>
                        <template x-for="gr in grupos" :key="gr.id || gr.grupo_id">
                            <option :value="gr.id || gr.grupo_id" x-text="gr.nombre_grupo || gr.nombre"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        <!-- ESTADO DE CARGA -->
        <div x-show="loading.resumen" class="bg-white rounded-md p-8 border border-[#E2E6EC] text-center">
            <svg class="animate-spin h-8 w-8 text-[#2E3E5C] mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm text-[#4A5A78]">Cargando resumen de actividades...</p>
        </div>

        <!-- PERIODO CERRADO -->
        <template x-if="!loading.resumen && resumen && resumen.periodo_estado === 'CERRADO'">
            <div class="bg-red-50 border border-red-200 rounded-md p-4 text-sm text-red-700">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <strong>Periodo cerrado</strong>
                </div>
                <p>Este periodo está cerrado. Las calificaciones se encuentran en modo solo lectura.</p>
            </div>
        </template>

        <!-- PANEL PRINCIPAL -->
        <template x-if="!loading.resumen && resumen">
            <div class="space-y-6">
                <!-- BLOQUE DE ATENCIÓN REQUERIDA (SIN BOTÓN DE REVISAR DRAWER) -->
                <div class="bg-white rounded-md p-4 border border-[#E2E6EC]">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#B7791F] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-sm text-[#1A2332] mb-1">Atención requerida</h3>
                                <p class="text-sm text-[#4A5A78]">
                                    <span class="font-bold text-[#B7791F]" x-text="resumen.totales.desincronizado"></span> pendiente(s) de recálculo · 
                                    <span class="font-bold text-[#6B7280]" x-text="resumen.totales.sin_actividades"></span> materia(s) sin actividades
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button 
                                type="button"
                                @click="filtroRapido = 'desincronizado'" 
                                :disabled="resumen.totales.desincronizado === 0" 
                                class="px-3 py-1.5 text-xs font-semibold text-[#B7791F] bg-amber-50 border border-amber-200 rounded-md hover:bg-amber-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Filtrar pendientes
                            </button>
                            <button 
                                type="button"
                                @click="filtroRapido = 'sin_actividades'" 
                                :disabled="resumen.totales.sin_actividades === 0" 
                                class="px-3 py-1.5 text-xs font-semibold text-[#6B7280] bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                Incompletos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- FILTROS RÁPIDOS DE LISTA -->
                <div class="flex items-center gap-1 text-sm">
                    <span class="text-[#7A8AA0] mr-2">Filtrar:</span>
                    <button type="button" @click="filtroRapido = 'todos'" :class="filtroRapido === 'todos' ? 'text-[#2E3E5C] font-semibold bg-[#E8ECF1]' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-3 py-1 rounded-md transition-colors">
                        Todos
                    </button>
                    <button type="button" @click="filtroRapido = 'desincronizado'" :class="filtroRapido === 'desincronizado' ? 'text-[#B7791F] font-semibold bg-amber-50' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-3 py-1 rounded-md transition-colors">
                        Pendientes
                    </button>
                    <button type="button" @click="filtroRapido = 'sin_actividades'" :class="filtroRapido === 'sin_actividades' ? 'text-[#6B7280] font-semibold bg-gray-100' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-3 py-1 rounded-md transition-colors">
                        Sin actividades
                    </button>
                </div>

                <!-- LISTA DE GRUPOS (ACORDEÓN) -->
                <div class="space-y-2">
                    <template x-for="grupo in gruposFiltrados" :key="grupo.grupo_id">
                        <div class="bg-white rounded-md border border-[#E2E6EC] overflow-hidden">
                            <!-- HEADER DEL GRUPO -->
                            <div @click="toggleGrupo(grupo.grupo_id)" class="acordeon-header px-4 py-3 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 flex-1">
                                    <svg class="w-4 h-4 text-[#7A8AA0] transition-transform" :class="gruposAbiertos.includes(grupo.grupo_id) ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="font-semibold text-sm text-[#1A2332]" x-text="`${grupo.nivel_nombre} · ${grupo.grado_nombre} ${grupo.grupo_nombre}`"></h4>
                                            <span class="estado-badge" :class="'estado-' + grupo.estado">
                                                <span x-text="grupo.estado === 'al_dia' ? 'Al día' : grupo.estado === 'desincronizado' ? 'Pendiente' : 'Incompleto'"></span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-[#7A8AA0]">
                                            <span x-text="grupo.total_materias"></span> materias asignadas · 
                                            <span x-text="grupo.total_actividades"></span> actividades registradas
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- CONTENIDO DEL GRUPO: MATERIAS -->
                            <div x-show="gruposAbiertos.includes(grupo.grupo_id)" x-transition class="border-t border-[#E2E6EC]">
                                <div class="p-4">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-xs text-[#7A8AA0] uppercase border-b border-[#E2E6EC]">
                                                <th class="pb-2 font-medium">Materia</th>
                                                <th class="pb-2 font-medium">Docente</th>
                                                <th class="pb-2 font-medium text-center">Actividades</th>
                                                <th class="pb-2 font-medium text-center">Estado</th>
                                                <th class="pb-2 font-medium text-right">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#E2E6EC]">
                                            <template x-for="materia in grupo.materias" :key="materia.materia_id">
                                                <tr class="hover:bg-[#F7F9FC] transition-colors">
                                                    <td class="py-2 font-medium text-[#1A2332]" x-text="materia.materia_nombre"></td>
                                                    <td class="py-2 text-[#4A5A78]" x-text="materia.maestro_nombre"></td>
                                                    <td class="py-2 text-center text-[#4A5A78]" x-text="materia.actividades"></td>
                                                    <td class="py-2 text-center">
                                                        <span class="estado-badge" :class="'estado-' + materia.estado">
                                                            <span x-text="materia.estado === 'al_dia' ? '✓ Al día' : materia.estado === 'desincronizado' ? '⚠ Pendiente' : '○ Sin acts'"></span>
                                                        </span>
                                                    </td>
                                                    <td class="py-2 text-right">
                                                        <a :href="generarLinkCaptura(grupo, materia)" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-[#2E3E5C] bg-[#E8ECF1] rounded-md hover:bg-[#D8DEE8] transition-colors">
                                                            Ver captura
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                            </svg>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ESTADO VACÍO -->
                    <div x-show="gruposFiltrados.length === 0" class="bg-white rounded-md p-8 border border-[#E2E6EC] text-center text-sm text-[#7A8AA0]">
                        No hay grupos que coincidan con los filtros seleccionados
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
    function dashboardActividades() {
        return {
            selectedPeriodo: '',
            selectedNivel: '',
            selectedGrado: '',
            selectedGrupo: '',
            
            grados: [],
            grupos: [],
            resumen: null,
            
            filtroRapido: 'todos',
            gruposAbiertos: [],
            
            loading: {
                resumen: false,
                grados: false,
                grupos: false,
            },

            get gruposFiltrados() {
                if (!this.resumen || !Array.isArray(this.resumen.grupos)) return [];
                
                let lista = [...this.resumen.grupos];

                // 1. Filtrado rápido
                if (this.filtroRapido !== 'todos') {
                    lista = lista.filter(grupo => grupo.estado === this.filtroRapido);
                }

                // 2. Ordenamiento por jerarquía: PK1, PK2, PK3 -> Primaria 1º-6º -> Secundaria
                return lista.sort((a, b) => {
                    const obtenerScore = (g) => {
                        const gradoTexto = String(g.grado_nombre || '').toLowerCase();
                        const nivelTexto = String(g.nivel_nombre || '').toLowerCase();

                        if (gradoTexto.includes('pk1') || gradoTexto.includes('maternal')) return 10;
                        if (gradoTexto.includes('pk2') || (gradoTexto.includes('1') && nivelTexto.includes('preescolar'))) return 20;
                        if (gradoTexto.includes('pk3') || (gradoTexto.includes('2') && nivelTexto.includes('preescolar'))) return 30;
                        if (gradoTexto.includes('pk4') || (gradoTexto.includes('3') && nivelTexto.includes('preescolar'))) return 40;

                        const ordenDirecto = Number(g.orden ?? g.grado_orden ?? 0);
                        if (nivelTexto.includes('primaria')) {
                            return 100 + (ordenDirecto > 0 ? ordenDirecto : (parseInt(gradoTexto) || 0));
                        }
                        if (nivelTexto.includes('secundaria')) {
                            return 200 + (ordenDirecto > 0 ? ordenDirecto : (parseInt(gradoTexto) || 0));
                        }

                        return 500 + (ordenDirecto || 0);
                    };

                    const scoreA = obtenerScore(a);
                    const scoreB = obtenerScore(b);
                    if (scoreA !== scoreB) return scoreA - scoreB;

                    return String(a.grupo_nombre || a.nombre_grupo || '').localeCompare(String(b.grupo_nombre || b.nombre_grupo || ''));
                });
            },

            async init() {
                const periodos = @json($periodos);
                const periodoAbierto = periodos.find(p => p.estado === 'ABIERTO');
                
                if (periodoAbierto) {
                    this.selectedPeriodo = periodoAbierto.periodo_id;
                } else if (periodos.length > 0) {
                    this.selectedPeriodo = periodos[0].periodo_id;
                }
                
                await this.cargarResumen();
            },

            async nivelChanged() {
                this.selectedGrado = '';
                this.selectedGrupo = '';
                this.grados = [];
                this.grupos = [];
                
                if (this.selectedNivel) {
                    await this.cargarGrados();
                }
                
                await this.cargarResumen();
            },

            async gradoChanged() {
                this.selectedGrupo = '';
                this.grupos = [];
                
                if (this.selectedGrado) {
                    await this.cargarGrupos();
                }
                
                await this.cargarResumen();
            },

            async cargarGrados() {
                if (!this.selectedNivel) {
                    this.grados = [];
                    return;
                }

                this.loading.grados = true;
                try {
                    let url = '';
                    if (this.selectedNivel === 'extracurricular') {
                        url = `{{ route('admin.json.grados.extra') }}`;
                    } else {
                        url = `{{ url('/admin/json/niveles') }}/${this.selectedNivel}/grados`;
                    }

                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
                    this.grados = await res.json();
                } catch (e) {
                    console.error('Error al cargar grados:', e);
                    this.grados = [];
                } finally {
                    this.loading.grados = false;
                }
            },

            async cargarGrupos() {
                if (!this.selectedGrado) {
                    this.grupos = [];
                    return;
                }

                this.loading.grupos = true;
                try {
                    const url = `{{ url('/admin/json/grados') }}/${this.selectedGrado}/grupos`;
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
                    this.grupos = await res.json();
                } catch (e) {
                    console.error('Error al cargar grupos:', e);
                    this.grupos = [];
                } finally {
                    this.loading.grupos = false;
                }
            },

            async cargarResumen() {
                if (!this.selectedPeriodo) {
                    this.resumen = null;
                    return;
                }

                this.loading.resumen = true;
                const params = new URLSearchParams({
                    periodo_id: this.selectedPeriodo,
                });

                if (this.selectedNivel) params.append('nivel_id', this.selectedNivel);
                if (this.selectedGrado) params.append('grado_id', this.selectedGrado);
                if (this.selectedGrupo) params.append('grupo_id', this.selectedGrupo);

                try {
                    const url = `{{ route('admin.actividades.json.resumen') }}?${params.toString()}`;
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
                    this.resumen = await res.json();
                } catch (e) {
                    console.error('Error al cargar resumen:', e);
                    this.resumen = null;
                } finally {
                    this.loading.resumen = false;
                }
            },

            toggleGrupo(grupoId) {
                const index = this.gruposAbiertos.indexOf(grupoId);
                if (index === -1) {
                    this.gruposAbiertos.push(grupoId);
                } else {
                    this.gruposAbiertos.splice(index, 1);
                }
            },

            generarLinkCaptura(grupo, materia) {
                const params = new URLSearchParams({
                    nivel_id: grupo.nivel_id || this.selectedNivel || '',
                    grado_id: grupo.grado_id || this.selectedGrado || '',
                    grupo_id: grupo.grupo_id,
                    materia_id: materia.materia_id,
                    periodo_id: this.selectedPeriodo,
                    autocargar: '1'
                });

                return `{{ route('admin.actividades.captura') }}?${params.toString()}`;
            }
        };
    }
    </script>
</x-app-layout>