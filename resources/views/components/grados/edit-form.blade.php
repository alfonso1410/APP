{{-- resources/views/components/grados/edit-form.blade.php --}}

@props(['grado', 'niveles'])

<form method="POST" action="{{ route('admin.grados.update', $grado) }}">
    @csrf
    @method('PUT') {{-- Usamos el método PUT para actualizar --}}

    {{-- Input oculto para saber qué modal reabrir si falla la validación --}}
    <input type="hidden" name="grado_id" value="{{ $grado->grado_id }}">

    <div>
        <x-input-label for="nombre_edit_{{ $grado->grado_id }}" value="Nombre del Grado" />
        <x-text-input 
            id="nombre_edit_{{ $grado->grado_id }}" 
            class="block mt-1 w-full" 
            type="text" 
            name="nombre" 
            {{-- Usa el valor 'old' si existe, si no, el valor actual del grado --}}
            :value="old('nombre', $grado->nombre)" 
            required 
            autofocus 
        />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="nivel_id_edit_{{ $grado->grado_id }}" value="Nivel Educativo" />
        <select 
            id="nivel_id_edit_{{ $grado->grado_id }}" 
            name="nivel_id" 
            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" 
            required
        >
            @foreach ($niveles as $nivel)
                <option value="{{ $nivel->nivel_id }}" 
                    {{-- Selecciona la opción correcta basándose en 'old' o el valor actual --}}
                    @selected(old('nivel_id', $grado->nivel_id) == $nivel->nivel_id)
                >
                    {{ $nivel->nombre }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('nivel_id')" class="mt-2" />
    </div>

    {{-- Botones de Acción --}}
    <div class="mt-6 flex justify-end gap-4">
        <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-300 rounded-md transition-colors">
            Cancelar
        </button>
        
        <button type="submit" class="bg-princeton hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Actualizar Grado
        </button>
    </div>
</form>