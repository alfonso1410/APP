@props(['ciclo'])

{{--*
 * 1. DEFINIMOS LA LÓGICA DE VALIDACIÓN
 *
 * Esta variable $isThisFormFailed será 'true' SOLAMENTE si:
 * 1. Hay errores de validación en la sesión ($errors->any()).
 * 2. El 'ciclo_escolar_id' que viene en 'old()' coincide con el ID de este ciclo.
 *
 * Esto evita que los errores/old() del modal 'CREAR' contaminen este modal 'EDITAR'.
*--}}
@php
    $isThisFormFailed = $errors->any() && old('ciclo_escolar_id') == $ciclo->ciclo_escolar_id;
@endphp

<form method="POST" action="{{ route('admin.ciclo-escolar.update', $ciclo) }}">
    @csrf
    @method('PUT')

    {{-- Input oculto para saber qué modal reabrir si falla la validación --}}
    <input type="hidden" name="ciclo_escolar_id" value="{{ $ciclo->ciclo_escolar_id }}">

    {{-- Nombre del Ciclo --}}
    <div>
        <x-input-label for="nombre_edit_{{ $ciclo->ciclo_escolar_id }}" value="Nombre del Ciclo Escolar" />
        <x-text-input 
            id="nombre_edit_{{ $ciclo->ciclo_escolar_id }}" 
            class="block mt-1 w-full" 
            type="text" 
            name="nombre" 
            {{--* 2. LÓGICA DE VALOR CORREGIDA *--}}
            :value="$isThisFormFailed ? old('nombre') : $ciclo->nombre" 
            required 
            autofocus 
        />
        {{--* 3. LÓGICA DE ERROR CORREGIDA *--}}
        <x-input-error :messages="$isThisFormFailed ? $errors->get('nombre') : []" class="mt-2" />
    </div>

    {{-- Fecha de Inicio --}}
    <div class="mt-4">
        <x-input-label for="fecha_inicio_edit_{{ $ciclo->ciclo_escolar_id }}" value="Fecha de Inicio" />
        {{--* 4. [BONUS] Formateamos la fecha a Y-m-d, requerido por el input type="date" *--}}
        <x-text-input 
            id="fecha_inicio_edit_{{ $ciclo->ciclo_escolar_id }}" 
            class="block mt-1 w-full" 
            type="date" 
            name="fecha_inicio" 
            :value="$isThisFormFailed ? old('fecha_inicio') : \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('Y-m-d')" 
            required 
        />
        <x-input-error :messages="$isThisFormFailed ? $errors->get('fecha_inicio') : []" class="mt-2" />
    </div>

    {{-- Fecha de Fin --}}
    <div class="mt-4">
        <x-input-label for="fecha_fin_edit_{{ $ciclo->ciclo_escolar_id }}" value="Fecha de Fin" />
        <x-text-input 
            id="fecha_fin_edit_{{ $ciclo->ciclo_escolar_id }}" 
            class="block mt-1 w-full" 
            type="date" 
            name="fecha_fin" 
            :value="$isThisFormFailed ? old('fecha_fin') : \Carbon\Carbon::parse($ciclo->fecha_fin)->format('Y-m-d')" 
            required 
        />
        <x-input-error :messages="$isThisFormFailed ? $errors->get('fecha_fin') : []" class="mt-2" />
    </div>

    {{-- Estado --}}
    <div class="mt-4">
        <x-input-label for="estado_edit_{{ $ciclo->ciclo_escolar_id }}" value="Estado" />
        <select id="estado_edit_{{ $ciclo->ciclo_escolar_id }}" name="estado" class="border-gray-300 rounded-md shadow-sm block mt-1 w-full" required>
            {{--* 5. LÓGICA DE SELECT CORREGIDA (más limpia) *--}}
            @php
                $selectedEstado = $isThisFormFailed ? old('estado') : $ciclo->estado;
            @endphp
            <option value="ACTIVO" @selected($selectedEstado == 'ACTIVO')>ACTIVO</option>
            <option value="CERRADO" @selected($selectedEstado == 'CERRADO')>CERRADO</option>
        </select>
        <x-input-error :messages="$isThisFormFailed ? $errors->get('estado') : []" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">Nota: Al marcar un ciclo como ACTIVO, los demás se marcarán automáticamente como CERRADO.</p>
    </div>

    {{-- Botones de Acción --}}
    <div class="mt-6 flex justify-end gap-4">
        <x-secondary-button x-on:click="$dispatch('close')">
            Cancelar
        </x-secondary-button>
        <x-primary-button>
            Actualizar Ciclo Escolar
        </x-primary-button>
    </div>
</form>