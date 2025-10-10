<div class="mb-4 flex items-center gap-2">
    <span class="text-sm font-medium text-gray-700">Filtrar por Nivel:</span>
    
    @foreach ($niveles as $nivel)
        <a href="{{ route($route, ['nivel' => $nivel->nivel_id]) }}"
           class="px-3 py-1 text-sm font-semibold rounded-full
                  {{ $selectedNivel == $nivel->nivel_id ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            {{ $nivel->nombre }}
        </a>
    @endforeach
</div>