@props(['grupo'])

<div class="p-6 relative bg-white rounded-lg">
   

    {{-- Encabezado --}}
    <div class="flex justify-between items-center pb-4">
        <h2 class="text-lg font-medium text-gray-900">
            Información del Grupo
        </h2>
        <div class="flex items-center gap-3">
              <form method="POST" action="{{ route('admin.grupos.archivar', $grupo) }}" onsubmit="return confirm('¿Estás seguro de que deseas archivar este grupo? Los alumnos serán desvinculados.');">
        @csrf
        @method('PATCH')
        <button type="submit" class="flex items-center gap-2 px-3 py-1.5 bg-gray-200 text-gray-800 text-xs font-bold rounded-md hover:bg-gray-300 transition">
            <svg class="w-4 h-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg> {{-- Sugerencia de ícono --}}
            <span>Cerrar Grupo</span>
        </button>
    </form>

            {{-- Botón para Editar (abre la modal) --}}
          <button 
            x-data 
            x-on:click.prevent="$dispatch('open-modal', 'editar-grupo-{{ $grupo->grupo_id }}')"
            class="flex items-center gap-2 px-4 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-md hover:bg-blue-700 transition"
        >
            <svg class="w-4 h-4"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
            <span>Editar</span>
        </button>

        {{-- Botón para Eliminar (usa el componente de confirmación) --}}
        <x-grupos.delete-form
            :action="route('admin.grupos.destroy', $grupo)"
            confirm-message="¿Seguro que quieres eliminar el grupo '{{ $grupo->nombre_grupo }}'? No se podrá si tiene alumnos asignados."
            class="bg-red-100 text-red-800"
        >
            <svg class="w-5 h-5"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use></svg>
        </x-grupos.delete-form>
        </div>
    </div>

    {{-- Cuerpo con detalles del grupo --}}
    <div class="mt-4">
        <h3 class="text-2xl font-bold text-gray-800">
            {{-- Combina Grado, Grupo y Ciclo Escolar Abreviado --}}
            {{ $grupo->grado->nombre }} {{ $grupo->nombre_grupo }} {{-- Mostrando el nombre completo para mayor claridad --}}
        </h3>
        <div class="mt-2 space-y-1 text-sm">
            <p><span class="text-gray-500">Ciclo Escolar:</span> <span class="font-semibold text-gray-700">{{ $grupo->cicloEscolar->nombre ?? 'N/A' }}</span></p>
            <p><span class="text-gray-500">Estado del Grupo:</span>
                @if ($grupo->estado === 'ACTIVO')
                    <span class="font-semibold text-green-600">Activo</span>
                @else
                    <span class="font-semibold text-red-600">Inactivo</span>
                @endif
            </p>
            <p><span class="text-gray-500">Nivel Educativo:</span> <span class="font-semibold text-gray-700">{{ $grupo->grado->nivel->nombre }}</span></p>

            @if($grupo->tipo_grupo === 'EXTRA' && $grupo->grado->gradosRegularesMapeados->isNotEmpty())
                <div class="pt-2">
                    <p>
                        <span class="text-gray-500">Grados Permitidos:</span> 
                        <span class="font-semibold text-indigo-700">
                            {{-- Esto toma los nombres de los grados mapeados y los une con comas --}}
                            {{ $grupo->grado->gradosRegularesMapeados->pluck('nombre')->implode(', ') }}
                        </span>
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Pie con las tarjetas de navegación --}}
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <a href="{{ route('admin.grupos.alumnos.index', $grupo) }}" class="block py-8 text-center bg-green-500 text-white rounded-2xl hover:bg-green-500 transition shadow-md hover:shadow-lg transform hover:-translate-y-1">
            <h3 class="text-xl font-bold">Alumnos</h3>
        </a>
        <a href="{{ route('admin.grupos.materias.index', $grupo) }}" class="block py-8 text-center bg-blue-500 text-white rounded-2xl hover:bg-blue-500 transition shadow-md hover:shadow-lg transform hover:-translate-y-1">
            <h3 class="text-xl font-bold">Materias</h3>
        </a>
        <a href="{{ route('admin.grupos.maestros.index', $grupo) }}" class="block py-8 text-center bg-orange-400 text-white rounded-2xl hover:bg-orange-500 transition shadow-md hover:shadow-lg transform hover:-translate-y-1">
            <h3 class="text-xl font-bold">Maestros</h3>
        </a>
    </div>
</div>