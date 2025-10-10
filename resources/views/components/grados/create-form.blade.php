{{-- resources/views/components/grados/create-form.blade.php --}}

@props(['niveles']) {{-- Le decimos al componente que espera recibir la variable 'niveles' --}}

<form method="POST" action="{{ route('grados.store') }}">
    @csrf
    
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

    {{-- Botones de Acción de la Modal --}}
    <div class="mt-6 flex justify-end gap-4">
        {{-- Usamos Alpine para que el botón Cancelar cierre la modal --}}
        <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-300 rounded-md transition-colors">
            Cancelar
        </button>
        
        <button type="submit" class="bg-princeton hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Guardar Grado
        </button>
    </div>
</form>