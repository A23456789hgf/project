@props([
    'id',
    'title' => null,
    'size' => 'md',
    'footer' => null,
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" {{ $attributes }}>
    <div class="modal-dialog modal-{{ $size }} modal-dialog-centered">
        <div class="modal-content ds-modal-container">
            @if($title)
                <div class="modal-header ds-card-header">
                    <h5 class="modal-title ds-card-title">{{ $title }}</h5>
                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            <div class="modal-body p-4">
                {{ $slot }}
            </div>
            @if($footer)
                <div class="modal-footer p-3 bg-light border-top">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
