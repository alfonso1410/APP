<!-- MODAL OBSERVACIONES -->
<div x-show="modalObs" x-cloak class="fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4">
    <div @click.away="modalObs = false" class="bg-white rounded-lg shadow-xl max-w-md w-full overflow-hidden border border-[#E2E6EC]">
        <div class="px-6 py-4 border-b border-[#E2E6EC] flex justify-between items-center bg-[#F7F9FC]">
            <h3 class="font-semibold text-[#1A2332] text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-[#2E3E5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                Observaciones
            </h3>

            <button @click="modalObs = false" class="text-[#A8B2C4] hover:text-[#4A5A78] text-xl">&times;</button>
        </div>

        <div class="p-6 space-y-3 text-sm">
            <div>
                <p class="font-semibold text-[#1A2332]" x-text="obsData.alumnoNombre"></p>
                <p class="text-[#7A8AA0] text-xs" x-text="'Actividad: ' + obsData.actNombre"></p>
            </div>

            <div>
                <label class="block font-medium text-[#4A5A78] mb-1">Nota o Justificación registrada:</label>
                <textarea 
                    x-model="obsData.texto" 
                    rows="3" 
                    placeholder="Ej. Presentó justificante médico con fecha..." 
                    class="w-full bg-[#F7F9FC] border border-[#E2E6EC] rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#2E3E5C] resize-none"
                ></textarea>
            </div>
        </div>

        <div class="px-6 py-3.5 border-t border-[#E2E6EC] bg-[#F7F9FC] flex justify-end gap-2">
            <button 
                type="button" 
                @click="modalObs = false" 
                class="px-4 py-2 text-sm font-medium text-[#4A5A78] hover:bg-[#E8ECF1] rounded-md transition-colors"
            >
                Cerrar
            </button>

            <button 
                type="button" 
                @click="guardarObservacionModal()" 
                class="px-4 py-2 text-sm font-medium text-white bg-[#2E3E5C] hover:bg-[#23324A] rounded-md transition-colors"
            >
                Guardar Nota
            </button>
        </div>
    </div>
</div>