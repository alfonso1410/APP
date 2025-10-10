{{-- resources/views/grados/create.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Nuevo Grado
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            {{-- Usamos nuestro nuevo componente de formulario reutilizable --}}
            <x-form-card 
                :action="route('grados.store')" 
                :cancelRoute="route('grados.index')"
                submitText="Guardar Grado"
            >
                {{-- Aquí dentro va el contenido del slot: nuestros campos personalizados --}}

                <div>
                    <x-input-label for="nombre" value="Nombre del Grado (ej. Primero, Segundo)" />
                    <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
                    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="nivel_id" value="Nivel Educativo al que Pertenece" />
                    <select id="nivel_id" name="nivel_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                        <option value="">Seleccione un Nivel</option>
                        @foreach ($niveles as $nivel)
                            <option value="{{ $nivel->nivel_id }}" @selected(old('nivel_id') == $nivel->nivel_id)>
                                {{ $nivel->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('nivel_id')" class="mt-2" />
                </div>
                
            </x-form-card>
        </div>
    </div>
</x-app-layout>