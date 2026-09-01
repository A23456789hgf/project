@props([
    'type' => 'info',
    'dismissible' => true,
    'icon' => null,
])

@php
    $icons = [
        'success' => 'fas fa-check-circle',
        'danger' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
    ];
    $iconClass = $icon ?? ($icons[$type] ?? 'fas fa-info-circle');
@endphp

<div {{ $attributes->merge(['class' => "alert alert-{$type} " . ($dismissible ? 'alert-dismissible fade show' : '') . " d-flex align-items-center gap-3 shadow-sm rounded-lg p-3"]) }} role="alert">
    <i class="{{ $iconClass }} fa-lg"></i>
    <div class="flex-grow-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
