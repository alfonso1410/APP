{{-- resources/views/components/actividades/tabla-admin.blade.php --}}
<div x-show="tablaCargada" class="space-y-4">
    <!-- BARRA DE ACCIONES SUPERIOR -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button 
                type="button"
                @click="sincronizar()" 
                :disabled="guardando" 
                class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition-colors shadow-sm"
            >
                <svg x-show="!guardando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg x-show="guardando" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="guardando ? 'Actualizando...' : 'Actualizar'"></span>
            </button>

            <!-- ALERTA DE SINCRONIZACIÓN -->
            <template x-if="hayDesincronizados">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-800 bg-amber-50 border border-amber-200 rounded-md">
                    <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Hay calificaciones modificadas sin sincronizar en boleta</span>
                </span>
            </template>
        </div>

        <!-- FILTRO RÁPIDO -->
        <div class="flex items-center gap-1 text-xs">
            <span class="text-[#7A8AA0] mr-1">Filtrar:</span>
            <button type="button" @click="filtroRapido = 'todos'" :class="filtroRapido === 'todos' ? 'bg-white text-[#1A2332] shadow-sm font-bold border border-[#E2E6EC]' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-2.5 py-1 rounded transition-all">Todos</button>
            <button type="button" @click="filtroRapido = 'desincronizados'" :class="filtroRapido === 'desincronizados' ? 'bg-white text-[#B7791F] shadow-sm font-bold border border-[#E2E6EC]' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-2.5 py-1 rounded transition-all">Desincronizados</button>
            <button type="button" @click="filtroRapido = 'pendientes'" :class="filtroRapido === 'pendientes' ? 'bg-white text-[#2E3E5C] shadow-sm font-bold border border-[#E2E6EC]' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-2.5 py-1 rounded transition-all">Pendientes</button>
        </div>
    </div>

    <!-- CRITERIOS TABS -->
    <div class="criterios-bar grid grid-cols-1 md:grid-cols-4">
        <template x-for="criterio in criterios" :key="criterio.id">
            <button type="button" @click="criterioActivo = criterio.id" class="criterio-tab" :class="{ 'active': criterioActivo === criterio.id }">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold text-[#1A2332]" x-text="criterio.nombre"></div>
                        <div class="text-[11px] text-[#7A8AA0] mt-0.5" x-text="criterio.peso + ' de la evaluación'"></div>
                    </div>
                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded whitespace-nowrap" :class="{
                        'bg-emerald-100 text-emerald-800': criterio.estado === 'al_dia',
                        'bg-amber-100 text-amber-800': criterio.estado === 'desincronizado',
                        'bg-gray-100 text-gray-500': criterio.estado === 'sin_actividades'
                    }" x-text="criterio.estado === 'al_dia' ? 'Al día' : criterio.estado === 'desincronizado' ? 'Pendiente' : 'Sin acts'"></span>
                </div>
                <div class="mt-2 text-[11px] text-[#7A8AA0]" x-text="criterio.actividadesRegistradas + ' actividades'"></div>
            </button>
        </template>
    </div>

    <!-- TABLA DE VISUALIZACIÓN -->
    <div class="bg-white rounded-md border border-[#E2E6EC] overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-[#E2E6EC] flex flex-wrap items-center justify-between gap-3 bg-[#F7F9FC]">
            <div>
                <h3 class="font-bold text-sm text-[#1A2332] flex items-center gap-2">
                    <span x-text="getCriterioActivo().nombre"></span>
                    <span class="text-xs font-semibold text-[#7A8AA0] bg-[#E8ECF1] px-2 py-0.5 rounded" x-text="getCriterioActivo().peso"></span>
                </h3>
                <p class="text-xs text-[#7A8AA0] mt-0.5">Modo supervisión: Visualización general de actividades y calificaciones</p>
            </div>
            
            <!-- ACTUALIZADO POR (NOMBRE, PRIMER APELLIDO Y FECHA) -->
            <div class="flex items-center gap-2">
                <template x-if="getCriterioActivo().estado === 'al_dia'">
                    <div class="text-xs text-[#4A5A78] bg-white border border-[#E2E6EC] px-3 py-1.5 rounded-md flex items-center gap-1.5 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2F855A]"></span>
                        <span>Actualizado: <strong x-text="getCriterioActivo().calculadoEn"></strong> por <strong x-text="formatearNombreCorto(getCriterioActivo().calculadoPor)"></strong></span>
                    </div>
                </template>
                <template x-if="getCriterioActivo().estado === 'desincronizado'">
                    <div class="text-xs text-[#B7791F] bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-md flex items-center gap-1.5 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#B7791F]"></span>
                        <span>Actualizado: <span x-text="getCriterioActivo().calculadoEn"></span> <strong>(Pendiente de sincronizar)</strong></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="table-scroll overflow-x-auto max-h-[600px]">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#F7F9FC] text-[#4A5A78] border-b border-[#E2E6EC] font-semibold text-xs uppercase">
                        <th class="py-3 px-3 w-10 text-center sticky left-0 bg-[#F7F9FC] z-20">#</th>
                        <th class="py-3 px-4 min-w-[200px] sticky left-10 bg-[#F7F9FC] z-20 border-r border-[#E2E6EC]">Alumno</th>
                        <template x-for="act in getActividadesDelCriterioActivo()" :key="act.id">
                            <th class="py-3 px-3 text-center border-l border-[#E2E6EC] min-w-[125px]">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="font-bold text-[#1A2332] truncate text-xs" x-text="act.nombre" :title="act.nombre"></span>
                                    
                                    <!-- ÍCONO DE INFORMACIÓN PARA DESCRIPCIÓN (MODAL) -->
                                    <template x-if="act.descripcion || act.descripcion_actividad || act.detalle">
                                        <button type="button" @click="mostrarInfoActividad(act)" class="text-[#7A8AA0] hover:text-[#2E3E5C] transition-colors cursor-pointer" title="Ver descripción">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </template>
                                </div>
                                <div class="text-[10px] text-[#7A8AA0] mt-0.5">Máx: <strong x-text="act.valorMaximo"></strong> pts</div>
                                <template x-if="act.desincronizada">
                                    <div class="mt-1 text-[9px] font-bold text-[#B7791F] bg-amber-50 px-1.5 py-0.2 rounded inline-block">Modificada</div>
                                </template>
                            </th>
                        </template>
                        <th class="py-3 px-4 text-center border-l-2 border-[#E2E6EC] bg-[#F7F9FC] font-bold text-[#1A2332] min-w-[110px] sticky right-0 z-20">
                            Promedio
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E6EC]">
                    <template x-for="(alumno, idx) in alumnosFiltrados" :key="alumno.id">
                        <tr class="hover:bg-[#F7F9FC] transition-colors group">
                            <td class="py-2.5 px-3 text-center text-[#7A8AA0] font-mono text-xs sticky left-0 bg-white group-hover:bg-[#F7F9FC] z-10" x-text="idx + 1"></td>
                            <td class="py-2.5 px-4 font-medium text-xs text-[#1A2332] sticky left-10 bg-white group-hover:bg-[#F7F9FC] z-10 border-r border-[#E2E6EC] whitespace-nowrap" x-text="alumno.nombre"></td>
                            
                            <!-- CALIFICACIONES ESTÁTICAS CON ÍCONO DE OBSERVACIÓN -->
                            <template x-for="act in getActividadesDelCriterioActivo()" :key="act.id + '-' + alumno.id">
                                <td class="py-2 px-3 text-center border-l border-[#E2E6EC]">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <template x-if="getCalificacion(alumno, act.id).estado === 'ENTREGADO'">
                                            <span class="font-bold text-xs" :class="{
                                                'text-[#2F855A]': getCalificacion(alumno, act.id).valor !== null && Number(getCalificacion(alumno, act.id).valor) >= (act.valorMaximo * 0.8),
                                                'text-[#B7791F]': getCalificacion(alumno, act.id).valor !== null && Number(getCalificacion(alumno, act.id).valor) >= (act.valorMaximo * 0.6) && Number(getCalificacion(alumno, act.id).valor) < (act.valorMaximo * 0.8),
                                                'text-[#C53030]': getCalificacion(alumno, act.id).valor !== null && Number(getCalificacion(alumno, act.id).valor) < (act.valorMaximo * 0.6),
                                                'text-[#A8B2C4]': getCalificacion(alumno, act.id).valor === null || getCalificacion(alumno, act.id).valor === ''
                                            }" x-text="getCalificacion(alumno, act.id).valor !== null && getCalificacion(alumno, act.id).valor !== '' ? getCalificacion(alumno, act.id).valor : '—'"></span>
                                        </template>

                                        <template x-if="getCalificacion(alumno, act.id).estado === 'NO_ENTREGO'">
                                            <span class="text-xs font-bold text-[#C53030]">0</span>
                                        </template>

                                        <template x-if="getCalificacion(alumno, act.id).estado === 'FALTA_JUSTIFICADA'">
                                            <span class="text-[11px] font-bold text-[#4A5A78] bg-slate-100 px-1.5 py-0.5 rounded">FJ</span>
                                        </template>

                                        <!-- BOTÓN DE OBSERVACIÓN DISPONIBLE -->
                                        <template x-if="getCalificacion(alumno, act.id).observaciones">
                                            <button type="button" @click="mostrarObservacion(alumno, act)" class="text-[#2E3E5C] hover:text-[#1A2332] transition-colors p-0.5" title="Ver observación">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </template>

                            <!-- PROMEDIO A 2 DECIMALES -->
                            <td class="py-2.5 px-4 text-center border-l-2 border-[#E2E6EC] bg-white group-hover:bg-[#F7F9FC] sticky right-0 z-10">
                                <template x-if="calcularPromedioCriterio(alumno)['n/a']">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-[#E8ECF1] text-[#A8B2C4]">N/A</span>
                                </template>
                                <template x-if="!calcularPromedioCriterio(alumno)['n/a']">
                                    <span class="text-xs font-bold" :class="{
                                        'text-[#2F855A]': calcularPromedioCriterio(alumno).valor >= 8,
                                        'text-[#B7791F]': calcularPromedioCriterio(alumno).valor >= 6 && calcularPromedioCriterio(alumno).valor < 8,
                                        'text-[#C53030]': calcularPromedioCriterio(alumno).valor < 6
                                    }" x-text="calcularPromedioCriterio(alumno).valor.toFixed(2)"></span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TARJETA MODAL DETALLES DE LA ACTIVIDAD -->
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

    <!-- TARJETA MODAL OBSERVACIÓN EN MODO LECTURA -->
    <div x-show="modalObsLectura" x-cloak class="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4">
        <div @click.away="modalObsLectura = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden border border-[#E2E6EC] transform transition-all">
            <div class="px-5 py-3.5 bg-[#F7F9FC] border-b border-[#E2E6EC] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-1 bg-[#2E3E5C]/10 text-[#2E3E5C] rounded-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </span>
                    <h4 class="font-bold text-sm text-[#1A2332]">Observación del Alumno</h4>
                </div>
                <button @click="modalObsLectura = false" class="text-[#7A8AA0] hover:text-[#1A2332] text-xl font-bold">&times;</button>
            </div>

            <div class="p-5 space-y-3 text-xs">
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#7A8AA0]">Alumno</span>
                    <p class="font-bold text-sm text-[#1A2332]" x-text="obsLecturaData.alumnoNombre"></p>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#7A8AA0]">Actividad</span>
                    <p class="font-medium text-slate-700" x-text="obsLecturaData.actNombre"></p>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#7A8AA0] mb-1">Nota del Docente</span>
                    <div class="p-3 bg-[#F8FAFC] border border-[#E2E6EC] rounded-lg text-slate-700 leading-relaxed text-xs" x-text="obsLecturaData.texto"></div>
                </div>
            </div>

            <div class="px-5 py-3 bg-[#F7F9FC] border-t border-[#E2E6EC] flex justify-end">
                <button type="button" @click="modalObsLectura = false" class="px-4 py-1.5 text-xs font-bold text-white bg-[#2E3E5C] hover:bg-[#23324A] rounded-md transition-colors shadow-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- ESTADO VACÍO -->
    <div x-show="tablaCargada && alumnosFiltrados.length === 0" class="bg-white rounded-md p-8 border border-[#E2E6EC] text-center text-xs text-[#7A8AA0]">
        No hay alumnos o actividades para mostrar con los filtros seleccionados
    </div>
</div>