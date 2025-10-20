{{-- resources/views/components/grados/eliminate-form.blade.php --}}

@props(['action', 'confirmMessage' => '¿Estás seguro?'])

<form 
    method="POST" 
    action="{{ $action }}" 
    class="inline-block" 
    onsubmit="return confirm('{{ $confirmMessage }}');"
>
    @csrf
    @method('DELETE')
    
    <button 
        type="submit" 
        {{ $attributes->merge(['class' => 'p-1 flex size-4 sm:size-6 flex items-center justify-center rounded-full transition-transform hover:scale-150']) }}
        title="Eliminar Grado"
        
    >
        {{-- Aquí se inyectará el ícono SVG --}}
        {{ $slot }} 
    </button>
</form>