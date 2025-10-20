{{-- resources/views/components/grupos/edit-form.blade.php --}}
@props(['grupo'])

<form method="POST" action="{{ route('grupos.update', $grupo) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="nombre_grupo" value="Nombre del Grupo" />
            <x-text-input id="nombre_grupo" name="nombre_grupo" type="text" :value="old('nombre_grupo', $grupo->nombre_grupo)" required autofocus />
        </div>
        <div>
            <x-input-label for="ciclo_escolar" value="Ciclo Escolar" />
            <x-text-input id="ciclo_escolar" name="ciclo_escolar" type="text" :value="old('ciclo_escolar', $grupo->ciclo_escolar)" required />
        </div>
    </div>
   

    <div class="mt-6 flex justify-end gap-4">
        <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
        <button type="submit" class="px-4 py-2 bg-princeton text-white rounded-md">Actualizar Grupo</button>
    </div>
</form>