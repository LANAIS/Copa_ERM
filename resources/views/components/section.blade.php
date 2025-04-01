@props(['title' => '', 'background' => 'white', 'centered' => true])

@php
$bgClass = $background === 'light' ? 'bg-light-bg' : ($background === 'primary' ? 'bg-primary text-white' : 'bg-white');
$centeredClass = $centered ? 'text-center' : '';
@endphp

<section {{ $attributes->merge(['class' => "py-16 md:py-24 {$bgClass}"]) }}>
    <div class="container mx-auto px-6">
        @if($title)
        <h2 class="text-3xl font-bold mb-12 {{$centeredClass}} {{ $background === 'primary' ? 'text-white' : 'text-primary' }} fade-in">{{ $title }}</h2>
        @endif
        
        <div class="fade-in">
            {{ $slot }}
        </div>
    </div>
</section> 