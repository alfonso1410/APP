<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             {{-- El nombre del maestro viene del controlador --}}
            Bienvenido/a, {{ $maestro->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-10">
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">
                    Mis Grupos Asignados
                </h3>
                
                @if($gruposAsignados->isEmpty())
                    <p class="text-gray-500">No tienes grupos asignados actualmente.</p>
                @else
                    {{-- Definimos los colores de las tarjetas --}}
                    @php
                        $colors = [
                            'bg-blue-400',  // Azul
                            'bg-green-400', // Verde
                            'bg-orange-400',// Naranja
                            'bg-indigo-400',
                            'bg-pink-400',
                        ];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($gruposAsignados as $grupo)
                            @php
                                $color = $colors[$loop->index % count($colors)];
                            @endphp

                            <div class="{{ $color }} text-white rounded-xl shadow-lg p-6 flex flex-col justify-between">
                                <div>
                                    {{-- Nombre del Grado y Grupo --}}
                                    <h4 class="text-3xl font-bold">
                                        {{ $grupo->grado->nombre ?? '' }} {{ $grupo->nombre ?? 'Grupo' }}
                                    </h4>
                                    <span class="text-lg opacity-90">
                                        {{-- Usamos el ciclo escolar dinámico --}}
                                        Ciclo {{ $grupo->ciclo_escolar }} 
                                        · {{ $grupo->alumnos_count }} Alumnos Totales
                                    </span>
                                </div>
                                
                                <div class="mt-6">
                                    {{-- ¡BOTÓN CORREGIDO PARA ASISTENCIA! --}}
                                    <a href="{{ route('maestro.asistencias.tomar', ['grupo' => $grupo->grupo_id]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 bg-opacity-80 rounded-lg font-semibold text-sm text-white hover:bg-opacity-100 transition">
                                        {{-- Icono de Check (puedes cambiarlo) --}}
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        Tomar Asistencia
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            {{-- Esta página no tiene la sección de "Notificaciones" --}}

        </div>
    </div>
</x-app-layout>