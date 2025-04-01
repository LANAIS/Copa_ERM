@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-accent text-start text-base font-medium text-white bg-primary-800 focus:outline-none focus:text-secondary focus:bg-primary-700 focus:border-accent transition duration-300 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-white hover:text-secondary hover:bg-primary-700 hover:border-secondary focus:outline-none focus:text-secondary focus:bg-primary-700 focus:border-secondary transition duration-300 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
