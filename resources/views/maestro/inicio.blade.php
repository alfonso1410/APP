{{-- 1. ELIMINAMOS el x-data y x-cloak de aquí --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             Bienvenido/a, {{ $maestro->name }}
        </h2>
    </x-slot>

    {{-- 2. AÑADIMOS UN DIV CONTENEDOR con el x-data y x-cloak --}}
    <div x-data="{ showModal: false, modalGroup: {} }" x-cloak>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-10">
                    <h3 class="text-2xl font-semibold text-gray-700 mb-4">
                        Grupos donde imparte materias
                    </h3>
                    @if($gruposDondeImparte->isEmpty())
                        <p class="text-gray-500">No imparte materias en ningún grupo actualmente.</p>
                    @else
                        @php $colors = ['bg-blue-400', 'bg-green-400', 'bg-indigo-400']; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($gruposDondeImparte as $grupo)
                                @php $color = $colors[$loop->index % count($colors)]; @endphp
                                <div class="{{ $color }} text-white rounded-xl shadow-lg p-6 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-3xl font-bold">
                                            {{ $grupo->grado->nombre ?? '' }} {{ $grupo->nombre_grupo ?? 'Grupo' }}
                                        </h4>
                                        <span class="text-lg opacity-90">
                                            Ciclo {{ $cicloActivoNombre }} 
                                            · {{ $grupo->alumnos_count }} Alumnos Totales
                                        </span>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.calificaciones.index') }}?grupo_id={{ $grupo->grupo_id }}" 
                                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 bg-opacity-80 rounded-lg font-semibold text-sm text-white hover:bg-opacity-100 transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Llenar Boletas
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mb-10">
                    <h3 class="text-2xl font-semibold text-gray-700 mb-4">
                        Grupos Titulares
                    </h3>
                    @if($gruposTitulares->isEmpty())
                        <p class="text-gray-500">No es tutor de ningún grupo actualmente.</p>
                    @else
                        @php $colors = ['bg-orange-400', 'bg-pink-400', 'bg-teal-400']; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($gruposTitulares as $grupo)
                                @php $color = $colors[$loop->index % count($colors)]; @endphp
                                <div class="{{ $color }} text-white rounded-xl shadow-lg p-6 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-3xl font-bold">
                                            {{ $grupo->grado->nivel->nombre ?? '' }} {{ $grupo->grado->nombre ?? '' }} - {{ $grupo->nombre_grupo ?? 'Grupo' }}
                                        </h4>
                                        <span class="text-lg opacity-90">
                                            Ciclo {{ $cicloActivoNombre }} 
                                            · {{ $grupo->alumnos_count }} Alumnos Totales
                                        </span>
                                    </div>
                                    <div class="mt-6">
                                        <button 
                                            @click="showModal = true; modalGroup = { 
                                                nombre: '{{ addslashes($grupo->grado->nivel->nombre ?? '') }} {{ addslashes($grupo->grado->nombre ?? '') }} - {{ addslashes($grupo->nombre_grupo ?? '') }}', 
                                                alumnos: {{ $grupo->alumnos_count ?? 0 }},
                                                ciclo: '{{ $cicloActivoNombre }}'
                                            }"
                                            class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 bg-opacity-80 rounded-lg font-semibold text-sm text-white hover:bg-opacity-100 transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Ver Grupo
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div>
                    </div>

            </div>
        </div>

        {{-- 
            Este modal ahora está DENTRO del 'div' con 'x-data',
            por lo que 'x-show="showModal"' funcionará correctamente.
        --}}
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-black bg-opacity-50"
             @click.away="showModal = false">
            
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg mx-4" 
                 @click.stop
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">
                 
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-2xl font-semibold text-gray-800" x-text="modalGroup.nombre"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                
                <div class="py-4">
                    <ul class="list-disc list-inside mt-4 space-y-2 text-gray-600">
                        <li class="text-lg">Ciclo Escolar: <strong class="text-gray-800" x-text="modalGroup.ciclo"></strong></li>
                        <li class="text-lg">Total de Alumnos: <strong class="text-gray-800" x-text="modalGroup.alumnos"></strong></li>
                    </ul>
                </div>

                <div class="flex justify-end pt-3 border-t">
                    <button @click="showModal = false" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
        
    </div> {{-- 3. FIN del div x-data --}}
</x-app-layout>