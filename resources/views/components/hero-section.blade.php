@props(['title' => '', 'subtitle' => ''])

<div {{ $attributes->merge(['class' => 'hero-section py-24 bg-gradient-to-r from-primary to-primary/80 text-white']) }}>
    <div class="container mx-auto px-6 text-center">
        @if($title)
        <h1 class="text-4xl md:text-5xl font-bold mb-4 fade-in">{{ $title }}</h1>
        @endif
        
        @if($subtitle)
        <p class="text-xl md:text-2xl mb-8 fade-in">{{ $subtitle }}</p>
        @endif
        
        <div class="fade-in">
            {{ $slot }}
        </div>
    </div>
</div> 