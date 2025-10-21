@props(['user', 'action', 'method' => 'PATCH']) {{-- Recibimos el objeto $user (que representa al maestro) --}}

<form method="POST" action="{{ $action }}">
    @csrf
    @method($method)

    {{-- Campo oculto para identificar al maestro en caso de error de validación --}}
    <input type="hidden" name="user_id" value="{{ $user->id }}">

    {{-- Nombre --}}
    <div class="mt-4"> {{-- Añadido mt-4 --}}
        {{-- CORREGIDO: ID y FOR --}}
        <x-input-label for="name_edit_{{ $user->id }}" :value="__('Nombre')" />
        <x-text-input
            id="name_edit_{{ $user->id }}" {{-- CORREGIDO --}}
            class="block mt-1 w-full"
            type="text"
            name="name"
            :value="old('name', $user->name)"
            required autofocus
            autocomplete="name"
        />
        {{-- CORREGIDO: Condición para mostrar error --}}
        @if(old('user_id') == $user->id)
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        @endif
    </div>

    {{-- Apellido Paterno --}}
    <div class="mt-4">
        {{-- CORREGIDO: ID y FOR --}}
        <x-input-label for="apellido_paterno_edit_{{ $user->id }}" :value="__('Apellido Paterno')" />
        <x-text-input
            id="apellido_paterno_edit_{{ $user->id }}" {{-- CORREGIDO --}}
            class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $user->apellido_paterno)" required autocomplete="apellido-paterno" {{-- Corregido autocomplete --}}
        />
        {{-- CORREGIDO: Condición para mostrar error --}}
        @if(old('user_id') == $user->id)
            <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
        @endif
    </div>

    {{-- Apellido Materno --}}
    <div class="mt-4">
        {{-- CORREGIDO: ID y FOR --}}
        <x-input-label for="apellido_materno_edit_{{ $user->id }}" :value="__('Apellido Materno')" />
        <x-text-input
            id="apellido_materno_edit_{{ $user->id }}" {{-- CORREGIDO --}}
            class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $user->apellido_materno)" autocomplete="apellido-materno"
        />
        {{-- CORREGIDO: Condición para mostrar error --}}
        @if(old('user_id') == $user->id)
            <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
        @endif
    </div>

    {{-- Rol (Deshabilitado, pero con ID único por consistencia) --}}
    <div class="mt-4">
        {{-- CORREGIDO: ID y FOR --}}
        <x-input-label for="rol_edit_{{ $user->id }}" :value="__('Rol de Usuario')" />
        <x-text-input
            id="rol_edit_{{ $user->id }}" {{-- CORREGIDO --}}
            class="block mt-1 w-full bg-gray-100" type="text" name="rol" value="Maestro" readonly disabled {{-- Usar readonly además de disabled --}}
        />
        {{-- No necesitamos error para un campo deshabilitado --}}
    </div>

    {{-- Email Address --}}
    <div class="mt-4">
        {{-- CORREGIDO: ID y FOR --}}
        <x-input-label for="email_edit_{{ $user->id }}" :value="__('Email')" />
        <x-text-input
            id="email_edit_{{ $user->id }}" {{-- CORREGIDO --}}
            class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username"
        />
        {{-- CORREGIDO: Condición para mostrar error --}}
        @if(old('user_id') == $user->id)
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        @endif
    </div>

    {{-- Activo/Inactivo (Radio Button) --}}
    <div class="mt-4">
        <x-input-label :value="__('Estado del Usuario')" class="mb-2" /> {{-- Quitamos 'for' --}}

        <div class="flex space-x-6"> {{-- Quitamos ID --}}
            @php $currentActivo = old('activo', $user->activo); @endphp

            {{-- CORREGIDO: ID y FOR --}}
            <label for="activo_1_edit_{{ $user->id }}" class="inline-flex items-center">
                <input
                    id="activo_1_edit_{{ $user->id }}" {{-- CORREGIDO --}}
                    type="radio" name="activo" value="1"
                    {{ $currentActivo == 1 ? 'checked' : '' }}
                    class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                />
                <span class="ms-2 text-sm text-gray-600">{{ __('Activo') }}</span>
            </label>

            {{-- CORREGIDO: ID y FOR --}}
            <label for="activo_0_edit_{{ $user->id }}" class="inline-flex items-center">
                <input
                    id="activo_0_edit_{{ $user->id }}" {{-- CORREGIDO --}}
                    type="radio" name="activo" value="0"
                    {{ $currentActivo == 0 ? 'checked' : '' }}
                    class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                />
                <span class="ms-2 text-sm text-gray-600">{{ __('Inactivo') }}</span>
            </label>
        </div>
        {{-- CORREGIDO: Condición para mostrar error --}}
        @if(old('user_id') == $user->id)
            <x-input-error :messages="$errors->get('activo')" class="mt-2" />
        @endif
    </div>

    <div class="flex items-center justify-end mt-4">
        {{-- El botón Cancelar ya usa el ID del usuario en el dispatch, está correcto --}}
        <button type="button" x-on:click.prevent="$dispatch('close-modal', 'editar-maestro-{{ $user->id }}')" class="px-4 py-2 text-sm font-medium text-gray-700">
            Cancelar
        </button>

        <button type="submit" class="ms-4 pb-1 bg-princeton hover:bg-slate-900 text-white font-bold py-2 px-4 rounded">
            Actualizar Maestro
        </button>
    </div>
</form>