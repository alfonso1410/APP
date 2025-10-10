@props(['user', 'action', 'method' => 'PATCH']) {{-- 1. Recibimos el objeto $user --}}

<form method="POST" action="{{ $action }}">
    @csrf
    @method($method) {{-- 2. Siempre inyectamos PATCH/PUT --}}
    
    {{-- Nombre --}}
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input 
            id="name" 
            class="block mt-1 w-full" 
            type="text" 
            name="name" 
            :value="old('name', $user->name)" {{-- 3. Llenamos con $user->name --}}
            required autofocus 
            autocomplete="name" 
        />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    {{-- Apellido Paterno --}}
    <div class="mt-4">
        <x-input-label for="apellido_paterno" :value="__('Apellido Paterno')" />
        <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $user->apellido_paterno)" required autocomplete="apelldio-paerno" />
        <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
    </div>

    {{-- Apellido Materno --}}
    <div class="mt-4">
        <x-input-label for="apellido_materno" :value="__('Apellido Materno')" />
        <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $user->apellido_materno)" autocomplete="apellido-materno" />
        <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
    </div>

    {{-- Rol (SELECT) --}}
    <div class="mt-4">
        <x-input-label for="rol" :value="__('Rol de Usuario')" />
        <select id="rol" name="rol" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
            <option value="">Seleccione un Rol</option>
            {{-- Usamos $user->rol como valor seleccionado --}}
            @php $currentRol = old('rol', $user->rol); @endphp
            <option value="DIRECTOR" @selected($currentRol == 'DIRECTOR')>Director</option>
            <option value="COORDINADOR" @selected($currentRol == 'COORDINADOR')>Coordinador</option>
            <option value="MAESTRO" @selected($currentRol == 'MAESTRO')>Maestro</option>
        </select>
        <x-input-error :messages="$errors->get('rol')" class="mt-2" />
    </div>

    {{-- Email Address --}}
    <div class="mt-4">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

 

    {{-- Activo/Inactivo (Radio Button) --}}
    <div class="mt-4">
        <x-input-label for="activo_group" :value="__('Estado del Usuario')" class="mb-2" />

        <div id="activo_group" class="flex space-x-6">
            {{-- Obtenemos el valor actual: old o $user->activo --}}
            @php $currentActivo = old('activo', $user->activo); @endphp
            
            <label for="activo_1" class="inline-flex items-center">
                <input id="activo_1" type="radio" name="activo" value="1" 
                    {{ $currentActivo == 1 ? 'checked' : '' }}
                    class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                />
                <span class="ms-2 text-sm text-gray-600">{{ __('Activo') }}</span>
            </label>

            <label for="activo_0" class="inline-flex items-center">
                <input id="activo_0" type="radio" name="activo" value="0" 
                    {{ $currentActivo == 0 ? 'checked' : '' }}
                    class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                />
                <span class="ms-2 text-sm text-gray-600">{{ __('Inactivo') }}</span>
            </label>
        </div>
        <x-input-error :messages="$errors->get('activo')" class="mt-2" />
    </div>

    <div class="flex items-center justify-end mt-4">
        {{-- Botón para cerrar la modal, ahora es 'editar-usuario-[ID]' --}}
        <button type="button" x-on:click.prevent="$dispatch('close-modal', 'editar-usuario-{{ $user->id }}')" class="px-4 py-2 text-sm font-medium text-gray-700">
            Cancelar
        </button>

        <button type="submit" class="ms-4 pb-1 bg-princeton hover:bg-slate-900 text-white font-bold py-2 px-4 rounded">
            Actualizar Usuario {{-- Texto específico de edición --}}
        </button>
    </div>
</form>