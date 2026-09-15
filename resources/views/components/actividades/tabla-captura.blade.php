{{-- resources/views/components/actividades/tabla-captura.blade.php --}}
@props([
    'mostrarBotonSincronizar' => true,
])

<!-- BARRA DE ACCIONES SUPERIOR -->
<div x-show="tablaCargada" class="flex items-center gap-3">
    <button 
        x-show="cambiosPendientes.length > 0"
        x-transition
        @click="flushCambiosPendientes()" 
        :disabled="guardandoCambios || periodoCerrado"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white rounded-md transition-colors"
        style="background:var(--system-primary,#2E3E5C)"
    >
        <svg x-show="guardandoCambios" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        <span x-text="guardandoCambios ? 'Guardando...' : `💾 Guardar (${cambiosPendientes.length})`"></span>
    </button>

    @if($mostrarBotonSincronizar)
    <button 
        @click="sincronizar()" 
        :disabled="periodoCerrado || guardando || guardandoCambios" 
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#2E3E5C] rounded-md hover:bg-[#23324A] disabled:bg-[#A8B2C4] disabled:cursor-not-allowed transition-colors"
    >
        <svg x-show="!guardando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        <svg x-show="guardando" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        <span x-text="guardando ? 'Actualizando...' : 'Actualizar'"></span>
    </button>
    @endif
</div>

<!-- ALERTA PERIODO CERRADO -->
<template x-if="periodoCerrado && tablaCargada">
    <div class="bg-red-50 border border-red-200 rounded-md p-3 text-sm text-red-700 flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span><strong>Periodo cerrado.</strong> Modo solo lectura.</span>
    </div>
</template>

<!-- ALERTA DESINCRONIZACIÓN -->
<template x-if="!periodoCerrado && hayDesincronizados && tablaCargada">
    <div class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-md p-3 text-sm text-amber-800">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Hay <strong>actividades modificadas</strong> pendientes de recálculo.</span>
        </div>
        <span class="text-xs font-medium text-amber-900 bg-amber-200/60 px-2 py-0.5 rounded">Recálculo pendiente</span>
    </div>
</template>

<!-- CRITERIOS TABS -->
<div x-show="tablaCargada" class="criterios-bar grid grid-cols-1 md:grid-cols-4">
    <template x-for="criterio in criterios" :key="criterio.id">
        <button type="button" @click="criterioActivo = criterio.id" class="criterio-tab" :class="{ 'active': criterioActivo === criterio.id }">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-[#1A2332]" x-text="criterio.nombre"></div>
                    <div class="text-xs text-[#7A8AA0] mt-0.5" x-text="criterio.peso + ' de la evaluación'"></div>
                </div>
                <span class="text-[10px] font-medium whitespace-nowrap" :class="{
                    'text-[#2F855A]': criterio.estado === 'al_dia',
                    'text-[#B7791F]': criterio.estado === 'desincronizado',
                    'text-[#A8B2C4]': criterio.estado === 'sin_actividades'
                }" x-text="criterio.estado === 'al_dia' ? 'Al día' : criterio.estado === 'desincronizado' ? 'Pendiente' : 'Sin acts'"></span>
            </div>
            <div class="mt-2 text-xs text-[#7A8AA0]" x-text="criterio.actividadesRegistradas + ' actividades'"></div>
        </button>
    </template>
</div>

<!-- FILTRO RÁPIDO -->
<div x-show="tablaCargada" class="flex items-center gap-1 text-sm">
    <span class="text-[#7A8AA0] mr-1">Filtrar:</span>
    <button @click="filtroRapido = 'todos'" :class="filtroRapido === 'todos' ? 'text-[#2E3E5C] font-medium' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-3 py-1 rounded-md transition-colors">Todos</button>
    <button @click="filtroRapido = 'desincronizados'" :class="filtroRapido === 'desincronizados' ? 'text-[#B7791F] font-medium' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-3 py-1 rounded-md transition-colors">Desincronizados</button>
    <button @click="filtroRapido = 'pendientes'" :class="filtroRapido === 'pendientes' ? 'text-[#2E3E5C] font-medium' : 'text-[#7A8AA0] hover:text-[#4A5A78]'" class="px-3 py-1 rounded-md transition-colors">Pendientes</button>
</div>

<!-- TABLA DE CAPTURA -->
<div x-show="tablaCargada" class="bg-white rounded-md border border-[#E2E6EC] overflow-hidden">
    <div class="px-4 py-3 border-b border-[#E2E6EC] flex flex-wrap items-center justify-between gap-3 bg-[#F7F9FC]">
        <div>
            <h3 class="font-semibold text-sm text-[#1A2332] flex items-center gap-2">
                <span x-text="getCriterioActivo().nombre"></span>
                <span class="text-xs font-normal text-[#7A8AA0] bg-[#E8ECF1] px-2 py-0.5 rounded" x-text="getCriterioActivo().peso"></span>
            </h3>
            <p class="text-xs text-[#7A8AA0] mt-0.5">Captura rápida: <kbd class="bg-[#E8ECF1] px-1.5 py-0.5 rounded text-[10px] font-medium">Enter</kbd> avanza al siguiente alumno</p>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="getCriterioActivo().estado === 'al_dia'">
                <div class="text-xs text-[#4A5A78] bg-white border border-[#E2E6EC] px-3 py-1.5 rounded-md flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2F855A]"></span>
                    <span>Actualizado: <strong x-text="getCriterioActivo().calculadoEn"></strong> por <strong x-text="getCriterioActivo().calculadoPor"></strong></span>
                </div>
            </template>
            <template x-if="getCriterioActivo().estado === 'desincronizado'">
                <div class="text-xs text-[#B7791F] bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-md flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#B7791F]"></span>
                    <span>Actualizado: <span x-text="getCriterioActivo().calculadoEn"></span> <strong>(Pendiente)</strong></span>
                </div>
            </template>
            
            <!--  BOTÓN NUEVA ACTIVIDAD DENTRO DEL HEADER DE LA TABLA -->
            <button @click="abrirModalNuevaActividad()" :disabled="periodoCerrado" class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-[#1A2332] rounded-md hover:bg-[#2E3E5C] disabled:bg-[#A8B2C4] disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva actividad</span>
            </button>
        </div>
    </div>

 
    <div class="table-scroll overflow-x-auto max-h-[600px]">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-[#F7F9FC] text-[#4A5A78] border-b border-[#E2E6EC] font-medium text-xs uppercase">
                    <th class="py-3 px-4 w-10 text-center sticky left-0 bg-[#F7F9FC] z-20">#</th>
                    <th class="py-3 px-4 min-w-[180px] sticky left-10 bg-[#F7F9FC] z-20 border-r border-[#E2E6EC]">Alumno</th>
                    <template x-for="act in getActividadesDelCriterioActivo()" :key="act.id">
                        <th class="py-3 px-3 text-center border-l border-[#E2E6EC] min-w-[130px] group/col relative">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-medium text-[#1A2332] truncate" x-text="act.nombre" :title="act.nombre"></span>
                                <div class="hidden group-hover/col:flex items-center gap-1" x-show="!periodoCerrado">
                                    <button type="button" @click.stop="abrirModalEditarActividad(act)" class="text-[#7A8AA0] hover:text-[#2E3E5C] p-0.5" title="Editar">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button type="button" @click.stop="confirmarEliminarActividad(act)" class="text-[#7A8AA0] hover:text-[#C53030] p-0.5" title="Eliminar">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="text-[10px] font-normal text-[#7A8AA0] mt-0.5">Máx: <span x-text="act.valorMaximo"></span> pts</div>
                            <template x-if="act.desincronizada"><div class="mt-1 text-[9px] font-medium text-[#B7791F] bg-amber-50 px-1.5 py-0.5 rounded inline-block">Editado</div></template>
                            <div class="text-[10px] text-[#A8B2C4] mt-0.5" x-text="act.fecha"></div>
                        </th>
                    </template>
                    <th class="py-3 px-4 text-center border-l-2 border-[#E2E6EC] bg-[#F7F9FC] font-semibold text-[#1A2332] min-w-[100px] sticky right-0 z-20">Promedio</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E6EC]">
                <template x-for="(alumno, idx) in alumnosFiltrados" :key="alumno.id">
                    <tr class="hover:bg-[#F7F9FC] transition-colors group">
                        <td class="py-2.5 px-4 text-center text-[#A8B2C4] font-mono text-xs sticky left-0 bg-white group-hover:bg-[#F7F9FC] z-10" x-text="idx + 1"></td>
                        <td class="py-2.5 px-4 font-medium text-[#1A2332] sticky left-10 bg-white group-hover:bg-[#F7F9FC] z-10 border-r border-[#E2E6EC] whitespace-nowrap" x-text="alumno.nombre"></td>
                        <template x-for="act in getActividadesDelCriterioActivo()" :key="act.id + '-' + alumno.id">
                            <td class="py-2 px-2 text-center border-l border-[#E2E6EC] relative">
                                <div class="flex items-center justify-center gap-1">
                                    <select x-model="getCalificacion(alumno, act.id).estado" @change="autoGuardarDebounced(alumno, act)" :disabled="periodoCerrado" class="w-7 text-[10px] bg-transparent border-none text-center cursor-pointer focus:ring-0 p-0 appearance-none font-medium" :class="{
                                        'text-[#7A8AA0]': getCalificacion(alumno, act.id).estado === 'ENTREGADO',
                                        'text-[#C53030]': getCalificacion(alumno, act.id).estado === 'NO_ENTREGO',
                                        'text-[#2E3E5C]': getCalificacion(alumno, act.id).estado === 'FALTA_JUSTIFICADA'
                                    }">
                                        <option value="ENTREGADO">✓</option>
                                        <option value="NO_ENTREGO">✗</option>
                                        <option value="FALTA_JUSTIFICADA">FJ</option>
                                    </select>
                                    
                                    <template x-if="getCalificacion(alumno, act.id).estado === 'ENTREGADO'">
                                        <input type="number" step="0.1" min="0" :max="act.valorMaximo" x-model.number="getCalificacion(alumno, act.id).valor" @input="validarRango($event, act.valorMaximo); autoGuardarDebounced(alumno, act)" :disabled="periodoCerrado" class="w-14 text-center bg-[#F7F9FC] border border-[#E2E6EC] rounded py-1 font-medium text-[#1A2332] text-sm focus:bg-white focus:ring-1 focus:ring-[#2E3E5C] focus:outline-none transition-colors" :class="getInputClass(getCalificacion(alumno, act.id), act.valorMaximo)">
                                    </template>
                                    <template x-if="getCalificacion(alumno, act.id).estado !== 'ENTREGADO'">
                                        <div class="w-14 text-center py-1 text-sm font-medium select-none" :class="getCalificacion(alumno, act.id).estado === 'NO_ENTREGO' ? 'text-[#C53030]' : 'text-[#7A8AA0]'" x-text="getCalificacion(alumno, act.id).estado === 'NO_ENTREGO' ? '0' : '—'"></div>
                                    </template>
                                    
                                    <button @click="abrirObservacion(alumno, act)" class="transition p-0.5 rounded hover:bg-[#E8ECF1]" :class="getCalificacion(alumno, act.id).observaciones ? 'text-[#2E3E5C]' : 'text-[#C8CED8] hover:text-[#7A8AA0]'" title="Observaciones">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </template>
                        <td class="py-2.5 px-4 text-center border-l-2 border-[#E2E6EC] font-semibold bg-white sticky right-0 z-10">
                            <template x-if="calcularPromedioCriterio(alumno)['n/a']">
                                <span class="inline-block px-2 py-0.5 rounded text-xs bg-[#E8ECF1] text-[#A8B2C4]">N/A</span>
                            </template>
                            <template x-if="!calcularPromedioCriterio(alumno)['n/a']">
                                <span class="font-semibold" :class="{
                                    'text-[#2F855A]': calcularPromedioCriterio(alumno).valor >= 8,
                                    'text-[#B7791F]': calcularPromedioCriterio(alumno).valor >= 6 && calcularPromedioCriterio(alumno).valor < 8,
                                    'text-[#C53030]': calcularPromedioCriterio(alumno).valor < 6
                                }" x-text="calcularPromedioCriterio(alumno).valor.toFixed(1)"></span>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<!-- ESTADO VACÍO -->
<div x-show="tablaCargada && alumnosFiltrados.length === 0" class="bg-white rounded-md p-8 border border-[#E2E6EC] text-center text-sm text-[#7A8AA0] mt-4">
    No hay alumnos o actividades para mostrar con el filtro actual
</div>