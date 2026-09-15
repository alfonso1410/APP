<!-- MODAL EDITAR ACTIVIDAD -->
<div x-show="modalEditarActividad" x-cloak class="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4">
    <div @click.away="modalEditarActividad = false" class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden border border-[#E2E6EC]">
        <div class="px-6 py-4 border-b border-[#E2E6EC] flex justify-between items-center bg-[#F7F9FC]">
            <h3 class="font-semibold text-[#1A2332] text-sm">Editar Actividad</h3>
            <button @click="modalEditarActividad = false" class="text-[#A8B2C4] hover:text-[#4A5A78] text-xl">&times;</button>
        </div>

        <form @submit.prevent="guardarEdicionActividad()" class="p-6 space-y-4 text-sm">
            <div>
                <label class="block font-medium text-[#4A5A78] mb-1">Nombre de la actividad *</label>
                <input 
                    type="text" 
                    x-model="formEditarActividad.nombre_actividad" 
                    required 
                    class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]"
                >
            </div>

            <div>
                <label class="block font-medium text-[#4A5A78] mb-1">Descripción o instrucciones</label>
                <textarea 
                    rows="2" 
                    x-model="formEditarActividad.descripcion" 
                    class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] resize-none"
                ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-[#4A5A78] mb-1">Criterio *</label>
                    <select 
                        x-model="formEditarActividad.materia_criterio_id" 
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
                        x-model.number="formEditarActividad.valor_maximo" 
                        required 
                        class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]"
                    >
                </div>
            </div>

            <div>
                <label class="block font-medium text-[#4A5A78] mb-1">Fecha de aplicación *</label>
                <input 
                    type="date" 
                    x-model="formEditarActividad.fecha_actividad" 
                    required 
                    class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C]"
                >
            </div>

            <div class="pt-3 border-t border-[#E2E6EC] flex justify-end gap-2">
                <button 
                    type="button" 
                    @click="modalEditarActividad = false" 
                    class="px-4 py-2 text-sm font-medium text-[#4A5A78] hover:bg-[#E8ECF1] rounded-md transition-colors"
                >
                    Cancelar
                </button>

                <button 
                    type="submit" 
                    :disabled="guardandoActividad" 
                    class="px-4 py-2 text-sm font-medium text-white bg-[#2E3E5C] hover:bg-[#23324A] rounded-md transition-colors"
                >
                    <span x-text="guardandoActividad ? 'Guardando...' : 'Actualizar Actividad'"></span>
                </button>
            </div>
        </form>
    </div>
</div>