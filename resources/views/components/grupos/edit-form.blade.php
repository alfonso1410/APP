{{-- resources/views/components/grupos/edit-form.blade.php --}}
@props(['grupo'])

<form method="POST" action="{{ route('admin.grupos.update', $grupo) }}">
    @csrf
    @method('PUT')

    {{-- Input oculto para reabrir modal si falla validación --}}
    <input type="hidden" name="grupo_id" value="{{ $grupo->grupo_id }}">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Nombre del Grupo --}}
        <div>
            <x-input-label for="nombre_grupo_edit_{{ $grupo->grupo_id }}" value="Nombre del Grupo" />
            <x-text-input id="nombre_grupo_edit_{{ $grupo->grupo_id }}" name="nombre_grupo" type="text" :value="old('nombre_grupo', $grupo->nombre_grupo)" required autofocus class="mt-1 block w-full"/>
             <x-input-error :messages="$errors->get('nombre_grupo')" class="mt-2" />
        </div>

        {{-- INICIO MODIFICACIÓN: Mostrar Ciclo Escolar (no editable) --}}
        <div>
            <x-input-label for="ciclo_escolar_display_edit_{{ $grupo->grupo_id }}" value="Ciclo Escolar" />
            <x-text-input id="ciclo_escolar_display_edit_{{ $grupo->grupo_id }}" type="text"
                          :value="$grupo->cicloEscolar->nombre ?? 'N/A'" {{-- Carga la relación --}}
                          readonly disabled
                          class="mt-1 block w-full bg-gray-100 text-gray-500" />
            <p class="text-xs text-gray-500 mt-1">El ciclo escolar no se puede cambiar al editar.</p>
        </div>
        {{-- FIN MODIFICACIÓN --}}

        {{-- OPCIONAL: Añadir selector para el ESTADO del grupo --}}
        {{-- <div class="mt-4 md:col-span-2">
             <x-input-label for="estado_edit_{{ $grupo->grupo_id }}" value="Estado del Grupo" />
             <select id="estado_edit_{{ $grupo->grupo_id }}" name="estado" class="border-gray-300 rounded-md shadow-sm block mt-1 w-full">
                 <option value="ACTIVO" @selected(old('estado', $grupo->estado) == 'ACTIVO')>ACTIVO</option>
                 <option value="CERRADO" @selected(old('estado', $grupo->estado) == 'CERRADO')>CERRADO</option>
                 <option value="ARCHIVADO" @selected(old('estado', $grupo->estado) == 'ARCHIVADO')>ARCHIVADO</option> // Si usas ARCHIVADO
             </select>
             <x-input-error :messages="$errors->get('estado')" class="mt-2" />
        </div> --}}

    </div>

    <div class="mt-6 flex justify-end gap-4">
        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
        <x-primary-button>Actualizar Grupo</x-primary-button>
    </div>
</form>