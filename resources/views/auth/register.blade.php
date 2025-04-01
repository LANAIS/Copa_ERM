<x-guest-layout>
    <h2>Regístrate</h2>
    
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <x-input-label for="name" :value="__('Nombre')" class="text-white" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 error-message" />
        </div>

        <!-- Email Address -->
        <div class="form-group mt-4">
            <x-input-label for="email" :value="__('Correo electrónico')" class="text-white" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 error-message" />
        </div>

        <!-- Password -->
        <div class="form-group mt-4">
            <x-input-label for="password" :value="__('Contraseña')" class="text-white" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 error-message" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="text-white" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 error-message" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <div class="forgot-password">
                <a class="text-sm text-white hover:text-secondary" href="{{ route('login') }}">
                    <i class="fas fa-arrow-left mr-1"></i> {{ __('¿Ya tienes cuenta? Iniciar sesión') }}
                </a>
            </div>

            <x-primary-button class="login-button">
                <i class="fas fa-user-plus mr-1"></i> {{ __('Registrarme') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
