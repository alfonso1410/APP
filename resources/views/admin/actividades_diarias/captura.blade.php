<x-app-layout>
    <style>
        [x-cloak]{display:none!important}
        :root{--system-primary:#2E3E5C;--page-bg:#F5F6F8;--border-color:#E2E6EC;--text-primary:#1A2332;--text-secondary:#4A5A78;--text-muted:#7A8AA0}
        .table-scroll::-webkit-scrollbar{height:6px}
        .table-scroll::-webkit-scrollbar-track{background:#f1f4f8}
        .table-scroll::-webkit-scrollbar-thumb{background:#c8ced8;border-radius:3px}
        .table-scroll::-webkit-scrollbar-thumb:hover{background:#a8b0be}
        .criterios-bar{background:#fff;border:1px solid var(--border-color);border-radius:8px;overflow:hidden}
        .criterio-tab{position:relative;width:100%;padding:14px 18px;background:#fff;border-right:1px solid var(--border-color);transition:background .15s;cursor:pointer;text-align:left}
        .criterio-tab:last-child{border-right:none}
        .criterio-tab:hover{background:#F7F9FC}
        .criterio-tab.active{background:#F7F9FC;box-shadow:inset 0 -2px 0 var(--system-primary)}
        .modal-overlay{background:rgba(26,35,50,0.5);backdrop-filter:blur(2px)}
    </style>

    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="text-xl font-semibold text-[#1A2332] tracking-tight">{{ __('Actividades Diarias') }}</h2>
                <p class="text-sm text-[#4A5A78] mt-0.5">Supervisión y consulta de calificaciones</p>
            </div>
        </div>
    </x-slot>

    <div x-data="actividadesAdminManager()" x-cloak class="space-y-5">
        <!-- FILTROS EN CASCADA -->
        <div class="bg-white rounded-md p-4 border border-[#E2E6EC]">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Nivel</label>
                    <select x-model="selectedNivel" @change="nivelChanged()" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]">
                        <option value="">Selecciona nivel</option>
                        @if(isset($niveles))
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->nivel_id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        @endif
                        <option value="extracurricular">Extracurriculares</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Grado</label>
                    <select x-model="selectedGrado" @change="gradoChanged()" :disabled="loading.grados || !selectedNivel" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] disabled:opacity-50">
                        <option value="">Selecciona grado</option>
                        <template x-for="g in grados" :key="g.id"><option :value="g.id" x-text="g.nombre"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Grupo</label>
                    <select x-model="selectedGrupo" @change="grupoChanged()" :disabled="loading.grupos || !selectedGrado" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] disabled:opacity-50">
                        <option value="">Selecciona grupo</option>
                        <template x-for="gr in grupos" :key="gr.id"><option :value="gr.id" x-text="gr.nombre_grupo"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Materia</label>
                    <select x-model="selectedMateria" :disabled="loading.materias || !selectedGrupo" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] disabled:opacity-50">
                        <option value="">Selecciona materia</option>
                        <template x-for="m in materias" :key="m.id"><option :value="m.id" x-text="m.nombre"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Periodo</label>
                    <select x-model="selectedPeriodo" class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 text-[#1A2332] focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]">
                        <option value="">Selecciona periodo</option>
                        @if(isset($periodos))
                            @foreach($periodos as $p)
                                <option value="{{ $p->periodo_id }}" data-estado="{{ $p->estado }}">{{ $p->nombre }} ({{ $p->estado }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#E2E6EC] flex items-center gap-3">
                <button @click="cargarTabla()" :disabled="!selectedGrupo || !selectedMateria || !selectedPeriodo || loading.tabla || guardandoCambios" class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-[#2E3E5C] rounded-md hover:bg-[#23324A] disabled:bg-[#A8B2C4] disabled:cursor-not-allowed transition-colors shadow-sm">
                    <svg x-show="loading.tabla" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="loading.tabla ? 'Cargando datos...' : 'Cargar Actividades'"></span>
                </button>
            </div>
        </div>

        <!-- COMPONENTE TABLA DE LECTURA Y AUDITORÍA PARA ADMIN -->
        <x-actividades.tabla-admin />

        <!-- TOAST NOTIFICACIONES -->
        <x-actividades.toast />
    </div>

    @include('components.actividades.captura-scripts')

    <script>
    function actividadesAdminManager() {
        const base = actividadesDiariasManager();

        return {
            ...base,
            modalInfoActividad: false,
            modalObsLectura: false,
            obsLecturaData: { alumnoNombre: '', actNombre: '', texto: '' },
            infoActividadData: { nombre: '', descripcion: '', valorMaximo: 10, fecha: '' },

            // Sincronizar y recargar datos frescos del backend para actualizar auditoría
            async sincronizar() {
                await base.sincronizar.call(this);
                await this.cargarTabla();
            },

            // Formatea nombre a: Nombre + Primer Apellido
            formatearNombreCorto(nombreCompleto) {
                if (!nombreCompleto || nombreCompleto === '—') return '—';
                const partes = nombreCompleto.trim().split(/\s+/).filter(Boolean);
                if (partes.length === 1) return partes[0];
                return `${partes[0]} ${partes[1]}`;
            },

            get alumnosFiltrados() {
                if (!Array.isArray(this.alumnos)) return [];
                const filtro = this.filtroRapido || 'todos';

                if (filtro === 'todos') {
                    return this.alumnos;
                }

                const actividades = this.getActividadesDelCriterioActivo();
                if (!actividades || actividades.length === 0) {
                    return this.alumnos;
                }

                if (filtro === 'desincronizados') {
                    const actsDesincIds = actividades.filter(a => a && a.desincronizada).map(a => a.id);
                    if (actsDesincIds.length === 0) return [];

                    return this.alumnos.filter(alumno => {
                        if (!alumno || !alumno.calificaciones) return false;
                        return actsDesincIds.some(actId => alumno.calificaciones[actId]?.estado === 'ENTREGADO');
                    });
                }

                if (filtro === 'pendientes') {
                    return this.alumnos.filter(alumno => {
                        if (!alumno) return false;
                        return actividades.some(act => {
                            const cal = alumno.calificaciones?.[act.id];
                            return !cal || (cal.estado === 'ENTREGADO' && (cal.valor === null || cal.valor === undefined || String(cal.valor).trim() === '' || isNaN(Number(cal.valor))));
                        });
                    });
                }

                return this.alumnos;
            },

            mostrarInfoActividad(act) {
                if (!act) return;
                this.infoActividadData = {
                    nombre: act.nombre || act.nombre_actividad || 'Sin título',
                    descripcion: act.descripcion ?? act.descripcion_actividad ?? act.detalle ?? 'Sin descripción detallada.',
                    valorMaximo: act.valorMaximo ?? act.valor_maximo ?? 10,
                    fecha: act.fecha || act.fecha_actividad || '—'
                };
                this.modalInfoActividad = true;
            },

            mostrarObservacion(alumno, act) {
                const cal = this.getCalificacion(alumno, act.id);
                this.obsLecturaData = {
                    alumnoNombre: alumno.nombre || '',
                    actNombre: act.nombre || '',
                    texto: cal.observaciones || 'Sin observación registrada.'
                };
                this.modalObsLectura = true;
            }
        };
    }
    </script>
</x-app-layout>