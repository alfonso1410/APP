@props(['user', 'action', 'method' => 'PATCH']) {{-- Mantenemos $user y $action --}}

<form method="POST" action="{{ $action }}">
    @csrf
    @method($method) {{-- Usamos el método pasado (PATCH por defecto) --}}

    {{-- Campo oculto para identificar al usuario en caso de error de validación --}}
    <input type="hidden" name="user_id" value="{{ $user->id }}">

    {{-- Nombre --}}
    <div class="mt-4"> {{-- Añadido mt-4 para consistencia --}}
        {{-- CORREGIDO: ID y FOR --}}
        <x-input-label for="name_edit_{{ $user->id }}" :value="__('Name')" />
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

    {{-- Rol (SELECT) --}}
    <div class="mt-4">
        {{-- CORREGIDO: ID y FOR --}}
        <x-input-label for="rol_edit_{{ $user->id }}" :value="__('Rol de Usuario')" />
        <select
            id="rol_edit_{{ $user->id }}" {{-- CORREGIDO --}}
            name="rol" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
            <option value="">Seleccione un Rol</option>
            @php $currentRol = old('rol', $user->rol); @endphp
            <option value="Administrador" @selected($currentRol == 'Administrador')>Administrador</option> {{-- Ajustado 'DIRECTOR' a 'Administrador' según tu código anterior --}}
            {{-- <option value="COORDINADOR" @selected($currentRol == 'COORDINADOR')>Coordinador</option> --}} {{-- Comentado si no lo usas --}}
            <option value="Maestro" @selected($currentRol == 'Maestro')>Maestro</option> {{-- Ajustado 'MAESTRO' a 'Maestro' --}}
        </select>
        {{-- CORREGIDO: Condición para mostrar error --}}
        @if(old('user_id') == $user->id)
            <x-input-error :messages="$errors->get('rol')" class="mt-2" />
        @endif
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
        <x-input-label :value="__('Estado del Usuario')" class="mb-2" /> {{-- Quitamos el 'for' si no apunta a un ID específico --}}

        <div class="flex space-x-6"> {{-- Quitamos el ID de grupo si no es necesario --}}
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
        {{-- El botón Cancelar ya usa el ID del usuario, está correcto --}}
        <button type="button" x-on:click.prevent="$dispatch('close-modal', 'editar-usuario-{{ $user->id }}')" class="px-4 py-2 text-sm font-medium text-gray-700">
            Cancelar
        </button>

        <button type="submit" class="ms-4 pb-1 bg-princeton hover:bg-slate-900 text-white font-bold py-2 px-4 rounded">
            Actualizar Usuario
        </button>
    </div>
</form>