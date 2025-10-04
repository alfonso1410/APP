<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2f425d] leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>
    <button x-data="" 
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
        Eliminar Usuario
    </button>

    <button x-data="" 
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="bg-gray-800 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
        Agregar usuario
    </button>

    <x-modal name="confirm-user-deletion" :show="false" focusable>
        {{-- Contenido de la Modal --}}
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                ¿Estás seguro de que quieres eliminar este usuario?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Esta acción es irreversible. Confirma para continuar.
            </p>

            <div class="mt-6 flex justify-end">
                {{-- Botón para CERRAR la Modal --}}
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                {{-- Botón para la acción principal --}}
                <x-danger-button class="ms-3">
                    Eliminar
                </x-danger-button>
            </div>
        </div>
    </x-modal>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
        </div>
    </div>
    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">apellido_paterno</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">apellido_materno</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th class="px-6 py-3 bg-gray-50"></th> </tr>
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
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        {{-- Botones de acción: Editar y Eliminar --}}
                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                        {{-- ... formulario para eliminar ... --}}
                    </td>
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
