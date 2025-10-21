{{-- Definimos un prefijo opcional para los IDs y aceptamos $alumno --}}
@props(['alumno' => null, 'prefix' => ''])

@csrf

{{-- Campo oculto para mantener el filtro de nivel al redirigir --}}
<input type="hidden" name="current_nivel_id" value="{{ request()->input('nivel', 0) }}">

{{-- Campo oculto para identificar al alumno en edición (para errores) --}}
@if ($alumno)
    <input type="hidden" name="alumno_id_error_key" value="{{ $alumno->alumno_id }}">
@endif

{{-- Nombres --}}
{{-- CORREGIDO: Añadido mt-4 al primer div --}}
<div class="mt-4">
    <x-input-label for="{{ $prefix }}nombres" :value="__('Nombre(s)')" />
    <x-text-input
        id="{{ $prefix }}nombres"
        class="block mt-1 w-full" type="text" name="nombres" :value="old('nombres', $alumno?->nombres)" required autofocus />
    @if((!$alumno && !$errors->update->any()) || old('alumno_id_error_key') == $alumno?->alumno_id)
        <x-input-error :messages="$errors->{$alumno ? 'update' : 'store'}->get('nombres')" class="mt-2" />
    @endif
</div>

{{-- Apellido Paterno --}}
<div class="mt-4">
    <x-input-label for="{{ $prefix }}apellido_paterno" :value="__('Apellido Paterno')" />
    <x-text-input
        id="{{ $prefix }}apellido_paterno"
        class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $alumno?->apellido_paterno)" required />
    @if((!$alumno && !$errors->update->any()) || old('alumno_id_error_key') == $alumno?->alumno_id)
        <x-input-error :messages="$errors->{$alumno ? 'update' : 'store'}->get('apellido_paterno')" class="mt-2" />
    @endif
</div>

{{-- Apellido Materno --}}
<div class="mt-4">
    <x-input-label for="{{ $prefix }}apellido_materno" :value="__('Apellido Materno')" />
    <x-text-input
        id="{{ $prefix }}apellido_materno"
        class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $alumno?->apellido_materno)" required />
    @if((!$alumno && !$errors->update->any()) || old('alumno_id_error_key') == $alumno?->alumno_id)
        <x-input-error :messages="$errors->{$alumno ? 'update' : 'store'}->get('apellido_materno')" class="mt-2" />
    @endif
</div>

{{-- Fecha de Nacimiento --}}
<div class="mt-4">
    <x-input-label for="{{ $prefix }}fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
    <x-text-input
        id="{{ $prefix }}fecha_nacimiento"
        class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento', $alumno?->fecha_nacimiento)" required />
    @if((!$alumno && !$errors->update->any()) || old('alumno_id_error_key') == $alumno?->alumno_id)
        <x-input-error :messages="$errors->{$alumno ? 'update' : 'store'}->get('fecha_nacimiento')" class="mt-2" />
    @endif
</div>

{{-- CURP --}}
<div class="mt-4">
    <x-input-label for="{{ $prefix }}curp" :value="__('CURP')" />
    <x-text-input
        id="{{ $prefix }}curp"
        class="block mt-1 w-full uppercase" type="text" name="curp" :value="old('curp', $alumno?->curp)" required pattern="[A-Z]{4}[0-9]{6}[H,M][A-Z]{5}[A-Z0-9]{2}" title="Formato CURP inválido" maxlength="18" />
    @if((!$alumno && !$errors->update->any()) || old('alumno_id_error_key') == $alumno?->alumno_id)
        <x-input-error :messages="$errors->{$alumno ? 'update' : 'store'}->get('curp')" class="mt-2" />
    @endif
</div>

{{-- Estado Alumno (SELECT para edición, HIDDEN para creación) --}}
@if ($alumno)
<div class="mt-4">
    <x-input-label for="{{ $prefix }}estado_alumno" :value="__('Estado del Alumno')" />
    <select
        name="estado_alumno"
        id="{{ $prefix }}estado_alumno"
        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

        <option value="ACTIVO" {{ old('estado_alumno', $alumno->estado_alumno) === 'ACTIVO' ? 'selected' : '' }}>
            Activo
        </option>
        <option value="INACTIVO" {{ old('estado_alumno', $alumno->estado_alumno) === 'INACTIVO' ? 'selected' : '' }}>
            Inactivo
        </option>
    </select>
    @if(old('alumno_id_error_key') == $alumno->alumno_id)
        <x-input-error :messages="$errors->update->get('estado_alumno')" class="mt-2" />
    @endif
</div>
@else
<input type="hidden" name="estado_alumno" value="ACTIVO">
@endif