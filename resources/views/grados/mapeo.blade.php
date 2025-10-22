<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mapear Grados para: <span class="text-indigo-600">{{ $grado->nombre }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.grados.storeMapeo', $grado) }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-gray-600 mb-6">
                        Selecciona los grados regulares que podrán inscribirse a esta agrupación extracurricular.
                    </p>
                    
                    <div class="space-y-3">
                        @foreach ($gradosRegulares as $gradoRegular)
                            <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="grados_regulares[]" 
                                    value="{{ $gradoRegular->grado_id }}" 
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    {{-- Aquí marcamos los checkboxes que ya estaban guardados --}}
                                    @checked(in_array($gradoRegular->grado_id, $idsMapeados))
                                >
                                <span class="ml-4 text-gray-800 font-medium">
                                    {{ $gradoRegular->nombre }} (Nivel: {{ $gradoRegular->nivel->nombre }})
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <a href="{{ route('admin.grados.index', ['view_mode' => 'extracurricular']) }}" class="px-4 py-2 bg-gray-200 text-sm font-semibold rounded-md hover:bg-gray-300">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-princeton text-white text-sm font-semibold rounded-md">Guardar Mapeo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>