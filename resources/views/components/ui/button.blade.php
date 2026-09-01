@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'href' => null,
])

@php
    $classes = "ds-btn ds-btn-{$variant} ds-btn-{$size}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <i class="{{ $icon }}"></i> @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) <i class="{{ $icon }}"></i> @endif
        <span>{{ $slot }}</span>
    </button>
@endif
