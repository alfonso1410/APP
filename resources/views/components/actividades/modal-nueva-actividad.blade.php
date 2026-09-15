@props([
    'mostrarGruposReplicables' => true,
])

<!-- MODAL NUEVA ACTIVIDAD -->
<div x-show="modalNuevaActividad" x-cloak class="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4">
    <div @click.away="modalNuevaActividad = false" class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden border border-[#E2E6EC]">
        <div class="px-6 py-4 border-b border-[#E2E6EC] flex justify-between items-center bg-[#F7F9FC]">
            <h3 class="font-semibold text-[#1A2332] text-sm">Crear Nueva Actividad</h3>
            <button @click="modalNuevaActividad = false" class="text-[#A8B2C4] hover:text-[#4A5A78] text-xl">&times;</button>
        </div>

        <form @submit.prevent="guardarNuevaActividad()" class="p-6 space-y-4 text-sm">
            <div>
                <label class="block font-medium text-[#4A5A78] mb-1">Nombre de la actividad *</label>
                <input 
                    type="text" 
                    x-model="formActividad.nombre_actividad" 
                    required 
                    placeholder="Ej. Ejercicios pág. 42" 
                    class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]"
                >
            </div>

            <div>
                <label class="block font-medium text-[#4A5A78] mb-1">Descripción o instrucciones (Opcional)</label>
                <textarea 
                    rows="2" 
                    x-model="formActividad.descripcion" 
                    placeholder="Ej. Resolver en libreta los problemas 1 al 10..." 
                    class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] resize-none"
                ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Criterio a ponderar *</label>
                    <select 
                        x-model="formActividad.materia_criterio_id" 
                        required 
                        class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]"
                    >
                        <template x-for="c in criterios" :key="c.id">
                            <option :value="c.id" x-text="c.nombre + ' (' + c.peso + ')'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Valor Máximo (Puntos)</label>
                    <input 
                        type="number" 
                        step="0.5" 
                        min="0.5" 
                        x-model.number="formActividad.valor_maximo" 
                        required 
                        class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]"
                    >
                </div>
            </div>

            <div>
                <label class="block font-medium text-[#4A5A78] mb-1">Fecha de aplicación *</label>
                <input 
                    type="date" 
                    x-model="formActividad.fecha_actividad" 
                    required 
                    class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]"
                >
            </div>

            @if($mostrarGruposReplicables)
                <div class="bg-[#F7F9FC] border border-[#E2E6EC] rounded-md p-4 space-y-2" x-show="gruposReplicables.length > 0">
                    <label class="block font-medium text-[#1A2332]">Replicar a otros grupos:</label>
                    <p class="text-xs text-[#7A8AA0]">
                        Se crearán <strong>copias independientes</strong>. Los cambios posteriores no afectarán a los otros grupos.
                    </p>

                    <div class="flex items-center gap-4 pt-1">
                        <template x-for="g in gruposReplicables" :key="g.grupo_id">
                            <label class="flex items-center gap-1.5 text-[#4A5A78] font-medium cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    :value="g.grupo_id" 
                                    x-model="formActividad.grupos_replicar" 
                                    class="rounded border-[#E2E6EC] text-[#2E3E5C] focus:ring-1 focus:ring-[#2E3E5C]"
                                >
                                <span x-text="g.nombre_grupo"></span>
                            </label>
                        </template>
                    </div>
                </div>
            @endif

            <div class="pt-3 border-t border-[#E2E6EC] flex justify-end gap-2">
                <button 
                    type="button" 
                    @click="modalNuevaActividad = false" 
                    class="px-4 py-2 text-sm font-medium text-[#4A5A78] hover:bg-[#E8ECF1] rounded-md transition-colors"
                >
                    Cancelar
                </button>

                <button 
                    type="submit" 
                    :disabled="guardandoActividad" 
                    class="px-4 py-2 text-sm font-medium text-white bg-[#2E3E5C] hover:bg-[#23324A] rounded-md transition-colors"
                >
                    <span x-text="guardandoActividad ? 'Guardando...' : 'Guardar Actividad'"></span>
                </button>
            </div>
        </form>
    </div>
</div>