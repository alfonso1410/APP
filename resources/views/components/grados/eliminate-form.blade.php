{{-- resources/views/components/grados/eliminate-form.blade.php --}}

@props(['action', 'confirmMessage' => '¿Estás seguro?'])

<form 
    method="POST" 
    action="{{ $action }}" 
    class="inline-block" 
    x-data="{ isDeleting: false }"
    
    {{-- ===== INICIO DE LA CORRECCIÓN ===== --}}
    {{-- Cambiamos las comillas simples ('') por backticks (``) --}}
    x-on:submit.prevent="
        if (!isDeleting) { 
            if (confirm(`{{ $confirmMessage }}`)) { {{-- <-- CAMBIO AQUÍ --}}
                isDeleting = true; 
                $el.submit(); 
            }
        }
    "
    {{-- ===== FIN DE LA CORRECCIÓN ===== --}}
>
    @csrf
    @method('DELETE')
    
    <button 
        type="submit" 
        ::disabled="isDeleting"
        {{ $attributes->merge(['class' => 'p-1 flex size-4 sm:size-6 flex items-center justify-center rounded-full transition-transform hover:scale-150 disabled:opacity-50 disabled:cursor-not-allowed']) }}
        title="Eliminar Grado"
    >
        {{ $slot }} 
    </button>
</form>