<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asignar Maestros Titulares a: <span class="text-indigo-600">{{ $grupo->grado->nombre }} - "{{ $grupo->nombre_grupo }}"</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />
            <form action="{{ route('admin.grupos.maestros.store', $grupo) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg p-6">
                    
                    <p class="text-gray-600 mb-6">
                        Selecciona el maestro titular y auxiliar para ESPAÑOL y para INGLÉS.
                    </p>

                    <div class="space-y-6">
                        
                        @if ($maestrosDisponibles->isNotEmpty())

                            {{-- ===== SECCIÓN ESPAÑOL ===== --}}
                            <div class="p-4 border rounded-lg space-y-4">
                                <h3 class="font-semibold text-lg text-gray-800">ESPAÑOL</h3>
                                
                                {{-- 1. Titular Español --}}
                                <div>
                                    <label for="maestro_titular_espanol_id" class="block font-medium text-sm text-gray-700">Maestro Titular (ESPAÑOL)</label>
                                    <select name="maestro_titular_espanol_id" id="maestro_titular_espanol_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">[ Ninguno seleccionado ]</option>
                                        @foreach ($maestrosDisponibles as $maestro)
                                            <option value="{{ $maestro->id }}" 
                                                {{-- Lógica actualizada: usa old() y la variable del controlador --}}
                                                @selected(old('maestro_titular_espanol_id', $asignacionEspanol?->maestro_titular_id) == $maestro->id)
                                            >
                                                {{ $maestro->name }} {{ $maestro->apellido_paterno }} {{ $maestro->apellido_materno}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('maestro_titular_espanol_id')" class="mt-2" />
                                </div>

                                {{-- 2. Auxiliar Español (NUEVO) --}}
                                <div>
                                    <label for="maestro_auxiliar_espanol_id" class="block font-medium text-sm text-gray-700">Maestro Auxiliar (ESPAÑOL)</label>
                                    <select name="maestro_auxiliar_espanol_id" id="maestro_auxiliar_espanol_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">[ Ninguno seleccionado ]</option>
                                        @foreach ($maestrosDisponibles as $maestro)
                                            <option value="{{ $maestro->id }}"
                                                @selected(old('maestro_auxiliar_espanol_id', $asignacionEspanol?->maestro_auxiliar_id) == $maestro->id)
                                            >
                                                {{ $maestro->name }} {{ $maestro->apellido_paterno }} {{ $maestro->apellido_materno}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('maestro_auxiliar_espanol_id')" class="mt-2" />
                                </div>
                            </div>

                            {{-- ===== SECCIÓN INGLÉS ===== --}}
                            <div class="p-4 border rounded-lg space-y-4">
                                <h3 class="font-semibold text-lg text-gray-800">INGLÉS</h3>

                                {{-- 3. Titular Inglés --}}
                                <div>
                                    <label for="maestro_titular_ingles_id" class="block font-medium text-sm text-gray-700">Maestro Titular (INGLÉS)</label>
                                    <select name="maestro_titular_ingles_id" id="maestro_titular_ingles_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">[ Ninguno seleccionado ]</option>
                                        @foreach ($maestrosDisponibles as $maestro)
                                            <option value="{{ $maestro->id }}" 
                                                @selected(old('maestro_titular_ingles_id', $asignacionIngles?->maestro_titular_id) == $maestro->id)
                                            >
                                                {{ $maestro->name }} {{ $maestro->apellido_paterno }} {{ $maestro->apellido_materno}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('maestro_titular_ingles_id')" class="mt-2" />
                                </div>

                                {{-- 4. Auxiliar Inglés (NUEVO) --}}
                                <div>
                                    <label for="maestro_auxiliar_ingles_id" class="block font-medium text-sm text-gray-700">Maestro Auxiliar (INGLÉS)</label>
                                    <select name="maestro_auxiliar_ingles_id" id="maestro_auxiliar_ingles_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">[ Ninguno seleccionado ]</option>
                                        @foreach ($maestrosDisponibles as $maestro)
                                            <option value="{{ $maestro->id }}"
                                                @selected(old('maestro_auxiliar_ingles_id', $asignacionIngles?->maestro_auxiliar_id) == $maestro->id)
                                            >
                                                {{ $maestro->name }} {{ $maestro->apellido_paterno }} {{ $maestro->apellido_materno}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('maestro_auxiliar_ingles_id')" class="mt-2" />
                                </div>
                            </div>

                        @else
                            <div class="text-center p-4 border rounded-lg text-gray-500">
                                No hay usuarios con el rol "maestro" en el sistema.
                            </div>
                        @endif

                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <a href="{{ route('admin.grupos.maestros.index', $grupo) }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Volver al Grupo</a>
                        
                        @if ($maestrosDisponibles->isNotEmpty())
                            <button type="submit" class="px-4 py-2 bg-princeton text-white text-sm font-semibold rounded-md hover:bg-slate-500">
                                Guardar Maestros
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>