{{--
    Este formulario parcial es reutilizable tanto para crear como para editar alumnos.
    Utiliza el operador de fusión de null (??) para manejar el caso de la creación,
    donde la variable $alumno no existe.
--}}

@csrf

<div>
    <x-input-label for="nombres" :value="__('Nombre(s)')" />
    <x-text-input id="nombres" class="block mt-1 w-full" type="text" name="nombres" :value="old('nombres', $alumno->nombres ?? '')" required autofocus />
</div>

<div class="mt-4">
    <x-input-label for="apellido_paterno" :value="__('Apellido Paterno')" />
    <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $alumno->apellido_paterno ?? '')" required />
</div>

<div class="mt-4">
    <x-input-label for="apellido_materno" :value="__('Apellido Materno')" />
    <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $alumno->apellido_materno ?? '')" required />
</div>

<div class="mt-4">
    <x-input-label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
    <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '')" required />
</div>

<div class="mt-4">
    <x-input-label for="curp" :value="__('CURP')" />
    <x-text-input id="curp" class="block mt-1 w-full uppercase" type="text" name="curp" :value="old('curp', $alumno->curp ?? '')" required maxlength="18" />
</div>

{{-- Campo 'estado_alumno' visible solo en el formulario de edición --}}
@isset($alumno)
<div class="mt-4">
    <x-input-label for="estado_alumno" :value="__('Estado del Alumno')" />
    <select name="estado_alumno" id="estado_alumno" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="1" {{ old('estado_alumno', $alumno->estado_alumno) == 1 ? 'selected' : '' }}>Activo</option>
        <option value="0" {{ old('estado_alumno', $alumno->estado_alumno) == 0 ? 'selected' : '' }}>Inactivo</option>
    </select>
</div>
@else
{{-- En el formulario de creación, se envía el estado como activo por defecto --}}
<input type="hidden" name="estado_alumno" value="1">
@endisset