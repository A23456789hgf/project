@props([
    'variant' => 'info',
    'icon' => null,
])

<span {{ $attributes->merge(['class' => "ds-badge ds-badge-{$variant}"]) }}>
    @if($icon) <i class="{{ $icon }} me-1"></i> @endif
    {{ $slot }}
</span>
