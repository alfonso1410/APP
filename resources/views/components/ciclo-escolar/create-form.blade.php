{{-- resources/views/components/ciclo-escolar/create-form.blade.php --}}

<form method="POST" action="{{ route('admin.ciclo-escolar.store') }}">
    @csrf

    {{-- Campo oculto para identificar el formulario en caso de error de validación --}}
    <input type="hidden" name="form_type" value="ciclo_escolar">

    {{-- Nombre del Ciclo --}}
    <div>
        <x-input-label for="nombre" value="Nombre del Ciclo Escolar (Ej: 2025-2026)" />
        <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    {{-- Fecha de Inicio --}}
    <div class="mt-4">
        <x-input-label for="fecha_inicio" value="Fecha de Inicio" />
        <x-text-input id="fecha_inicio" class="block mt-1 w-full" type="date" name="fecha_inicio" :value="old('fecha_inicio')" required />
        <x-input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
    </div>

    {{-- Fecha de Fin --}}
    <div class="mt-4">
        <x-input-label for="fecha_fin" value="Fecha de Fin" />
        <x-text-input id="fecha_fin" class="block mt-1 w-full" type="date" name="fecha_fin" :value="old('fecha_fin')" required />
        <x-input-error :messages="$errors->get('fecha_fin')" class="mt-2" />
        {{-- Podrías añadir validación para asegurar que fecha_fin sea posterior a fecha_inicio --}}
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