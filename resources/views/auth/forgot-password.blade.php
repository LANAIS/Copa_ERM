<x-guest-layout>
    <h2>Recupera tu contraseña</h2>

    <div class="mb-4 text-sm text-white">
        {{ __('¿Olvidaste tu contraseña? No hay problema. Solo proporciona tu dirección de correo electrónico y te enviaremos un enlace para que puedas crear una nueva contraseña.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <x-input-label for="email" :value="__('Correo electrónico')" class="text-white" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2 error-message" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-sm text-white hover:text-secondary flex items-center">
                <i class="fas fa-arrow-left mr-1"></i> {{ __('Volver al login') }}
            </a>
            
            <x-primary-button class="login-button">
                <i class="fas fa-paper-plane mr-1"></i> {{ __('Enviar enlace') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
