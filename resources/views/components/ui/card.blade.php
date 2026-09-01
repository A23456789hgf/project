@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'hover' => false,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'ds-card' . ($hover ? ' ds-card-hover' : '')]) }}>
    @if($title || $actions)
        <div class="ds-card-header">
            <div>
                @if($title)
                    <h3 class="ds-card-title">
                        @if($icon) <i class="{{ $icon }} me-2 text-primary"></i> @endif
                        {{ $title }}
                    </h3>
                @endif
                @if($subtitle)
                    <small class="text-muted">{{ $subtitle }}</small>
                @endif
            </div>
            @if($actions)
                <div class="ds-card-actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="ds-card-body">
        {{ $slot }}
    </div>
</div>
