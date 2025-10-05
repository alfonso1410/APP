<nav x-data="{ open: false }" class="bg-white border-b border-gray-100"> 
    {{-- Botón (Hamburguesa) para mostrar/ocultar el sidebar en móviles --}}
    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
        <span class="sr-only">Open sidebar</span>
        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
           <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
        </svg>
    </button>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
    </form>
    
    {{-- El Sidebar Fijo --}}
    <aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
       <div class="h-full px-3 py-4 overflow-y-auto bg-[#2f425d]">
          <a href="{{ route('dashboard') }}" class="flex items-center ps-2.5 mb-5">
             <img src="{{ asset('Assets/logo-princeton.png') }}"  alt="Logo del sistema boletas" />
          </a>
          <ul class="space-y-2 font-medium">
             
             {{-- 1. ENLACE DE INICIO --}}
             <li>
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-white rounded-lg dark:text-white hover:bg-gray-700 dark:hover:bg-gray-700 group">
                   {{-- Aquí va el SVG del ícono del Dashboard --}}
                   <span class="ms-3">Inicio</span>
                </a>
             </li>
    
             {{-- 2. ENLACE PARA GESTIONAR USUARIOS --}}
             <li>
                <a href="{{ route('users.index') }}" class="flex items-center p-2 text-white rounded-lg dark:text-white hover:bg-gray-700 dark:hover:bg-gray-700 group">
                   {{-- SVG de Users --}}
                   <svg class="shrink-0 w-5 h-5 text-white transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                      <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Usuarios</span>
                </a>
             </li>

             {{-- ========================================================== --}}
             {{-- =========== SECCIÓN DE PRUEBA CORREGIDA AQUÍ =========== --}}
             <li>
                <a href="{{ route('alumnos.index') }}" class="flex items-center p-2 text-white rounded-lg dark:text-white hover:bg-gray-700 dark:hover:bg-gray-700 group">
                   {{-- SVG de Alumnos (puedes usar el mismo o cambiarlo) --}}
                   <svg class="shrink-0 w-5 h-5 text-white transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                      <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                   </svg>
                   <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Alumnos</span>
                </a>
             </li>
             {{-- ========================================================== --}}
             
             {{-- 4. ENLACE PARA CERRAR SESIÓN --}}
             <li>
                <a href="#" 
                   class="flex items-center p-2 text-white rounded-lg hover:bg-red-600 group"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                   
                   {{-- Icono de Salida --}}
                   <svg class="shrink-0 w-5 h-5 text-red-300 transition duration-75 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                   
                   <span class="ms-3">Cerrar Sesión</span>
                </a>
             </li>
          </ul>
       </div>
    </aside>
</nav>