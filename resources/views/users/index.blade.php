<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-2xl text-princeton leading-tight">
            {{ __('Usuarios') }}
        </h1>
    </x-slot>
 
    <div x-data="{}" class="mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-end m-2 p-2">
            {{-- Botón para abrir la modal --}}
            <button 
                x-on:click="$dispatch('open-modal', 'agregar-usuario')" 
                class="px-4 py-2 bg-princeton hover:bg-gray-800 text-white font-bold rounded-lg"
            >
                Agregar Usuario
            </button>
        </div>

    @if ($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg">
        <strong>Hubo algunos errores al intentar guardar el usuario:</strong>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 bg-green-100 rounded-lg">
        {{ session('success') }}
    </div>
@endif

    <x-modal name="agregar-usuario" :show="$errors->any()" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-princeton mb-4">Agregar Nuevo Usuario</h2>
            {{-- Usar el componente del formulario --}}
            <x-user-create action="{{ route('users.store') }}" method="POST" />
        </div>
    </x-modal>
    

    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">apellido_paterno</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">apellido_materno</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
             </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            
            {{-- 👈 Recorrer la colección de usuarios pasada desde el controlador --}}
            @foreach ($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $user->apellido_paterno }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $user->apellido_materno }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $user->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{-- Esto asume que tienes un campo 'role' --}}
                        {{ $user->rol ?? 'N/A' }} 
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                        @if ($user->activo)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Activo
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Inactivo
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                       <section x-data="{}" class="flex gap-2 justify-center ">
                     {{-- Botones de acción: Editar y Eliminar --}}
                        <button
        type="button"
        x-on:click.prevent="$dispatch('open-modal', 'editar-usuario-{{ $user->id }}')" 
        class="bg-blue-100 text-blue-800 p-1 flex size-4 sm:size-6 items-center justify-center rounded-full hover:scale-150 transition-transform"
        title="Editar Usuario"
    >
        <svg class="size-6">
            <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use>
        </svg>
        </button>

        
                        <x-user-eliminate-form
            :action="route('users.destroy', $user)"
            confirm-message="¿Deseas desactivar a {{ $user->name }}? No podrá iniciar sesión."
            class="bg-red-100"
        >
            {{-- Inyectamos el icono SVG en el slot --}}
            <svg class="size-6">
                <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use>
            </svg>
        </x-user-eliminate-form>
                    </section>
                    </td>

        {{-- Modal para editar usuario --}}
                    <x-modal name="editar-usuario-{{ $user->id }}" 
         :show="$errors->hasAny(['name', 'email', 'password', 'rol', 'activo', 'apellido_paterno', 'apellido_materno']) && old('user_id') == $user->id" 
         focusable>
        <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Editar Usuario: {{ $user->name }}</h2>     
        {{-- Usamos el componente de Edición --}}
        <x-user-edit-form 
            :user="$user"                                  {{-- Pasamos el objeto $user --}}
            action="{{ route('users.update', $user) }}"  {{-- Ruta al método update --}}       />
        </div>
</x-modal>
                </tr>
            @endforeach

        </tbody>
    </table>
    
    {{-- Muestra los enlaces de paginación si usaste paginate() --}}
    <div class="p-4">
        {{ $users->links() }}
    </div>
</div>
</x-app-layout>
