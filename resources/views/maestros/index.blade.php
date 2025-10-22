<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-2xl text-princeton leading-tight">
            {{ __('Gestión de Maestros') }}
        </h1>
    </x-slot>
 
    <div x-data="{}" class="mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-end m-2 p-2">
            <button  x-on:click="$dispatch('open-modal', 'agregar-maestro')" class="px-4 py-2 bg-princeton hover:bg-gray-800 text-white font-bold rounded-lg">
                Agregar Maestro
            </button>
        </div>

        {{-- Alertas de éxito y error (sin cambios) --}}
        @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 bg-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <x-modal name="agregar-maestro" :show="$errors->any()" focusable>
            <div class="p-6">
                <h2 class="text-lg font-medium text-princeton mb-4">Agregar Nuevo Maestro</h2>
                {{-- Usamos el nuevo componente de maestro --}}
                <x-maestros.create-form action="{{ route('admin.maestros.store') }}" method="POST" />
            </div>
        </x-modal>
     
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                {{-- Cabecera de la tabla (sin cambios) --}}
                <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">apellido_paterno</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">apellido_materno</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
             </tr>
        </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($users as $maestro)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $maestro->apellido_paterno }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $maestro->apellido_materno }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $maestro->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $maestro->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($maestro->activo)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <section x-data class="flex gap-2 justify-center">
                                    <button type="button" x-on:click="$dispatch('open-modal', 'editar-maestro-{{ $maestro->id }}')" class="bg-blue-100 text-blue-800 p-1 flex size-4 sm:size-6 items-center justify-center rounded-full hover:scale-150 transition-transform" title="Editar Maestro">
                                        <svg class="size-6"><use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-edit"></use></svg>
                                    </button>
                                    
                                    <x-user-eliminate-form
            :action="route('admin.maestros.destroy', $maestro)"
            confirm-message="¿Deseas desactivar a {{ $maestro->name }}? No podrá iniciar sesión."
            class="bg-red-100"
        >
            {{-- Inyectamos el icono SVG en el slot --}}
            <svg class="size-6">
                <use xlink:href="{{ asset('Assets/sprite.svg') }}#icon-delete"></use>
            </svg>
        </x-user-eliminate-form>
                                </section>
                            </td>

                            {{-- Modal para editar maestro --}}
                            <x-modal name="editar-maestro-{{ $maestro->id }}" :show="$errors->any() && old('user_id') == $maestro->id" focusable>
                                <div class="p-6">
                                    <h2 class="text-lg font-medium text-gray-900 mb-4">Editar Maestro: {{ $maestro->name }}</h2>
                                    <x-maestros.edit-form :user="$maestro" action="{{ route('admin.maestros.update', $maestro) }}" />
                                </div>
                            </x-modal>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="p-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>