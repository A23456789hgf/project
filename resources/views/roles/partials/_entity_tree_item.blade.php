{{-- Entity Tree Item - Recursive Component --}}
@php
    $isHighlighted = isset($highlightEntityId) && $entity->id === $highlightEntityId;
@endphp
<div class="entity-item ps-{{ $level > 0 ? 3 : 0 }}" style="margin-right: {{ $level * 20 }}px;">
    <div class="d-flex align-items-center py-2 px-3 mb-2 rounded-3 border-start border-4 
                {{ $isHighlighted ? 'bg-success bg-opacity-10 border-success' : 'bg-light' }}
                {{ !$isHighlighted ? ($level === 0 ? 'border-primary' : ($level === 1 ? 'border-info' : 'border-secondary')) : '' }}">
        <div class="entity-icon me-3">
            @if($entity->children->count() > 0)
                <span class="p-2 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="fas fa-sitemap text-{{ $isHighlighted ? 'success' : ($level === 0 ? 'primary' : ($level === 1 ? 'info' : 'secondary')) }}"></i>
                </span>
            @else
                <span class="p-2 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="fas fa-building text-{{ $isHighlighted ? 'success' : 'muted' }}"></i>
                </span>
            @endif
        </div>
        <div class="entity-info flex-grow-1">
            <span class="fw-semibold {{ $isHighlighted ? 'text-success' : 'text-dark' }}">
                {{ $entity->name }}
                @if($isHighlighted)
                    <span class="badge bg-success ms-2 small">جهتك</span>
                @endif
            </span>
            @if($entity->children->count() > 0)
                <span class="badge bg-secondary bg-opacity-25 text-secondary ms-2 small">
                    {{ $entity->children->count() }} جهة فرعية
                </span>
            @endif
        </div>
        <div class="entity-status">
            @if($entity->is_active)
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">
                    <i class="fas fa-check-circle me-1"></i> نشط
                </span>
            @else
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">
                    <i class="fas fa-times-circle me-1"></i> غير نشط
                </span>
            @endif
        </div>
    </div>
    
    {{-- Recursively display children --}}
    @if($entity->children->count() > 0)
        <div class="entity-children">
            @foreach($entity->children as $child)
                @include('roles.partials._entity_tree_item', ['entity' => $child, 'level' => $level + 1, 'highlightEntityId' => $highlightEntityId ?? null])
            @endforeach
        </div>
    @endif
</div>
