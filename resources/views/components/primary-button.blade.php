<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-300 login-button']) }}>
    {{ $slot }}
</button>
