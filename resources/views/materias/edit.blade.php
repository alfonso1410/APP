<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Materia: {{ $materia->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('materias.update', $materia) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="nombre" :value="__('Nombre de la Materia')" />
                            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $materia->nombre)" required autofocus />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-secondary-link-button href="{{ route('materias.index') }}">
                                Cancelar
                            </x-secondary-link-button>
                            <x-primary-button class="ml-4">
                                Actualizar Materia
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>