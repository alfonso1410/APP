{{--*
 * 1. DEFINIMOS LA LÓGICA DE VALIDACIÓN
 *
 * Esta variable $isThisFormFailed será 'true' SOLAMENTE si:
 * 1. Hay errores de validación en la sesión ($errors->any()).
 * 2. El 'form_type' que viene en 'old()' coincide con 'ciclo_escolar'.
 *
 * Esto evita que los errores/old() del modal 'EDITAR' contaminen este modal 'CREAR'.
*--}}
@php
    $isThisFormFailed = $errors->any() && old('form_type') === 'ciclo_escolar';
@endphp

<form method="POST" action="{{ route('admin.ciclo-escolar.store') }}">
    @csrf

    {{-- Campo oculto para identificar el formulario en caso de error de validación --}}
    <input type="hidden" name="form_type" value="ciclo_escolar">

    {{-- Nombre del Ciclo --}}
    <div>
        <x-input-label for="nombre_create" value="Nombre del Ciclo Escolar (Ej: 2025-2026)" />
        <x-text-input 
            id="nombre_create" {{--* ID ÚNICO *--}}
            class="block mt-1 w-full" 
            type="text" 
            name="nombre" 
            {{--* 2. LÓGICA DE VALOR CORREGIDA *--}}
            :value="$isThisFormFailed ? old('nombre') : ''" 
            required 
            autofocus 
        />
        {{--* 3. LÓGICA DE ERROR CORREGIDA *--}}
        <x-input-error :messages="$isThisFormFailed ? $errors->get('nombre') : []" class="mt-2" />
    </div>

    {{-- Fecha de Inicio --}}
    <div class="mt-4">
        <x-input-label for="fecha_inicio_create" value="Fecha de Inicio" />
        <x-text-input 
            id="fecha_inicio_create" {{--* ID ÚNICO *--}}
            class="block mt-1 w-full" 
            type="date" 
            name="fecha_inicio" 
            :value="$isThisFormFailed ? old('fecha_inicio') : ''" 
            required 
        />
        <x-input-error :messages="$isThisFormFailed ? $errors->get('fecha_inicio') : []" class="mt-2" />
    </div>

    {{-- Fecha de Fin --}}
    <div class="mt-4">
        <x-input-label for="fecha_fin_create" value="Fecha de Fin" />
        <x-text-input 
            id="fecha_fin_create" {{--* ID ÚNICO *--}}
            class="block mt-1 w-full" 
            type="date" 
            name="fecha_fin" 
            :value="$isThisFormFailed ? old('fecha_fin') : ''" 
            required 
        />
        <x-input-error :messages="$isThisFormFailed ? $errors->get('fecha_fin') : []" class="mt-2" />
    </div>

    {{-- Botones de Acción --}}
    <div class="mt-6 flex justify-end gap-4">
        {{-- Cierra el modal --}}
        <x-secondary-button x-on:click="$dispatch('close')">
            Cancelar
        </x-secondary-button>

        <x-primary-button>
            Guardar Ciclo Escolar
        </x-primary-button>
    </div>
</form>