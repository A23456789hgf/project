@props([
    'title' => 'لا توجد بيانات متاحة',
    'subtitle' => 'لم يتم العثور على أي سجلات مطابقة للبحث أو التصفية الحالية.',
    'icon' => 'fas fa-inbox',
    'action' => null,
])

<div class="text-center py-5 my-4 px-3 bg-white rounded-lg border">
    <div class="mb-3 text-muted" style="font-size: 3rem;">
        <i class="{{ $icon }}"></i>
    </div>
    <h4 class="fw-bold text-dark mb-2">{{ $title }}</h4>
    <p class="text-muted mb-4 small max-w-md mx-auto">{{ $subtitle }}</p>
    @if($action)
        <div>{{ $action }}</div>
    @endif
</div>
