<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Información de Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Aquí puedes ver tu información de perfil. El nombre y correo no se pueden modificar desde esta pantalla.") }}
        </p>
    </header>

    {{-- Formulario para reenviar verificación (se mantiene por si acaso) --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- Quitamos el formulario 'profile.update' y solo mostramos los campos --}}
    <div class="mt-6 space-y-6">

        {{-- CAMPO NOMBRE COMPLETO (SOLO LECTURA) --}}
        <div>
            <x-input-label for="name" :value="__('Nombre Completo')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-gray-100" 
                          {{-- Concatenamos el nombre, apellido_paterno y apellido_materno --}}
                          :value="old('name', $user->name . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno)" 
                          disabled 
                          autocomplete="name" />
        </div>

        {{-- CAMPO CORREO (SOLO LECTURA) --}}
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100" 
                          :value="old('email', $user->email)" 
                          disabled 
                          autocomplete="username" />

            {{-- Lógica de verificación de email (traducida) --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Tu correo electrónico no está verificado.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Se ha enviado un nuevo enlace de verificación a tu correo.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- ELIMINAMOS EL DIV QUE CONTIENE EL BOTÓN DE GUARDAR --}}
        {{-- 
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p ... >{{ __('Saved.') }}</p>
            @endif
        </div> 
        --}}
    </div>
</section>