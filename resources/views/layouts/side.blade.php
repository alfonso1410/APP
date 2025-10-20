{{-- Envolvemos todo en un div con el estado de Alpine.js --}}
<div x-data="{ open: false }">
    <nav class="bg-white border-b border-gray-100"> 
        {{-- 1. El botón ahora usa @click de Alpine para cambiar el estado 'open' --}}
        <button @click="open = !open" type="button" class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
            <span class="sr-only">Open sidebar</span>
            <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
            </svg>
        </button>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
            @csrf
        </form>
    </nav>
    
    {{-- 2. El Sidebar ahora escucha el estado 'open' para mostrarse --}}
    <aside id="logo-sidebar" 
           class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" 
           :class="{'translate-x-0': open}" 
           aria-label="Sidebar">
           
        <div class="h-full px-3 py-4 overflow-y-auto bg-princeton">
            <a href="{{ route ('dashboard') }}" class="flex items-center ps-2.5 mb-5">
               <img src="{{ asset('Assets/logo-princeton.png') }}" alt="Logo del sistema boletas" />
            </a>
            <ul class="space-y-2 font-medium">
                {{-- 1. ENLACE DE DASHBOARD --}}
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group">
                        <svg class="size-5 text-white-500 transition duration-150 group-hover:text-gray-900">
                            <use xlink:href="{{ asset('Assets/sprite.svg') }}#dalo"> </use>       
                        </svg>
                        <span class="ms-3">Inicio</span>
                    </a>
                </li>
    
                {{-- 2. ENLACE PARA GESTIONAR USUARIOS --}}
                <li>
                    <a href="{{ route('users.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group">
                        <svg class="shrink-0 w-5 h-5 text-white-500 transition duration-75 group-hover:text-gray-900">
                            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-user"> </use>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Usuarios</span>
                    </a>
                </li>
    
                {{-- 3. ENLACE PARA GESTIONAR ALUMNOS --}}
                <li>
                    <a href="{{ route('alumnos.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group">
                        <svg class="shrink-0 w-5 h-5 text-white-500 transition duration-75 group-hover:text-gray-900">
                            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-user"> </use>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Alumnos</span>
                    </a>
                </li>

                {{-- ENLACE PARA GESTIONAR GRADOS --}}
                <li>
                    <a href="{{ route('grados.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group">
                        <svg class="shrink-0 w-5 h-5 text-white-500 transition duration-75 group-hover:text-gray-900">
                            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-grade"> </use>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Grados</span>
                    </a>
                </li>

                  {{-- ENLACE PARA GESTIONAR maestros --}}
                <li>
                    <a href="{{ route('maestros.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group">
                        <svg class="shrink-0 w-5 h-5 text-white-500 transition duration-75 group-hover:text-gray-900">
                            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-user"> </use>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Maestros</span>
                    </a>
                </li>

                {{-- ENLACE PARA GESTIONAR MATERIAS --}}
                <li>
                    <a href="{{ route('materias.index') }}" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-700 group">
                        <svg class="shrink-0 w-5 h-5 text-white-500 transition duration-75 group-hover:text-gray-900">
                            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-grade"> </use>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Materias</span>
                    </a>
                </li>
    
                {{-- 4. ENLACE PARA CERRAR SESIÓN --}}
                <li>
                    <a href="#" 
                       class="flex items-center p-2 text-white rounded-lg hover:bg-red-600 group"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <svg class="shrink-0 w-5 h-5 text-red-300 transition duration-75 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="ms-3">Cerrar Sesión</span>
                    </a>
                </li>
                
            </ul>
        </div>
    </aside>

    {{-- 3. (Opcional pero recomendado) Overlay para cerrar el menú al hacer clic fuera --}}
    <div x-show="open" @click="open = false" class="fixed inset-0 bg-black bg-opacity-50 z-30 sm:hidden"></div>
</div>