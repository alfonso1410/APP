<x-app-layout>
    <style>
        [x-cloak]{display:none!important}
        :root{--system-primary:#2E3E5C;--page-bg:#F5F6F8;--border-color:#E2E6EC;--text-primary:#1A2332;--text-secondary:#4A5A78;--text-muted:#7A8AA0}
        .table-scroll::-webkit-scrollbar{height:6px}
        .table-scroll::-webkit-scrollbar-track{background:#f1f4f8}
        .table-scroll::-webkit-scrollbar-thumb{background:#c8ced8;border-radius:3px}
        .criterios-bar{background:#fff;border:1px solid var(--border-color);border-radius:8px;overflow:hidden}
        .criterio-tab{position:relative;width:100%;padding:14px 18px;background:#fff;border-right:1px solid var(--border-color);transition:background .15s;cursor:pointer;text-align:left}
        .criterio-tab:last-child{border-right:none}
        .criterio-tab:hover{background:#F7F9FC}
        .criterio-tab.active{background:#F7F9FC;box-shadow:inset 0 -2px 0 var(--system-primary)}
        .modal-overlay{background:rgba(26,35,50,0.5);backdrop-filter:blur(2px)}
        input.input-error{border-color:#C53030!important;background-color:#FFF5F5!important}
        input.input-success{border-color:#2F855A!important}
        .actividad-item-active{border-left:3px solid var(--system-primary);background:#F7F9FC}

        /* ANULA LA FLECHA NATIVA Y CENTRA LOS SÍMBOLOS ✓, ✗, FJ */
        .select-no-arrow {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: none !important;
            padding: 4px 0px !important;
            text-align-last: center !important;
            line-height: 1.2 !important;
        }
        .select-no-arrow::-ms-expand {
            display: none !important;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3 w-full">
            <div>
                <h2 class="text-xl font-semibold text-[#1A2332] tracking-tight">{{ __('Actividades Diarias') }}</h2>
                <p class="text-sm text-[#4A5A78] mt-0.5">Control formativo y registro de evaluación continua</p>
            </div>
        </div>
    </x-slot>

    <div x-data="actividadesMaestroManager()" x-cloak class="space-y-6">
        <!-- SELECTOR DE PERIODO -->
        <div class="flex justify-end">
            <div class="inline-flex items-center gap-2 bg-[#F8FAFC] pl-3 pr-2 py-1.5 rounded-lg border border-[#D2D7E0] shadow-sm hover:border-[#2E3E5C] transition-all">
                <span class="text-[11px] font-bold text-[#7A8AA0] uppercase tracking-wider">
                    Periodo:
                </span>

                <div class="relative flex items-center">
                    <select
                        x-model="selectedPeriodo"
                        class="text-xs font-bold text-[#1A2332] bg-transparent border-none py-0 pl-1 pr-6 focus:ring-0 cursor-pointer"
                    >
                        @foreach($periodos as $p)
                            <option value="{{ $p->periodo_id }}" class="bg-white text-slate-800 py-1">
                                {{ $p->nombre }} ({{ $p->estado }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- FASE 1: DASHBOARD -->
        <div x-show="!modoCaptura" class="space-y-6">
            <!-- PÍLDORAS DE GRUPOS ORDENADAS (PK1, PK2, PK3, 1º A 6º) -->
            <div class="bg-white rounded-lg border border-[#E2E6EC] p-4 shadow-sm space-y-3">
                <span class="block text-xs font-bold uppercase tracking-wider text-[#7A8AA0]">Mis Grupos Asignados:</span>
                <div class="flex flex-wrap gap-2">
                    <template x-for="grupo in misGruposOrdenados" :key="grupo.grupo_id">
                        <button 
                            type="button"
                            @click="seleccionarGrupoTab(grupo)"
                            class="px-4 py-2 rounded-md text-xs font-bold transition-all border"
                            :class="grupoSeleccionado?.grupo_id === grupo.grupo_id 
                                ? 'bg-[#2E3E5C] text-white border-[#2E3E5C] shadow-sm ring-2 ring-[#2E3E5C]/20' 
                                : 'bg-[#F7F9FC] text-[#4A5A78] border-[#E2E6EC] hover:bg-slate-100 hover:text-[#1A2332]'"
                        >
                            <span x-text="`${grupo.grado_nombre} · ${grupo.nombre_grupo}`"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-[#1A2332]" x-text="grupoSeleccionado ? `${grupoSeleccionado.nivel_nombre} · ${grupoSeleccionado.grado_nombre} - ${grupoSeleccionado.nombre_grupo}` : 'Selecciona un grupo'"></h3>
                    <p class="text-xs text-[#7A8AA0]">Selecciona una materia para registrar o calificar actividades</p>
                </div>
            </div>

            <div x-show="loading.materiasCards" class="bg-white rounded-lg border border-[#E2E6EC] p-10 text-center">
                <svg class="animate-spin h-7 w-7 text-[#2E3E5C] mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <p class="text-xs text-[#7A8AA0] font-medium">Actualizando materias...</p>
            </div>

            <div x-show="!loading.materiasCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="materia in materiasCards" :key="materia.materia_id">
                    <div class="bg-white border rounded-lg p-5 hover:border-[#2E3E5C] hover:shadow-md transition-all flex flex-col justify-between"
                         :class="materia.estado === 'desincronizado' ? 'border-amber-300' : 'border-[#E2E6EC]'">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <h4 class="font-bold text-base text-[#1A2332] leading-snug" x-text="materia.nombre"></h4>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded flex-shrink-0" :class="{
                                    'bg-emerald-100 text-emerald-800': materia.estado === 'al_dia',
                                    'bg-amber-100 text-amber-800': materia.estado === 'desincronizado',
                                    'bg-gray-100 text-gray-500': materia.estado === 'sin_actividades'
                                }" x-text="materia.estado === 'al_dia' ? '✓ Al día' : materia.estado === 'desincronizado' ? '⚠ Pendiente' : 'Sin acts'"></span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 py-3 border-y border-[#E2E6EC] bg-[#F7F9FC] rounded-md px-3 mb-3">
                                <div>
                                    <span class="block text-[10px] text-[#7A8AA0] uppercase font-semibold">Criterios:</span>
                                    <span class="text-sm font-bold text-[#1A2332]" x-text="`${materia.total_criterios} asignados`"></span>
                                </div>
                                <div>
                                    <span class="block text-[10px] text-[#7A8AA0] uppercase font-semibold">Actividades:</span>
                                    <span class="text-sm font-bold text-[#1A2332]" x-text="`${materia.total_actividades} creadas`"></span>
                                </div>
                            </div>

                            <div class="text-xs text-[#7A8AA0] flex items-center justify-between">
                                <span>Última modif:</span>
                                <strong class="text-[#4A5A78]" x-text="materia.ultima_actividad"></strong>
                            </div>
                        </div>

                        <div class="pt-4 mt-3 border-t border-[#E2E6EC]">
                            <button 
                                type="button" 
                                @click="abrirCapturaMateria(materia)" 
                                class="w-full py-2 px-4 bg-[#2E3E5C] hover:bg-[#23324A] text-white rounded-md text-xs font-bold flex items-center justify-center gap-1.5 transition-colors shadow-sm"
                            >
                                <span>Abrir Captura</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- FASE 2: CAPTURA -->
        <div x-show="modoCaptura" class="space-y-5">
            <div x-show="periodoCerrado && vistaTipo === 'enfocada'" x-transition class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg"> 
                <div class="flex-shrink-0"> 
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.007M10.29 3.86l-7.5 13A2 2 0 004.5 20h15a2 2 0 001.71-3.14l-7.5-13a2 2 0 00-3.42 0z" /></svg> 
                </div> 
                <div> 
                    <p class="text-sm font-bold text-red-800">Periodo cerrado</p> 
                    <p class="text-xs text-red-700 mt-0.5">Este periodo se encuentra cerrado. Las calificaciones y actividades están en modo lectura.</p> 
                </div> 
            </div>

            <!-- HEADER CONTEXTO -->
            <div class="bg-white rounded-lg p-4 border border-[#E2E6EC] flex flex-wrap items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="volverAlDashboard()" class="p-2 bg-[#F7F9FC] border border-[#E2E6EC] rounded-lg text-[#4A5A78] hover:text-[#1A2332] hover:bg-[#E8ECF1] transition-colors" title="Cambiar de materia">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-[#7A8AA0]">
                            <span x-text="contexto.nivelNombre"></span>
                            <span>·</span>
                            <span x-text="contexto.gradoNombre"></span>
                            <span>·</span>
                            <span class="text-[#1A2332] font-bold" x-text="contexto.grupoNombre"></span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <template x-if="materiasDelGrupoActual.length <= 1">
                                <h3 class="text-base font-bold text-[#1A2332]" x-text="contexto.materiaNombre"></h3>
                            </template>

                            <template x-if="materiasDelGrupoActual.length > 1">
                                <div class="inline-flex items-center bg-[#F8FAFC] px-3 py-1 rounded-lg border border-[#D2D7E0] shadow-sm hover:border-[#2E3E5C] transition-all">
                                    <select 
                                        @change="cambiarMateriaEnContexto($event.target.value)" 
                                        class="text-xs font-bold text-[#1A2332] bg-transparent border-none py-0 pl-0 pr-6 cursor-pointer focus:ring-0"
                                    >
                                        <template x-for="m in materiasDelGrupoActual" :key="m.materia_id">
                                            <option :value="m.materia_id" :selected="m.materia_id == selectedMateria" x-text="m.nombre" class="bg-white text-slate-800 py-1"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- TOGGLE VISTAS -->
                <div class="flex items-center gap-2">
                    <div class="bg-[#F7F9FC] p-1 border border-[#E2E6EC] rounded-lg flex items-center text-xs font-semibold">
                        <button @click="vistaTipo = 'enfocada'" :class="vistaTipo === 'enfocada' ? 'bg-white shadow-sm text-[#1A2332]' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-2.5 py-1 rounded transition-all">
                            Acts. Diarias
                        </button>
                        <button @click="vistaTipo = 'matriz'" :class="vistaTipo === 'matriz' ? 'bg-white shadow-sm text-[#1A2332]' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-2.5 py-1 rounded transition-all">
                            Tabla Completa
                        </button>
                    </div>

                    <button @click="cargarTabla()" :disabled="loading.tabla" class="p-2 text-[#4A5A78] hover:text-[#1A2332] bg-[#F7F9FC] border border-[#E2E6EC] rounded-lg transition-colors" title="Recargar">
                        <svg class="w-4 h-4" :class="loading.tabla ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>

            <!-- SPINNER -->
            <div x-show="loading.tabla" class="bg-white rounded-md p-10 border border-[#E2E6EC] text-center">
                <svg class="animate-spin h-8 w-8 text-[#2E3E5C] mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <p class="text-xs text-[#7A8AA0] font-medium">Cargando actividades y calificaciones...</p>
            </div>

            <!-- CONTENIDO DE CAPTURA -->
            <div x-show="!loading.tabla && tablaCargada" class="space-y-5">
                <!-- VISTA ACTS. DIARIAS -->
                <div x-show="vistaTipo === 'enfocada'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button 
                                x-show="cambiosPendientes.length > 0"
                                @click="flushCambiosPendientes()" 
                                :disabled="guardandoCambios || periodoCerrado"
                                class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-white rounded-md transition-colors shadow-sm disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
                                style="background:var(--system-primary,#2E3E5C)"
                            >
                                <svg x-show="guardandoCambios" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="guardandoCambios ? 'Guardando...' : ` Guardar Cambios (${cambiosPendientes.length})`"></span>
                            </button>

                            <button 
                                @click="sincronizar()" 
                                :disabled="periodoCerrado || guardando || guardandoCambios" 
                                class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition-colors shadow-sm"
                            >
                                <svg x-show="!guardando" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span x-text="guardando ? 'Sincronizando...' : 'Actualizar'"></span>
                            </button>
                        </div>

                        <button 
                            @click="abrirModalNuevaActividad()" 
                            :disabled="periodoCerrado"  
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 rounded-md disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition-colors shadow-sm"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Nueva actividad</span>
                        </button>
                    </div>

                    <!-- PESTAÑAS CRITERIOS -->
                    <div class="criterios-bar grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4">
                        <template x-for="criterio in criterios" :key="criterio.id">
                            <button type="button" @click="cambiarCriterioEnfocado(criterio.id)" class="criterio-tab" :class="{ 'active': criterioActivo === criterio.id }">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-[#1A2332]" x-text="criterio.nombre"></span>
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded" :class="{
                                        'bg-emerald-100 text-emerald-800': criterio.estado === 'al_dia',
                                        'bg-amber-100 text-amber-800': criterio.estado === 'desincronizado',
                                        'bg-gray-100 text-gray-500': criterio.estado === 'sin_actividades'
                                    }" x-text="criterio.estado === 'al_dia' ? 'Al día' : criterio.estado === 'desincronizado' ? 'Pendiente' : 'Sin acts'"></span>
                                </div>
                                <div class="text-[11px] text-[#7A8AA0] mt-1" x-text="`${criterio.peso} · ${criterio.actividadesRegistradas} acts`"></div>
                            </button>
                        </template>
                    </div>

                    <!-- SPLIT ACTIVIDADES / TABLA -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                        <!-- LISTA LATERAL CON DESCRIPCIÓN AMPLIADA A 70 CARACTERES -->
                        <div class="md:col-span-4 bg-white rounded-md border border-[#E2E6EC] overflow-hidden shadow-sm">
                            <div class="p-3 bg-[#F7F9FC] border-b border-[#E2E6EC] flex items-center justify-between">
                                <span class="text-xs font-bold text-[#1A2332]">Actividades del Criterio</span>
                                <span class="text-[11px] text-[#7A8AA0]" x-text="`${getActividadesDelCriterioActivo().length} total`"></span>
                            </div>

                            <div class="divide-y divide-[#E2E6EC] max-h-[550px] overflow-y-auto">
                                <template x-for="act in getActividadesDelCriterioActivo()" :key="act.id">
                                    <div 
                                        @click="actividadEnfocadaId = act.id"
                                        class="p-3 cursor-pointer transition-all hover:bg-slate-50 flex items-start justify-between gap-2"
                                        :class="actividadEnfocadaId === act.id ? 'actividad-item-active' : ''"
                                    >
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="actividadEnfocadaId === act.id ? 'bg-[#2E3E5C]' : 'bg-transparent border border-[#7A8AA0]'"></span>
                                                <h5 class="text-xs font-bold text-[#1A2332] truncate" x-text="act.nombre"></h5>
                                            </div>

                                            <!-- DESCRIPCIÓN RECORTADA A 70 CARACTERES -->
                                            <template x-if="act.descripcion || act.descripcion_actividad || act.detalle">
                                                <p class="text-[11px] text-[#4A5A78] mt-0.5 ml-3 line-clamp-2" x-text="(act.descripcion || act.descripcion_actividad || act.detalle).length > 70 ? (act.descripcion || act.descripcion_actividad || act.detalle).substring(0, 70) + '...' : (act.descripcion || act.descripcion_actividad || act.detalle)" :title="act.descripcion || act.descripcion_actividad || act.detalle"></p>
                                            </template>

                                            <div class="text-[10px] text-[#7A8AA0] mt-0.5 ml-3">
                                                Máx: <strong x-text="act.valorMaximo"></strong> pts · <span x-text="act.fecha"></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1" x-show="!periodoCerrado">
                                            <button type="button" @click.stop="abrirModalEditarActividad(act)" class="text-[#7A8AA0] hover:text-[#2E3E5C] p-1" title="Editar">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" @click.stop="confirmarEliminarActividad(act)" class="text-[#7A8AA0] hover:text-[#C53030] p-1" title="Eliminar">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="getActividadesDelCriterioActivo().length === 0">
                                    <div class="p-6 text-center text-xs text-[#7A8AA0]">
                                        No hay actividades en este criterio.
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- TABLA EVALUACIÓN DIRECTA -->
                        <div class="md:col-span-8 bg-white rounded-md border border-[#E2E6EC] overflow-hidden shadow-sm">
                            <template x-if="getActividadEnfocada()">
                                <div>
                                    <div class="px-4 py-3 bg-[#F7F9FC] border-b border-[#E2E6EC] flex items-center justify-between">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-sm text-[#1A2332]" x-text="getActividadEnfocada().nombre"></h4>
                                                
                                                <!-- BOTÓN DE INFORMACIÓN QUE ABRE TARJETITA MODAL -->
                                                <template x-if="getActividadEnfocada().descripcion || getActividadEnfocada().descripcion_actividad || getActividadEnfocada().detalle">
                                                    <button type="button" @click="mostrarInfoActividad(getActividadEnfocada())" class="text-[#7A8AA0] hover:text-[#2E3E5C] transition-colors" title="Ver descripción completa">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </button>
                                                </template>
                                            </div>
                                            <p class="text-[11px] text-[#7A8AA0]">
                                                Valor máximo: <strong class="text-[#1A2332]" x-text="getActividadEnfocada().valorMaximo"></strong> puntos · Aplicada: <span x-text="getActividadEnfocada().fecha"></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto max-h-[500px]">
                                        <table class="w-full text-left text-sm border-collapse">
                                            <thead class="bg-[#F7F9FC] text-[#4A5A78] text-xs uppercase border-b border-[#E2E6EC] sticky top-0 z-10">
                                                <tr>
                                                    <th class="py-2.5 px-3 w-10 text-center">#</th>
                                                    <th class="py-2.5 px-3">Alumno</th>
                                                    <th class="py-2.5 px-3 text-center w-20">Estado</th>
                                                    <th class="py-2.5 px-3 text-center w-24">Calificación</th>
                                                    <th class="py-2.5 px-3 text-center w-24">Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-[#E2E6EC]">
                                                <template x-for="(alumno, idx) in alumnosFiltrados" :key="alumno.id">
                                                    <tr class="hover:bg-[#F7F9FC] transition-colors">
                                                        <td class="py-2 px-3 text-center text-xs text-[#7A8AA0] font-mono" x-text="idx + 1"></td>
                                                        <td class="py-2 px-3 font-medium text-xs text-[#1A2332] whitespace-nowrap" x-text="alumno.nombre"></td>
                                                        
                                                        <!-- SELECTOR REACTIVO Y VINCULADO AL ESTADO -->
                                                        <td class="py-2 px-3 text-center">
                                                            <div class="inline-flex items-center justify-center">
                                                                <select 
                                                                    x-model="getCalificacion(alumno, getActividadEnfocada().id).estado" 
                                                                    @change="onEstadoCambiado(alumno, getActividadEnfocada())"
                                                                    :disabled="periodoCerrado"
                                                                    class="select-no-arrow w-9 text-xs font-bold rounded-md border bg-white cursor-pointer focus:ring-1 focus:ring-[#2E3E5C] disabled:bg-gray-100 disabled:text-gray-400 transition-colors"
                                                                    :class="{
                                                                        'text-[#2F855A] border-emerald-300 bg-emerald-50/50': getCalificacion(alumno, getActividadEnfocada().id).estado === 'ENTREGADO',
                                                                        'text-[#C53030] border-red-300 bg-red-50/50': getCalificacion(alumno, getActividadEnfocada().id).estado === 'NO_ENTREGO',
                                                                        'text-[#2E3E5C] border-slate-300 bg-slate-50/50': getCalificacion(alumno, getActividadEnfocada().id).estado === 'FALTA_JUSTIFICADA'
                                                                    }"
                                                                >
                                                                    <option value="ENTREGADO" class="text-emerald-700 bg-white font-bold">✓</option>
                                                                    <option value="NO_ENTREGO" class="text-red-700 bg-white font-bold">✗</option>
                                                                    <option value="FALTA_JUSTIFICADA" class="text-slate-700 bg-white font-bold">FJ</option>
                                                                </select>
                                                            </div>
                                                        </td>

                                                        <!-- INPUT REACTIVO CON VINCULACIÓN DIRECTA -->
                                                        <td class="py-2 px-3 text-center">
                                                            <div x-show="getCalificacion(alumno, getActividadEnfocada().id).estado === 'ENTREGADO'">
                                                                <input 
                                                                    type="number" 
                                                                    step="0.1" 
                                                                    min="0" 
                                                                    :max="getActividadEnfocada().valorMaximo" 
                                                                    x-model.number="getCalificacion(alumno, getActividadEnfocada().id).valor" 
                                                                    @input="validarRango($event, getActividadEnfocada().valorMaximo); autoGuardarDebounced(alumno, getActividadEnfocada())" 
                                                                    :disabled="periodoCerrado" 
                                                                    class="w-16 text-center bg-[#F7F9FC] border border-[#E2E6EC] rounded py-1 font-bold text-[#1A2332] text-xs focus:bg-white focus:ring-1 focus:ring-[#2E3E5C] disabled:bg-gray-100 disabled:text-gray-400"
                                                                    :class="getInputClass(getCalificacion(alumno, getActividadEnfocada().id), getActividadEnfocada().valorMaximo)"
                                                                >
                                                            </div>
                                                            <div x-show="getCalificacion(alumno, getActividadEnfocada().id).estado !== 'ENTREGADO'">
                                                                <span class="text-xs font-bold" :class="getCalificacion(alumno, getActividadEnfocada().id).estado === 'NO_ENTREGO' ? 'text-red-600' : 'text-gray-400'" x-text="getCalificacion(alumno, getActividadEnfocada().id).estado === 'NO_ENTREGO' ? '0' : 'FJ'"></span>
                                                            </div>
                                                        </td>

                                                        <!-- COLUMNA OBSERVACIÓN -->
                                                        <td class="py-2 px-3 text-center">
                                                            <button 
                                                                type="button" 
                                                                @click="!periodoCerrado && abrirObservacion(alumno, getActividadEnfocada())" 
                                                                :disabled="periodoCerrado"
                                                                class="p-1.5 rounded-md text-[#7A8AA0] hover:text-[#2E3E5C] hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed disabled:pointer-events-none transition-colors" 
                                                                :class="getCalificacion(alumno, getActividadEnfocada().id).observaciones ? 'text-[#2E3E5C] bg-slate-100' : ''"
                                                                title="Observación"
                                                            >
                                                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!getActividadEnfocada()">
                                <div class="p-12 text-center text-xs text-[#7A8AA0]">
                                    Selecciona una actividad a la izquierda para capturar calificaciones.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- VISTA TABLA COMPLETA -->
                <div x-show="vistaTipo === 'matriz'" class="pt-3">
                    <x-actividades.tabla-resumen :mostrar-boton-sincronizar="true" />
                </div>
            </div>
        </div>

        <!-- TARJETA MODAL DE INFORMACIÓN DE ACTIVIDAD -->
        <div x-show="modalInfoActividad" x-cloak class="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4">
            <div @click.away="modalInfoActividad = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-[#E2E6EC] transform transition-all">
                <div class="px-5 py-3.5 bg-[#F7F9FC] border-b border-[#E2E6EC] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="p-1 bg-[#2E3E5C]/10 text-[#2E3E5C] rounded-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <h4 class="font-bold text-sm text-[#1A2332]">Detalles de la Actividad</h4>
                    </div>
                    <button @click="modalInfoActividad = false" class="text-[#7A8AA0] hover:text-[#1A2332] text-xl font-bold">&times;</button>
                </div>

                <div class="p-5 space-y-3.5 text-xs">
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-[#7A8AA0]">Actividad</span>
                        <p class="font-bold text-sm text-[#1A2332] mt-0.5" x-text="infoActividadData.nombre"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 py-2.5 px-3 bg-[#F8FAFC] border border-[#E2E6EC] rounded-lg">
                        <div>
                            <span class="block text-[10px] text-[#7A8AA0] uppercase font-semibold">Valor Máximo</span>
                            <span class="text-xs font-bold text-[#1A2332]" x-text="`${infoActividadData.valorMaximo} puntos`"></span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-[#7A8AA0] uppercase font-semibold">Fecha de Aplicación</span>
                            <span class="text-xs font-bold text-[#1A2332]" x-text="infoActividadData.fecha"></span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-[#7A8AA0] mb-1">Descripción / Instrucciones</span>
                        <div class="p-3 bg-[#F8FAFC] border border-[#E2E6EC] rounded-lg text-slate-700 leading-relaxed max-h-48 overflow-y-auto whitespace-pre-line text-xs" x-text="infoActividadData.descripcion || 'Sin descripción detallada.'"></div>
                    </div>
                </div>

                <div class="px-5 py-3 bg-[#F7F9FC] border-t border-[#E2E6EC] flex justify-end">
                    <button type="button" @click="modalInfoActividad = false" class="px-4 py-1.5 text-xs font-bold text-white bg-[#2E3E5C] hover:bg-[#23324A] rounded-md transition-colors shadow-sm">
                        Entendido
                    </button>
                </div>
            </div>
        </div>

        <x-actividades.modal-nueva-actividad :mostrar-grupos-replicables="true" />
        <x-actividades.modal-editar-actividad />
        <x-actividades.modal-observaciones />
        <x-actividades.toast />
    </div>

    @include('components.actividades.captura-scripts', [
        'rutaTabla'              => route('admin.actividades.json.tabla'),
        'rutaStore'              => route('admin.actividades.store'),
        'rutaUpdateBase'         => url('/admin/actividades-diarias/actividades'),
        'rutaDeleteBase'         => url('/admin/actividades-diarias/actividades'),
        'rutaGuardarCalificacion'=> route('admin.actividades.guardarCalificacion'),
        'rutaSincronizar'        => route('admin.actividades.sincronizar'),
        'rutaGruposReplicables'  => route('admin.actividades.json.gruposReplicables'),
    ])

    <script>
    function actividadesMaestroManager() {
        const base = actividadesDiariasManager();

        return {
            ...base,
            misGruposRaw: @json($misGrupos),
            grupoSeleccionado: null,
            materiasCards: [],
            modoCaptura: false,
            vistaTipo: 'enfocada',
            actividadEnfocadaId: null,
            modalInfoActividad: false,
            infoActividadData: { nombre: '', descripcion: '', valorMaximo: 10, fecha: '' },
            loading: { ...base.loading, materiasCards: false },
            contexto: {
                grupoId: null, grupoNombre: '', gradoNombre: '',
                nivelNombre: '', materiaId: null, materiaNombre: '',
            },

            // ORDENAMIENTO ESTRICTO: PK1 -> PK2 -> PK3 -> 1º a 6º
            get misGruposOrdenados() {
                if (!Array.isArray(this.misGruposRaw)) return [];
                
                return [...this.misGruposRaw].sort((a, b) => {
                    const obtenerScore = (g) => {
                        const gradoTexto = String(g.grado_nombre || '').toLowerCase();
                        const nivelTexto = String(g.nivel_nombre || '').toLowerCase();

                        // 1. Detección directa por nombres específicos
                        if (gradoTexto.includes('pk1') || gradoTexto.includes('maternal')) return 10;
                        if (gradoTexto.includes('pk2') || (gradoTexto.includes('1') && nivelTexto.includes('preescolar'))) return 20;
                        if (gradoTexto.includes('pk3') || (gradoTexto.includes('2') && nivelTexto.includes('preescolar'))) return 30;
                        if (gradoTexto.includes('pk4') || (gradoTexto.includes('3') && nivelTexto.includes('preescolar'))) return 40;

                        // 2. Primaria usando columna orden o número de grado
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

                    return String(a.nombre_grupo || '').localeCompare(String(b.nombre_grupo || ''));
                });
            },

            mostrarInfoActividad(act) {
                if (!act) return;
                this.infoActividadData = {
                    nombre: act.nombre || act.nombre_actividad || 'Sin título',
                    descripcion: act.descripcion ?? act.descripcion_actividad ?? act.detalle ?? 'Sin descripción.',
                    valorMaximo: act.valorMaximo ?? act.valor_maximo ?? 10,
                    fecha: act.fecha || act.fecha_actividad || '—'
                };
                this.modalInfoActividad = true;
            },

            // REACTIVIDAD SELECTOR ✓, ✗, FJ -> INPUT
            onEstadoCambiado(alumno, act) {
                const cal = this.getCalificacion(alumno, act.id);
                if (cal.estado === 'NO_ENTREGO') {
                    cal.valor = 0;
                } else if (cal.estado === 'FALTA_JUSTIFICADA') {
                    cal.valor = null;
                } else if (cal.estado === 'ENTREGADO' && (cal.valor === 0 || cal.valor === null)) {
                    cal.valor = null;
                }
                this.autoGuardarDebounced(alumno, act);
            },

            abrirModalEditarActividad(act) {
                if (!act || !act.id) return;
                this.formEditarActividad = {
                    id: act.id,
                    nombre_actividad: act.nombre || act.nombre_actividad || '',
                    descripcion: act.descripcion ?? act.descripcion_actividad ?? act.detalle ?? '',
                    materia_criterio_id: act.criterio_id || act.materia_criterio_id || '',
                    valor_maximo: act.valorMaximo ?? act.valor_maximo ?? 10,
                    fecha_actividad: act.fecha_raw || act.fecha_actividad || (act.fecha && act.fecha.includes('-') ? act.fecha : new Date().toISOString().split('T')[0])
                };
                this.modalEditarActividad = true;
            },

            async guardarEdicionActividad() {
                if (!this.formEditarActividad.id) {
                    alert('Error: ID de actividad no válido.');
                    return;
                }

                this.guardandoActividad = true;
                try {
                    const payload = {
                        id: this.formEditarActividad.id,
                        nombre_actividad: this.formEditarActividad.nombre_actividad,
                        descripcion: this.formEditarActividad.descripcion || '',
                        materia_criterio_id: this.formEditarActividad.materia_criterio_id,
                        valor_maximo: this.formEditarActividad.valor_maximo,
                        fecha_actividad: this.formEditarActividad.fecha_actividad
                    };

                    const res = await fetch(`${this.rutas.updateBase}/${this.formEditarActividad.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        this.modalEditarActividad = false;
                        this.mostrarToast('Actividad actualizada correctamente');
                        if (typeof this.cargarTablaYAutoenfocar === 'function') {
                            await this.cargarTablaYAutoenfocar();
                        } else {
                            await this.cargarTabla();
                        }
                    } else {
                        if (data.errors) {
                            const mensajes = Object.values(data.errors).flat().join('\n');
                            alert(`Errores de validación:\n${mensajes}`);
                        } else {
                            alert(data.mensaje || 'Error al actualizar la actividad.');
                        }
                    }
                } catch (e) {
                    console.error('Error al actualizar la actividad:', e);
                    alert('Error inesperado al actualizar la actividad.');
                } finally {
                    this.guardandoActividad = false;
                }
            },

            get alumnosFiltrados() {
                if (!Array.isArray(this.alumnos)) return [];
                const filtro = this.filtroRapido || 'todos';

                if (filtro === 'todos') {
                    return this.alumnos;
                }

                let actividades = [];
                if (this.vistaTipo === 'matriz') {
                    actividades = this.getActividadesDelCriterioActivo();
                } else {
                    const enfocada = this.getActividadEnfocada();
                    actividades = enfocada ? [enfocada] : [];
                }

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

            abrirObservacion(alumno, act) {
                if (this.periodoCerrado) return;
                base.abrirObservacion.call(this, alumno, act);
            },

            guardarObservacionModal() {
                if (this.periodoCerrado) return;
                base.guardarObservacionModal.call(this);
            },

            get materiasDelGrupoActual() {
                const grupo = this.misGruposRaw.find(g => g.grupo_id == this.selectedGrupo);
                return grupo ? grupo.materias : [];
            },

            async init() {
                const periodos = @json($periodos);
                const params = new URLSearchParams(window.location.search);
                const urlPeriodo = params.get('periodo_id');
                
                let periodoInicial = null;
                if (urlPeriodo) {
                    periodoInicial = periodos.find(p => String(p.periodo_id) === String(urlPeriodo));
                }
                if (!periodoInicial) {
                    periodoInicial = periodos.find(p => p.estado === 'ABIERTO') || periodos[0];
                }

                if (periodoInicial) {
                    this.selectedPeriodo = String(periodoInicial.periodo_id);
                    this.periodoCerrado = periodoInicial.estado === 'CERRADO';
                }

                this.$watch('selectedPeriodo', async (nuevoValor, anteriorValor) => {
                    if (!nuevoValor || nuevoValor === anteriorValor) return;

                    const pSel = periodos.find(p => String(p.periodo_id) === String(nuevoValor));
                    this.periodoCerrado = pSel ? pSel.estado === 'CERRADO' : false;

                    if (!this.modoCaptura) {
                        await this.cargarTarjetasMaterias();
                    } else {
                        await this.cargarTablaYAutoenfocar();
                    }

                    this.mostrarToast(`📅 ${pSel ? pSel.nombre : 'Periodo'} (${pSel?.estado || '?'})`);
                });

                const gruposOrdenados = this.misGruposOrdenados;
                if (gruposOrdenados && gruposOrdenados.length > 0) {
                    await this.seleccionarGrupoTab(gruposOrdenados[0]);
                }
            },

            async seleccionarGrupoTab(grupo) {
                this.grupoSeleccionado = grupo;
                this.selectedGrupo = grupo.grupo_id;
                this.selectedGrado = grupo.grado_id || null;
                await this.cargarTarjetasMaterias();
            },

            async cargarTarjetasMaterias() {
                if (!this.selectedGrupo || !this.selectedPeriodo) return;
                this.loading.materiasCards = true;
                try {
                    const url = `{{ route('admin.actividades.json.maestro.materiasResumen') }}?grupo_id=${this.selectedGrupo}&periodo_id=${this.selectedPeriodo}`;
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) this.materiasCards = await res.json();
                } catch (e) {
                    console.error('Error cargando tarjetas:', e);
                    this.materiasCards = [];
                } finally {
                    this.loading.materiasCards = false;
                }
            },

            abrirCapturaMateria(materia) {
                this.selectedMateria = materia.materia_id;
                this.contexto = {
                    grupoId: this.grupoSeleccionado.grupo_id,
                    grupoNombre: this.grupoSeleccionado.nombre_grupo,
                    gradoNombre: this.grupoSeleccionado.grado_nombre,
                    gradoId: this.grupoSeleccionado.grado_id || null,
                    nivelNombre: this.grupoSeleccionado.nivel_nombre,
                    materiaId: materia.materia_id,
                    materiaNombre: materia.nombre,
                };
                this.modoCaptura = true;
                this.cargarTablaYAutoenfocar();
            },

            cambiarMateriaEnContexto(materiaId) {
                this.selectedMateria = materiaId;
                const mat = this.materiasDelGrupoActual.find(m => m.materia_id == materiaId);
                if (mat) {
                    this.contexto.materiaNombre = mat.nombre;
                    this.contexto.materiaId = mat.materia_id;
                }
                this.cargarTablaYAutoenfocar();
            },

            async volverAlDashboard() {
                this.modoCaptura = false;
                this.tablaCargada = false;
                await this.cargarTarjetasMaterias();
            },

            async cargarTablaYAutoenfocar() {
                await this.cargarTabla();
                this.autoenfocarPrimeraActividad();
            },

            cambiarCriterioEnfocado(criterioId) {
                this.criterioActivo = criterioId;
                this.autoenfocarPrimeraActividad();
            },

            autoenfocarPrimeraActividad() {
                const acts = this.getActividadesDelCriterioActivo();
                this.actividadEnfocadaId = acts.length > 0 ? acts[0].id : null;
            },

            getActividadEnfocada() {
                return this.actividades.find(a => a.id === this.actividadEnfocadaId) || null;
            },
        };
    }
    </script>
</x-app-layout>