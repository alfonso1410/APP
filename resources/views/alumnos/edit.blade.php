<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Alumno') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('alumnos.update', $alumno) }}">
                        @csrf
                        @method('PUT') {{-- Directiva para indicar que es una actualización --}}

                        <div>
                            <x-input-label for="nombre" :value="__('Nombre')" />
                            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $alumno->nombre)" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="apellido_paterno" :value="__('Apellido Paterno')" />
                            <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $alumno->apellido_paterno)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="apellido_materno" :value="__('Apellido Materno')" />
                            <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $alumno->apellido_materno)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                            <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento', $alumno->fecha_nacimiento)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="curp" :value="__('CURP')" />
                            <x-text-input id="curp" class="block mt-1 w-full" type="text" name="curp" :value="old('curp', $alumno->curp)" required />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('alumnos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                Cancelar
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Actualizar Alumno') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>