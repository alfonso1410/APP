<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased"
          style="background-image: url('{{ asset('Assets/background.jpg') }}');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;">
        <div class="min-h-screen flex flex-col sm:justify-center items-end pt-6 sm:pt-0 relative">
            
            <div class="absolute top-0 left-0 p-6">
                <a href="/">
                    <img src="{{ asset('Assets/logo-princeton.png') }}"
                        alt="Logo del sistema de boletas"
                        class="h-24 w-auto">
                </a>
            </div>

            <div class="w-full sm:max-w-xl mt-6 px-10 py-20 bg-white/50 shadow-md overflow-hidden sm:mr-20">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
