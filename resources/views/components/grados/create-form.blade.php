{{-- resources/views/components/grados/create-form.blade.php --}}

@props(['niveles', 'view_mode' => 'regular']) {{-- 👈 1. Aceptamos el 'view_mode' --}}

<form method="POST" action="{{ route('admin.grados.store') }}">
    @csrf

    {{-- 2. Campo oculto que envía el tipo de grado según el contexto --}}
    <input type="hidden" name="tipo_grado" value="{{ $view_mode === 'regular' ? 'REGULAR' : 'EXTRA' }}">
    
    {{-- Nombre del Grado --}}
    <div>
        <x-input-label for="nombre" value="Nombre del Grado o Agrupación" />
        <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    {{-- Selector de Nivel Educativo --}}
    <div class="mt-4">
        <x-input-label for="nivel_id" value="Nivel Educativo al que Pertenece" />
        <select id="nivel_id" name="nivel_id" class="border-gray-300 rounded-md shadow-sm block mt-1 w-full" required>
            <option value="">Seleccione un Nivel</option>
            @foreach ($niveles as $nivel)
                <option value="{{ $nivel->nivel_id }}" @selected(old('nivel_id') == $nivel->nivel_id)>
                    {{ $nivel->nombre }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('nivel_id')" class="mt-2" />
    </div>

    {{-- Botones de Acción --}}
    <div class="mt-6 flex justify-end gap-4">
        <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
        <button type="submit" class="px-4 py-2 bg-princeton text-white rounded-md">Guardar</button>
    </div>
</form>