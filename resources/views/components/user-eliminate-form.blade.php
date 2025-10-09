@props(['action', 'confirmMessage' => '¿Estás seguro de que deseas desactivar este elemento?', 'class' => ''])

{{-- 1. Usamos la acción pasada para apuntar al método destroy --}}
<form 
    method="POST" 
    action="{{ $action }}" 
    class="inline-block" 
    {{-- 2. Añadimos la confirmación de JavaScript --}}
    onsubmit="return confirm('{{ $confirmMessage }}');"
>
    @csrf
    @method('DELETE')
    
    {{-- 3. Botón de Desactivación (se pueden pasar clases para el estilo del icono) --}}
    <button 
        type="submit" 
        {{-- Agregamos clases por defecto y clases pasadas por el usuario --}}
        class="p-1 flex size-4 sm:size-6 text-red-800 items-center justify-center rounded-full hover:scale-150 transition-transform {{ $class }}"
        title="Desactivar"
    >
        {{ $slot }} {{-- Esto permite que el icono SVG se inyecte desde el index --}}
    </button>
</form>