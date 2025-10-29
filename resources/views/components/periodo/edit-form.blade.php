{{-- resources/views/components/periodo/edit-form.blade.php --}}
@props(['periodo', 'isFiltered' => false])

<form method="POST" action="{{ route('admin.periodos.update', $periodo) }}">
    @csrf
    @method('PUT')

    {{-- Input oculto para saber qué modal reabrir si falla la validación --}}
    <input type="hidden" name="periodo_id" value="{{ $periodo->periodo_id }}">
    {{-- Input oculto para saber si redirigir a la vista filtrada --}}
    @if($isFiltered)
        <input type="hidden" name="redirect_back_filter" value="1">
    @endif


    {{-- Nombre del Periodo --}}
    <div>
        <x-input-label for="nombre_edit_{{ $periodo->periodo_id }}" value="Nombre del Periodo" />
        <x-text-input id="nombre_edit_{{ $periodo->periodo_id }}" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $periodo->nombre)" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    {{-- Fecha de Inicio --}}
    <div class="mt-4">
        <x-input-label for="fecha_inicio_edit_{{ $periodo->periodo_id }}" value="Fecha de Inicio" />
        <x-text-input id="fecha_inicio_edit_{{ $periodo->periodo_id }}" class="block mt-1 w-full" type="date" name="fecha_inicio" :value="old('fecha_inicio', $periodo->fecha_inicio)" required />
        <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
         @if($periodo->cicloEscolar)
        <p class="text-xs text-gray-500 mt-1">Ciclo: {{ \Carbon\Carbon::parse($periodo->cicloEscolar->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodo->cicloEscolar->fecha_fin)->format('d/m/Y') }}</p>
        @endif
    </div>

    {{-- Fecha de Fin --}}
    <div class="mt-4">
        <x-input-label for="fecha_fin_edit_{{ $periodo->periodo_id }}" value="Fecha de Fin" />
        <x-text-input id="fecha_fin_edit_{{ $periodo->periodo_id }}" class="block mt-1 w-full" type="date" name="fecha_fin" :value="old('fecha_fin', $periodo->fecha_fin)" required />
        <x-input-error :messages="$errors->get('fecha_fin')" class="mt-2" />
    </div>

    {{-- Estado --}}
    <div class="mt-4">
        <x-input-label for="estado_edit_{{ $periodo->periodo_id }}" value="Estado" />
        <select id="estado_edit_{{ $periodo->periodo_id }}" name="estado" class="border-gray-300 rounded-md shadow-sm block mt-1 w-full" required>
            <option value="ABIERTO" @selected(old('estado', $periodo->estado) == 'ABIERTO')>ABIERTO</option>
            <option value="CERRADO" @selected(old('estado', $periodo->estado) == 'CERRADO')>CERRADO</option>
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
    </div>


    {{-- Botones de Acción --}}
    <div class="mt-6 flex justify-end gap-4">
        <x-secondary-button x-on:click="$dispatch('close')">
            Cancelar
        </x-secondary-button>
        <x-primary-button>
            Actualizar Periodo
        </x-primary-button>
    </div>
</form>