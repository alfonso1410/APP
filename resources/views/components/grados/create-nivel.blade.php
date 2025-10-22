{{-- resources/views/components/grados/create-nivel.blade.php --}}
<form method="post" action="{{ route('admin.niveles.store') }}" class="space-y-6">
    @csrf

    <div>
        <x-input-label for="nombre_nivel" value="Nombre del Nivel" />
        <x-text-input id="nombre_nivel" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required autofocus autocomplete="off" />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div class="flex justify-end gap-4">
        <x-secondary-button x-on:click="$dispatch('close')">
            Cancelar
        </x-secondary-button>

        <x-primary-button>
            Guardar Nivel
        </x-primary-button>
    </div>
</form>