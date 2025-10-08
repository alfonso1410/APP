<form action="{{ $action }}" method="GET">
    <div class="relative">
        {{-- El ícono de lupa posicionado a la derecha --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>

        {{-- El campo de texto con el diseño redondeado --}}
        <input 
            type="text" 
            name="search" 
            placeholder="{{ $placeholder }}" 
            class="w-full py-2 pl-4 pr-10 text-sm bg-white border border-gray-300 rounded-full focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            value="{{ $value }}"
        >
    </div>
</form>