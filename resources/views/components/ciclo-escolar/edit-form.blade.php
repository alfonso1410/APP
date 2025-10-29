{{-- resources/views/components/ciclo-escolar/edit-form.blade.php --}}
@props(['ciclo'])

<form method="POST" action="{{ route('admin.ciclo-escolar.update', $ciclo) }}">
    @csrf
    @method('PUT')

    {{-- Input oculto para saber qué modal reabrir si falla la validación --}}
    <input type="hidden" name="ciclo_escolar_id" value="{{ $ciclo->ciclo_escolar_id }}">

    {{-- Nombre del Ciclo --}}
    <div>
        <x-input-label for="nombre_edit_{{ $ciclo->ciclo_escolar_id }}" value="Nombre del Ciclo Escolar" />
        <x-text-input id="nombre_edit_{{ $ciclo->ciclo_escolar_id }}" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $ciclo->nombre)" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    {{-- Fecha de Inicio --}}
    <div class="mt-4">
        <x-input-label for="fecha_inicio_edit_{{ $ciclo->ciclo_escolar_id }}" value="Fecha de Inicio" />
        <x-text-input id="fecha_inicio_edit_{{ $ciclo->ciclo_escolar_id }}" class="block mt-1 w-full" type="date" name="fecha_inicio" :value="old('fecha_inicio', $ciclo->fecha_inicio)" required />
        <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
    </div>

    {{-- Fecha de Fin --}}
    <div class="mt-4">
        <x-input-label for="fecha_fin_edit_{{ $ciclo->ciclo_escolar_id }}" value="Fecha de Fin" />
        <x-text-input id="fecha_fin_edit_{{ $ciclo->ciclo_escolar_id }}" class="block mt-1 w-full" type="date" name="fecha_fin" :value="old('fecha_fin', $ciclo->fecha_fin)" required />
        <x-input-error :messages="$errors->get('fecha_fin')" class="mt-2" />
    </div>

    {{-- Estado --}}
    <div class="mt-4">
        <x-input-label for="estado_edit_{{ $ciclo->ciclo_escolar_id }}" value="Estado" />
        <select id="estado_edit_{{ $ciclo->ciclo_escolar_id }}" name="estado" class="border-gray-300 rounded-md shadow-sm block mt-1 w-full" required>
            <option value="ACTIVO" @selected(old('estado', $ciclo->estado) == 'ACTIVO')>ACTIVO</option>
            <option value="CERRADO" @selected(old('estado', $ciclo->estado) == 'CERRADO')>CERRADO</option>
        </select>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
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