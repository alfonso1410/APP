@props(['action', 'method' => 'POST'])

{{-- El método real para Laravel (si no es GET/POST) --}}
<form method="POST" action="{{ $action }}">
    @csrf
    
    {{-- Si el método es PUT, PATCH o DELETE, Laravel necesita esta directiva --}}
    @if (strtoupper($method) !== 'POST' && strtoupper($method) !== 'GET')
        @method($method)
    @endif

    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

     <div class="mt-4">
            <x-input-label for="apellido_paterno" :value="__('Apellido Paterno')" />
            <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required autocomplete="apelldio-paerno" />
            <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="apellido_materno" :value="__('Apellido Materno')" />
            <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" autocomplete="apellido-materno" />
            <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
        </div>

         <div class="mt-4">
            <x-input-label for="rol" :value="__('Rol de Usuario')" />
    
            <select id="rol" name="rol" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                <option value="">Seleccione un Rol</option>
                <option value="DIRECTOR" @selected(old('rol') == 'DIRECTOR')>Director</option>
                <option value="COORDINADOR" @selected(old('rol') == 'COORDINADOR')>Coordinador</option>
                <option value="MAESTRO" @selected(old('rol') == 'MAESTRO')>Maestro</option>
            </select>
    
            <x-input-error :messages="$errors->get('rol')" class="mt-2" />
        </div>

        
        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- activo/inactivo-->
      <div class="mt-4">
    <x-input-label for="activo_group" :value="__('Estado del Usuario')" class="mb-2" />

    <div id="activo_group" class="flex space-x-6">
        
        <label for="activo_1" class="inline-flex items-center">
            <input 
                id="activo_1" 
                type="radio" 
                name="activo"             {{-- ¡CAMBIADO a activo! --}}
                value="1" 
                {{-- Marcamos 'Activo' como predeterminado o si viene de un old('activo') --}}
                {{ old('activo', 1) == 1 ? 'checked' : '' }} {{-- ¡CAMBIADO a activo! --}}
                class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
            />
            <span class="ms-2 text-sm text-gray-600">{{ __('Activo') }}</span>
        </label>

        <label for="activo_0" class="inline-flex items-center">
            <input 
                id="activo_0" 
                type="radio" 
                name="activo"             {{-- ¡CAMBIADO a activo! --}}
                value="0" 
                {{ old('activo') == 0 ? 'checked' : '' }}         {{-- ¡CAMBIADO a activo! --}}
                class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
            />
            <span class="ms-2 text-sm text-gray-600">{{ __('Inactivo') }}</span>
        </label>
    </div>

    {{-- Mostrar errores de validación si existen para 'activo' --}}
    <x-input-error :messages="$errors->get('activo')" class="mt-2" /> {{-- ¡CAMBIADO a activo! --}}
</div>
    {{-- Mostrar errores de validación si existen para 'is_active' --}}
    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
</div>
<div class="flex items-center justify-end mt-6 mb-2 mr-2"> 
        {{-- Aumentamos el margen superior a mt-6 para separarlo del formulario --}}

        {{-- Botón Cancelar (Debe tener padding para igualar altura al otro botón) --}}
        <button type="button" 
                x-on:click.prevent="$dispatch('close-modal', 'agregar-usuario')" 
                class="px-4 py-2 bg-princeton text-sm font-semibold text-white hover:bg-gray-100 rounded-md transition-colors"
        >
            Cancelar
        </button>

        {{-- Botón Guardar (Eliminamos el 'pb-1' para evitar desalineación) --}}
        <button type="submit" 
                class="ms-4 bg-princeton hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
            Guardar Usuario
        </button>
    </div>
</form>