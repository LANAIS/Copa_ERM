@props(['title' => '', 'icon' => ''])

<div {{ $attributes->merge(['class' => 'card bg-white rounded-lg shadow-md p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-2']) }}>
    @if($icon)
    <div class="text-center text-4xl text-secondary mb-4">
        <i class="{{ $icon }}"></i>
    </div>
    @endif
    
    @if($title)
    <h3 class="text-xl font-semibold text-primary mb-3">{{ $title }}</h3>
    @endif
    
    <div class="text-gray-700">
        {{ $slot }}
    </div>
</div> 