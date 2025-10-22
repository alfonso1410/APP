<x-app-layout>
    {{-- 
      NOTA: Este código asume que estás usando el layout 'app.blade.php' de Breeze.
      Tu diseño tiene una barra lateral azul oscuro personalizada. 
      Para replicar ESO, necesitaríamos crear un 'maestro-layout.blade.php',
      pero por ahora, este código funcionará DENTRO de tu layout de admin existente.
    --}}

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Usamos el nombre del maestro que pasamos desde el controlador --}}
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
                            'bg-orange-400',// Naranja (como en tu diseño)
                            'bg-indigo-400',
                            'bg-pink-400',
                        ];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($gruposAsignados as $grupo)
                            @php
                                // Rotamos los colores
                                $color = $colors[$loop->index % count($colors)];
                            @endphp

                            <div class="{{ $color }} text-white rounded-xl shadow-lg p-6 flex flex-col justify-between">
                                <div>
                                    {{-- Nombre del Grado y Grupo --}}
                                    <h4 class="text-3xl font-bold">
                                        {{-- Asume que la relación 'grado' existe y tiene 'nombre' (Ej: "1°") --}}
                                        {{ $grupo->grado->nombre ?? '' }} {{ $grupo->nombre ?? 'Grupo' }}
                                    </h4>
                                    <span class="text-lg opacity-90">
                                        {{-- ¡CORRECCIÓN! Leemos el dato de la base de datos --}}
                                        Ciclo {{ $grupo->ciclo_escolar }} 
                                        · {{ $grupo->alumnos_count }} Alumnos Totales
                                    </span>
                                </div>
                                
                                <div class="mt-6">
                                    {{-- 
                                      Este botón debe ir a la página de "Llenar Boletas"
                                      Por ahora, usamos un enlace '#'
                                    --}}
                                    <a href="#" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 bg-opacity-80 rounded-lg font-semibold text-sm text-white hover:bg-opacity-100 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Llenar Boletas
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">
                    Notificaciones Importantes
                </h3>
                
                @if(empty($notificaciones))
                    <p class="text-gray-500">No hay notificaciones.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($notificaciones as $notif)
                            {{-- Banner Amarillo (Warning) --}}
                            @if ($notif['tipo'] == 'warning')
                                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg" role="alert">
                                    <div class="flex">
                                        <div class="py-1">
                                            <svg class="h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold">{{ $notif['mensaje'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-- Aquí podrías añadir más 'else if' para 'info' (azul) o 'danger' (rojo) --}}
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>