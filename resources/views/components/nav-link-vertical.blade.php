@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center w-full px-4 py-3 rounded-lg text-lg font-semibold bg-slate-700 text-white transition duration-150 ease-in-out'
            : 'inline-flex items-center w-full px-4 py-3 rounded-lg text-lg text-gray-300 hover:text-white hover:bg-slate-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>