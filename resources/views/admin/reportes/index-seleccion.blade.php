<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes: Promedio General Por Grupo') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ selectedNivel: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gray-100 sm:rounded-2xl p-8">
                
                <h3 class="text-lg font-bold text-slate-700 mb-8">Selecciona un grupo para consultar el promedio</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    
                    {{-- PASO 1: SELECCIONA EL GRADO (NIVEL) --}}
                    <div>
                        <div class="flex items-center mb-6">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-princeton text-white font-bold text-sm mr-3">1</span>
                            <div>
                                <h4 class="font-bold text-slate-800">Selecciona el grado</h4>
                                <p class="text-xs text-slate-500">Elige el nivel educativo.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach($niveles as $nivel)
                                @if($nivel->grados->count() > 0)
                                    <div 
                                        @click="selectedNivel = {{ $nivel->nivel_id }}"
                                        :class="selectedNivel == {{ $nivel->nivel_id }} ? 'border-princeton bg-blue-50/50 ring-1 ring-princeton' : 'border-slate-200 hover:border-princeton hover:bg-slate-50'"
                                        class="flex items-center justify-between p-5 border-2 rounded-2xl cursor-pointer transition-all duration-200 group"
                                    >
                                        <div class="flex items-center">
                                            <div :class="selectedNivel == {{ $nivel->nivel_id }} ? 'bg-princeton text-white' : 'bg-slate-100 text-slate-500'" class="p-3 rounded-full mr-4 transition-colors">
                                                @if(str_contains(strtoupper($nivel->nombre), 'PREESCOLAR'))
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                @else
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.08 0 01.665-6.479L12 14z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.08 0 01.665-6.479L12 14z" /></svg>
                                                @endif
                                            </div>
                                            <div>
                                                <h5 class="font-bold text-slate-800 text-lg">{{ $nivel->nombre }}</h5>
                                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">
                                                    {{ $nivel->grados->pluck('nombre')->implode(', ') }}
                                                </p>
                                            </div>
                                        </div>
                                        <svg :class="selectedNivel == {{ $nivel->nivel_id }} ? 'text-princeton' : 'text-slate-300'" class="w-6 h-6 transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- PASO 2: SELECCIONA EL GRUPO --}}
                    <div class="border-l border-slate-100 pl-12">
                        <div class="flex items-center mb-6 text-slate-400" :class="selectedNivel ? 'text-slate-800' : ''">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm mr-3 transition-colors" :class="selectedNivel ? 'bg-princeton text-white' : 'bg-slate-200 text-slate-500'">2</span>
                            <div>
                                <h4 class="font-bold">Selecciona el grupo</h4>
                                <p class="text-xs">Elige el grupo específico.</p>
                            </div>
                        </div>

                        {{-- Estado vacío --}}
                        <template x-if="!selectedNivel">
                            <div class="flex flex-col items-center justify-center h-64 border-2 border-dashed border-slate-100 rounded-2xl text-slate-400">
                                <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-sm">Selecciona un grado a la izquierda</p>
                            </div>
                        </template>

                        {{-- Lista de Grupos dinámicos --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($niveles as $nivel)
                                @foreach($nivel->grados as $grado)
                                    @foreach($grado->grupos as $grupo)
                                        <a 
                                            x-show="selectedNivel == {{ $nivel->nivel_id }}"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-4"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            href="{{ route('admin.reportes.resumen', $grupo->grupo_id) }}" 
                                            class="flex items-center justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-princeton hover:shadow-md transition-all group"
                                        >
                                            <span class="font-bold text-slate-700 uppercase group-hover:text-princeton transition-colors">{{ $grado->nombre }} {{ $grupo->nombre_grupo }}</span>
                                            <svg class="w-4 h-4 text-slate-300 group-hover:text-princeton transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </a>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Footer Info --}}
                <div class="mt-12 pt-6 border-t border-slate-50 flex justify-center">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-50 rounded-full text-blue-600 text-xs font-medium">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                        Selecciona primero un grado y luego el grupo para ver el promedio.
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>