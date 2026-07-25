@php
    $unassignedId = 0;
@endphp

<div class="mb-4">
    <form action="{{ route($route) }}" method="GET" class="flex flex-wrap items-center gap-2">
        
        @if ($showUnassigned)
            <a href="{{ route($route, ['nivel' => $unassignedId]) }}"
               class="px-4 py-2 text-sm font-medium rounded-md transition
                      {{ $selectedNivel == $unassignedId 
                         ? 'bg-gray-800 text-white shadow' 
                         : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                Sin Asignar
            </a>
        @endif

        @foreach ($niveles as $nivel)
            <a href="{{ route($route, ['nivel' => $nivel->nivel_id]) }}"
               class="px-4 py-2 text-sm font-medium rounded-md transition
                      {{ $selectedNivel == $nivel->nivel_id 
                         ? 'bg-gray-800 text-white shadow' 
                         : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                {{ $nivel->nombre }}
            </a>
        @endforeach

        @if ($showEgresados)
            <a href="{{ route($route, ['nivel' => 'egresados']) }}"
               class="px-4 py-2 text-sm font-medium rounded-md transition
                      {{ (string) $selectedNivel === 'egresados' 
                         ? 'bg-gray-800 text-white shadow' 
                         : 'bg-white text-gray-700 hover:bg-gray-100 border' }}">
                Egresados
            </a>
        @endif
        
        {{ $slot }}
    </form>
</div>