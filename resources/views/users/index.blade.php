<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2f425d] leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-4">
            <button x-data="" 
                    x-on:click.prevent="$dispatch('open-modal', 'add-user-modal')"
                    class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Agregar usuario
            </button>
            <button x-data="" 
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Eliminar Usuario
            </button>
        </div>
    </div>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellido Paterno</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apellido Materno</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 bg-gray-50"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
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
                                    @if(!empty($user->getRoleNames()))
                                        @foreach($user->getRoleNames() as $roleName)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $roleName }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Sin rol
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="p-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-modal name="confirm-user-deletion" :show="false" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                ¿Estás seguro de que quieres eliminar este usuario?
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Esta acción es irreversible. Confirma para continuar.
            </p>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>
                <x-danger-button class="ms-3">
                    Eliminar
                </x-danger-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="add-user-modal" :show="false" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Agregar Nuevo Usuario
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Aquí iría el formulario para agregar un nuevo usuario.
            </p>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>
                <x-primary-button class="ms-3">
                    Guardar Usuario
                </x-primary-button>
            </div>
        </div>
    </x-modal>
</x-app-layout>