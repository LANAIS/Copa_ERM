@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-secondary bg-secondary/10 p-3 rounded-md']) }}>
        {{ $status }}
    </div>
@endif
