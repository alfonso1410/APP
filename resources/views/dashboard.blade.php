<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard

 </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div>
                <h3 class="font-semibold text-xl text-gray-800 leading-tight">
                    Resumen del Ciclo Escolar
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-400 p-6 rounded-lg shadow-lg text-white">
                        <h4 class="text-5xl font-bold">{{ $totalAlumnos }}</h4>
                        <p class="mt-2 text-lg">Alumnos Totales</p>
                    </div>

                    <div class="bg-green-500 p-6 rounded-lg shadow-lg text-white">
                        <h4 class="text-5xl font-bold">{{ $totalMaestros }}</h4>
                        <p class="mt-2 text-lg">Maestros Registrados</p>
                    </div>

                    <div class="bg-orange-400 p-6 rounded-lg shadow-lg text-white">
                        <h4 class="text-5xl font-bold">{{ $totalGrupos }}</h4>
                        <p class="mt-2 text-lg">Grupos Activos</p>
                    </div>
                </div>
            </div>

            <div class="mt-10">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Accesos Rápidos
                </h3>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('alumnos.create') }}" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                        {{-- Icono SVG --}}
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Registrar Nuevo Alumno
                    </a>
                    <a href="#" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                        {{-- Icono SVG --}}
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Registrar Nuevo Maestro
                    </a>
                    <a href="#" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                        {{-- Icono SVG --}}
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Registrar Nuevo Grado
                    </a>
                    <a href="#" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
                        {{-- Icono SVG --}}
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Registrar Nueva Materia
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>