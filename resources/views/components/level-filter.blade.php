{{-- resources/views/components/level-filter.blade.php --}}
@php
    $unassignedId = 0;
@endphp

<div class="mb-4">
    <form action="{{ route($route) }}" method="GET" class="flex flex-wrap items-center gap-2">
        
        {{-- El botón ahora solo se muestra si la propiedad es verdadera --}}
        @if ($showUnassigned)
            <a href="{{ route($route, ['nivel' => $unassignedId]) }}"
               class="px-4 py-2 text-sm font-medium rounded-md transition
                      {{ $selectedNivel == $unassignedId 
                         ? 'bg-gray-800 text-white shadow' 
                         : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                Sin Asignar
            </a>
        @endif

        {{-- Los botones de los niveles no cambian --}}
        @foreach ($niveles as $nivel)
            <a href="{{ route($route, ['nivel' => $nivel->nivel_id]) }}"
               class="px-4 py-2 text-sm font-medium rounded-md transition
                      {{ $selectedNivel == $nivel->nivel_id 
                         ? 'bg-gray-800 text-white shadow' 
                         : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                {{ $nivel->nombre }}
            </a>
        @endforeach
        
         {{ $slot }}
    </form>
</div>