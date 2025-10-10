<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Título dinámico que muestra el nombre del grado --}}
            Nuevo Grupo para: {{ $grado->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    {{-- El formulario enviará los datos a la ruta 'grupos.store' --}}
                    <form action="{{ route('grupos.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="grado_id" value="{{ $grado->grado_id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nombre" class="block font-medium text-sm text-gray-700">Nombre del Grupo (Ej: A, B, C)</label>
                                <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" required autofocus
                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>

                            <div>
                                <label for="ciclo_escolar" class="block font-medium text-sm text-gray-700">Ciclo Escolar</label>
                                <input id="ciclo_escolar" name="ciclo_escolar" type="text" value="{{ old('ciclo_escolar', '2025-2026') }}" required
                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>

                            <div>
                                <label for="tipo_grupo" class="block font-medium text-sm text-gray-700">Tipo de Grupo</label>
                                <select id="tipo_grupo" name="tipo_grupo" required
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="REGULAR" {{ old('tipo_grupo') == 'REGULAR' ? 'selected' : '' }}>Regular</option>
                                    <option value="EXTRA" {{ old('tipo_grupo') == 'EXTRA' ? 'selected' : '' }}>Extracurricular</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('grados.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Guardar Grupo
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>