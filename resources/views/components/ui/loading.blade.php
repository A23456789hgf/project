@props([
    'text' => 'جاري تحميل البيانات...',
])

<div class="d-flex align-items-center justify-content-center gap-3 py-4 my-2 text-muted">
    <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <span class="fw-semibold">{{ $text }}</span>
</div>
