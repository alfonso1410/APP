{{-- resources/views/components/periodo/create-form.blade.php --}}
@props(['cicloActivo'])

<form method="POST" action="{{ route('admin.periodos.store') }}">
    @csrf

    {{-- Campo oculto para identificar el formulario --}}
    <input type="hidden" name="form_type" value="periodo">

    {{-- Input oculto con el ID del ciclo activo --}}
    <input type="hidden" name="ciclo_escolar_id" value="{{ $cicloActivo->ciclo_escolar_id }}">

    {{-- Nombre del Periodo --}}
    <div>
        <x-input-label for="nombre_periodo" value="Nombre del Periodo (Ej: Trimestre 1, Parcial 1)" />
        <x-text-input id="nombre_periodo" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    {{-- Fecha de Inicio --}}
    <div class="mt-4">
        <x-input-label for="fecha_inicio_periodo" value="Fecha de Inicio" />
        <x-text-input id="fecha_inicio_periodo" class="block mt-1 w-full" type="date" name="fecha_inicio" :value="old('fecha_inicio')" required />
        <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">Debe estar dentro de las fechas del ciclo escolar ({{ \Carbon\Carbon::parse($cicloActivo->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($cicloActivo->fecha_fin)->format('d/m/Y') }}).</p>
    </div>

    {{-- Fecha de Fin --}}
    <div class="mt-4">
        <x-input-label for="fecha_fin_periodo" value="Fecha de Fin" />
        <x-text-input id="fecha_fin_periodo" class="block mt-1 w-full" type="date" name="fecha_fin" :value="old('fecha_fin')" required />
        <x-input-error :messages="$errors->get('fecha_fin')" class="mt-2" />
    </div>

    {{-- Estado (Generalmente 'ABIERTO' por defecto al crear) --}}
    {{-- <div class="mt-4">
        <x-input-label for="estado_periodo" value="Estado Inicial" />
        <select id="estado_periodo" name="estado" class="border-gray-300 rounded-md shadow-sm block mt-1 w-full" required>
            <option value="ABIERTO" @selected(old('estado', 'ABIERTO') == 'ABIERTO')>ABIERTO</option>
            <option value="CERRADO" @selected(old('estado') == 'CERRADO')>CERRADO</option>
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
    </div> --}}


    {{-- Botones de Acción --}}
    <div class="mt-6 flex justify-end gap-4">
        <x-secondary-button x-on:click="$dispatch('close')">
            Cancelar
        </x-secondary-button>
        <x-primary-button>
            Guardar Periodo
        </x-primary-button>
    </div>
</form>