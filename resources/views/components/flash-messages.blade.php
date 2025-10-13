{{-- resources/views/components/flash-messages.blade.php --}}

@if (session('success'))
    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
        <p class="font-bold">Éxito</p>
        <p>{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
        <p class="font-bold">Error</p>
        <p>{{ session('error') }}</p>
    </div>
@endif