<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SECCIÓN DE RESUMEN DE ESTADÍSTICAS --}}
            <div>
                <h3 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                    Resumen del Ciclo Escolar
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-white">
                    {{-- Tarjeta de Alumnos --}}
                    <div class="bg-blue-500 p-6 rounded-lg shadow-lg">
                        <h4 class="text-5xl font-bold">{{ $totalAlumnos }}</h4>
                        <p class="mt-2 text-lg">Alumnos Activos</p>
                    </div>

                    {{-- Tarjeta de Maestros --}}
                    <div class="bg-green-500 p-6 rounded-lg shadow-lg">
                        <h4 class="text-5xl font-bold">{{ $totalMaestros }}</h4>
                        <p class="mt-2 text-lg">Maestros Registrados</p>
                    </div>

                    {{-- Tarjeta de Grupos --}}
                    <div class="bg-orange-400 p-6 rounded-lg shadow-lg">
                        <h4 class="text-5xl font-bold">{{ $totalGrupos }}</h4>
                        <p class="mt-2 text-lg">Grupos Activos</p>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN DE ACCESOS RÁPIDOS --}}
            <div class="mt-10">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Accesos Rápidos
                </h3>
                <div class="flex flex-wrap gap-4">

                    {{-- === CORRECCIÓN AQUÍ === --}}
                    {{-- Cambiado route('alumnos.create') a route('alumnos.index') --}}
                    <a href="{{ route('alumnos.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Registrar Alumno
                    </a>
                    {{-- === FIN CORRECCIÓN === --}}

                    {{-- Nota: Si Registrar Maestro y Crear Grupo también son modales ahora, deberías cambiar sus href a route('maestros.index') y route('grupos.index') respectivamente --}}
                    <a href="{{ route('maestros.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center"> {{-- Asumiendo que maestros.index existe --}}
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Registrar Maestro
                    </a>
                    <a href="#" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center"> {{-- Asumiendo que grupos.index existe --}}
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Crear Grupo
                    </a>
                    {{-- Cambia '#' por la ruta correcta si tienes una página para registrar calificaciones --}}
                    <a href="#" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Registrar Calificación
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>