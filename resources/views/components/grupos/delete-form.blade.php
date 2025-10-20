{{-- resources/views/components/grupos/delete-form.blade.php --}}

@props([
    'action', 
    'confirmMessage' => '¿Estás seguro de que deseas eliminar este elemento?',
])

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
        title="Eliminar"
    >
        {{-- Aquí se inyectará tu ícono SVG --}}
        {{ $slot }} 
    </button>
</form>