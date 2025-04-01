<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2>Iniciar Sesión</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Correo electrónico')" class="text-white" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 error-message" />
        </div>

        <!-- Password -->
        <div class="form-group mt-4">
            <x-input-label for="password" :value="__('Contraseña')" class="text-white" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 error-message" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="checkbox-label">
                <input id="remember_me" type="checkbox" name="remember">
                <span class="ms-2 text-sm">{{ __('Recordarme') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <div class="forgot-password">
                    <a class="text-sm text-white hover:text-secondary flex items-center" href="{{ route('password.request') }}">
                        <i class="fas fa-key mr-1"></i> {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                </div>
            @endif

            <x-primary-button class="login-button">
                <i class="fas fa-sign-in-alt mr-1"></i> {{ __('Ingresar') }}
            </x-primary-button>
        </div>
        
        <div class="register-link mt-8 text-center py-4 border-t border-secondary/20">
            <p class="text-white mb-3">¿No tienes una cuenta todavía?</p>
            <a href="{{ route('register') }}" class="btn-register">
                <i class="fas fa-user-plus mr-2"></i> Regístrate aquí
            </a>
        </div>
    </form>
</x-guest-layout>
