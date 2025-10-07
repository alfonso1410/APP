<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agregar Nuevo Alumno') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if ($errors->any())
                        {{-- ... (código de errores) ... --}}
                    @endif

                    <form method="POST" action="{{ route('alumnos.store') }}">
                        
                        {{-- Incluimos el mismo formulario parcial --}}
                        @include('alumnos._form')

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('alumnos.index') }}" class="underline text-sm ...">
                                Cancelar
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Guardar Alumno') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>