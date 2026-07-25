<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Grupo para: {{ $grado->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if($ciclos->isNotEmpty())
                        <form 
                            action="{{ route('admin.grupos.store') }}" 
                            method="POST"
                            x-data="{ isSubmitting: false }"
                            x-on:submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }"
                        >
                            @csrf

                            <input type="hidden" name="grado_id" value="{{ $grado->grado_id }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nombre del Grupo --}}
                                <div>
                                    <label for="nombre_grupo" class="block font-medium text-sm text-gray-700">
                                        Nombre del Grupo (Ej: A, B, C)
                                    </label>
                                    <input id="nombre_grupo" name="nombre_grupo" type="text" 
                                           value="{{ old('nombre_grupo') }}" required autofocus
                                           class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <x-input-error :messages="$errors->get('nombre_grupo')" class="mt-2" />
                                </div>

                                {{-- Selector de Ciclo Escolar --}}
                                <div>
                                    <label for="ciclo_escolar_id" class="block font-medium text-sm text-gray-700">
                                        Ciclo Escolar
                                    </label>
                                    <select id="ciclo_escolar_id" name="ciclo_escolar_id" required
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">-- Selecciona un ciclo --</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->ciclo_escolar_id }}" 
                                                    {{ old('ciclo_escolar_id') == $ciclo->ciclo_escolar_id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }} ({{ $ciclo->estado }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('ciclo_escolar_id')" class="mt-2" />
                                    <p class="text-xs text-gray-500 mt-1">
                                        Puedes crear grupos en cualquier ciclo, independientemente de su estado.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.grados.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                    Cancelar
                                </a>
                                <button 
                                    type="submit" 
                                    :disabled="isSubmitting"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                                >
                                    <span x-show="!isSubmitting">Guardar Grupo</span>
                                    <span x-show="isSubmitting" style="display: none;">Guardando...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                            <p class="font-bold">No hay Ciclos Escolares</p>
                            <p>Debes crear al menos un ciclo escolar antes de poder crear grupos.</p>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="{{ route('admin.grados.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Volver
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>