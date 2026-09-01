{{--
    Unified Project Table Template
    
    Usage:
    @include('projects.partials.tables._unified_table_template', [
        'tableId' => 'myTable',
        'title' => 'عنوان الجدول',
        'icon' => 'fas fa-table',
        'headers' => ['العمود الأول', 'العمود الثاني', 'الإجراءات'],
        'emptyMessage' => 'لا توجد بيانات',
        'addButtonText' => 'إضافة جديد',
        'addButtonId' => 'addBtn',
        'showAddButton' => true
    ])
--}}

<div class="project-table-container">
    <div class="project-table-header">
        @if(isset($icon))
            <i class="{{ $icon }}"></i>
        @endif
        {{ $title ?? 'جدول البيانات' }}
    </div>
    
    <div class="project-table-wrapper">
        <div class="table-responsive">
            <table class="project-table" id="{{ $tableId ?? 'projectTable' }}">
                @if(isset($headers) && count($headers) > 0)
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                @endif
                
                <tbody>
                    @if(isset($hasData) && $hasData)
                        {{ $slot }}
                    @else
                        <tr class="project-empty-row">
                            <td colspan="{{ isset($headers) ? count($headers) : 1 }}" class="text-center">
                                @if(isset($emptyIcon))
                                    <i class="{{ $emptyIcon }} fa-2x mb-2"></i><br>
                                @endif
                                {{ $emptyMessage ?? 'لا توجد بيانات متاحة' }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        @if(isset($showAddButton) && $showAddButton)
        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="{{ $addButtonId ?? 'addBtn' }}">
                <i class="fas fa-plus"></i>
                {{ $addButtonText ?? 'إضافة جديد' }}
            </button>
        </div>
        @endif
    </div>
</div>

@if(isset($includeScript) && $includeScript)
    {{ $script ?? '' }}
@endif