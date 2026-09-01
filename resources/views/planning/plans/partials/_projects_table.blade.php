<div class="project-table-container">
    <div class="project-table-header">
        <div class="header-title">
            <i class="fas fa-tasks"></i>
            <span>إدارة مشاريع الخطة التشغيلية</span>
        </div>
        <div class="header-badge">
            <span id="projectCount">0</span> مشاريع
        </div>
    </div>

    <div class="project-cards-wrapper">
        <div class="project-cards" id="projectsCards">
            @if(isset($projects) && $projects->count() > 0)
                @foreach($projects as $index => $project)
                    <div class="project-card" data-project-index="{{ $index }}">
                        <!-- رأس البطاقة: اسم المشروع + الأولوية + الأزرار -->
                                <div class="card-header">
                                    <div class="project-name">
                                        <input type="text" name="projects[{{ $index }}][name]" class="form-control"
                                            value="{{ $project->name }}" placeholder="أدخل اسم المشروع" {{ ($readOnlyProjects ?? false) ? 'readonly' : 'required' }}>
                                    </div>

                                    <!-- ========== حقل الأولوية المضافة ========== -->
                                    <div class="project-priority">
                                        <label>الأولوية</label>
                                        <select name="projects[{{ $index }}][priority_id]" class="form-select select2-dynamic" {{ ($readOnlyProjects ?? false) ? 'disabled' : '' }}>
                                            <option value="">-- نفس أولوية الخطة --</option>
                                            @php
                                                $prioritiesList = $priorities ?? \App\Models\Priority::where('is_enabled', true)->get();
                                            @endphp
                                            @foreach($prioritiesList as $priority)
                                                <option value="{{ $priority->id }}" {{ old("projects.$index.priority_id", $project->priority_id) == $priority->id ? 'selected' : '' }}>
                                                    {{ $priority->priority }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($readOnlyProjects ?? false)
                                            <input type="hidden" name="projects[{{ $index }}][priority_id]"
                                                value="{{ $project->priority_id }}">
                                        @endif
                                    </div>
                                    <!-- ======================================== -->

                                    <div class="card-actions">
                                        <button type="button" class="btn-manage-goals btn btn-sm btn-outline-info"
                                            title="إدارة الأهداف الخاصة">
                                            <i class="fas fa-bullseye"></i>
                                            <span
                                                class="goal-count-badge badge bg-info text-white rounded-pill ms-1">{{ isset($project->goals) ? $project->goals->count() : 0 }}</span>
                                        </button>
                                        @if(!($hideActivities ?? false))
                                            <button type="button" class="btn-manage-activities btn btn-sm btn-outline-warning"
                                                title="إدارة الأنشطة">
                                                <i class="fas fa-list-check"></i>
                                                <span
                                                    class="activity-count-badge badge bg-warning text-dark rounded-pill ms-1">{{ isset($project->activities) ? $project->activities->count() : 0 }}</span>
                                            </button>
                                        @endif
                                        @if(!($readOnlyProjects ?? false))
                                            <button type="button" class="btn-remove-card remove-card btn btn-sm btn-outline-danger"
                                                title="حذف">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @else
                                            <span class="text-muted"><i class="fas fa-lock"></i></span>
                                        @endif
                                    </div>
                                </div>

                                <!-- باقي الحقول كما هي (السطر الأول والثاني) -->
                                <div class="card-row first-row">
                                    <div class="field-group">
                                        <label>الأهمية *</label>
                                        <select name="projects[{{ $index }}][importance]" class="form-select select2-dynamic" {{ ($readOnlyProjects ?? false) ? 'disabled' : 'required' }}>
                                            <option value="normal" {{ old("projects.$index.importance", $project->importance) == 'normal' ? 'selected' : '' }}>عادي</option>
                                            <option value="important" {{ old("projects.$index.importance", $project->importance) == 'important' ? 'selected' : '' }}>هام</option>
                                            <option value="very_important" {{ old("projects.$index.importance", $project->importance) == 'very_important' ? 'selected' : '' }}>هام جداً</option>
                                        </select>
                                        @if($readOnlyProjects ?? false)
                                            <input type="hidden" name="projects[{{ $index }}][importance]"
                                                value="{{ $project->importance }}">
                                        @endif
                                    </div>

                                    <div class="field-group">
                                        <label>الحالة *</label>
                                        <select name="projects[{{ $index }}][status]" class="form-select select2-dynamic" {{ ($readOnlyProjects ?? false) ? 'disabled' : 'required' }}>
                                            <option value="">اختر الحالة...</option>
                                            <option value="new" {{ old("projects.$index.status", $project->status) == 'new' ? 'selected' : '' }}>جديد</option>
                                            <option value="terminated" {{ old("projects.$index.status", $project->status) == 'terminated' ? 'selected' : '' }}>مرحل </option>
                                            @if(!in_array($project->status, ['new', 'terminated']))
                                                <option value="{{ $project->status }}" selected>{{ $project->status }}</option>
                                            @endif
                                        </select>
                                        @if($readOnlyProjects ?? false)
                                            <input type="hidden" name="projects[{{ $index }}][status]" value="{{ $project->status }}">
                                        @endif
                                    </div>

                                    <div class="field-group">
                                        <label>نوع التكلفة *</label>
                                        <select name="projects[{{ $index }}][cost_type]" class="form-select select2-dynamic" {{ ($readOnlyProjects ?? false) ? 'disabled' : 'required' }}>
                                            <option value="">اختر...</option>
                                            <option value="YER" {{ old("projects.$index.cost_type", $project->cost_type) == 'YER' ? 'selected' : '' }}>ريال يمني</option>
                                            <option value="USD" {{ old("projects.$index.cost_type", $project->cost_type) == 'USD' ? 'selected' : '' }}>دولار أمريكي</option>
                                            <option value="EUR" {{ old("projects.$index.cost_type", $project->cost_type) == 'EUR' ? 'selected' : '' }}>يورو</option>
                                        </select>
                                        @if($readOnlyProjects ?? false)
                                            <input type="hidden" name="projects[{{ $index }}][cost_type]" value="{{ $project->cost_type }}">
                                        @endif
                                    </div>

                                    <div class="field-group">
                                        <label>التكلفة *</label>
                                        <input type="number" name="projects[{{ $index }}][cost]" class="form-control text-end"
                                            value="{{ $project->cost }}" step="0.01" {{ ($readOnlyProjects ?? false) ? 'readonly' : 'required' }}>
                                    </div>
                                </div>

                                <div class="card-row second-row">
                                    <div class="field-group">
                                        <label>المؤشرات</label>
                                        <textarea name="projects[{{ $index }}][indicators]" class="form-control" rows="1" {{ ($readOnlyProjects ?? false) ? 'readonly' : '' }}>{{ $project->indicators }}</textarea>
                                    </div>

                                    <div class="field-group">
                                        <label>المخرجات</label>
                                        <textarea name="projects[{{ $index }}][outputs]" class="form-control" rows="1" {{ ($readOnlyProjects ?? false) ? 'readonly' : '' }}>{{ $project->outputs }}</textarea>
                                    </div>

                                    <div class="field-group">
                                        <label>الأساس</label>
                                        <input type="text" name="projects[{{ $index }}][baseline]" class="form-control"
                                            value="{{ $project->baseline }}" {{ ($readOnlyProjects ?? false) ? 'readonly' : '' }}>
                                    </div>

                                    <div class="field-group">
                                        <label>القيمة المستهدفة</label>
                                        <input type="number" name="projects[{{ $index }}][target_value]" class="form-control"
                                            value="{{ $project->target_value }}" step="0.01" {{ ($readOnlyProjects ?? false) ? 'readonly' : '' }}>
                                    </div>

                                    <div class="field-group">
                                        <label>توفر التمويل</label>
                                        <select name="projects[{{ $index }}][funding_availability]"
                                            class="form-select funding-availability-select" {{ ($readOnlyProjects ?? false) ? 'disabled' : '' }}>
                                            <option value="0" {{ !$project->funding_availability ? 'selected' : '' }}>غير متوفر</option>
                                            <option value="1" {{ $project->funding_availability ? 'selected' : '' }}>متوفر</option>
                                        </select>
                                        @if($readOnlyProjects ?? false)
                                            <input type="hidden" name="projects[{{ $index }}][funding_availability]"
                                                value="{{ $project->funding_availability ? '1' : '0' }}">
                                        @endif
                                    </div>

                                    <div
                                        class="field-group funding-source-container {{ $project->funding_availability ? '' : 'd-none' }}">
                                        <label>مصدر التمويل</label>
                                        <select name="projects[{{ $index }}][funding_source_id]"
                                            class="form-select funding-source-select select2-dynamic" {{ ($readOnlyProjects ?? false) ? 'disabled' : '' }}>
                                            <option value="">اختر المصدر...</option>
                                            @foreach($fundingSources as $source)
                                                <option value="{{ $source->id }}" {{ $project->funding_source_id == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($readOnlyProjects ?? false)
                                            <input type="hidden" name="projects[{{ $index }}][funding_source_id]"
                                                value="{{ $project->funding_source_id }}">
                                        @endif
                                    </div>

                                    <div class="field-group">
                                        <label>الجهة المشاركة *</label>
                                        <select name="projects[{{ $index }}][participating_entity_id]"
                                            class="form-select select2-dynamic" {{ ($readOnlyProjects ?? false) ? 'disabled' : 'required' }}>
                                            <option value="">اختر الجهة...</option>
                                            @foreach($entities as $entity)
                                                <option value="{{ $entity->id }}" {{ old("projects.$index.participating_entity_id", $project->participating_entity_id) == $entity->id ? 'selected' : '' }}>{{ $entity->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($readOnlyProjects ?? false)
                                            <input type="hidden" name="projects[{{ $index }}][participating_entity_id]"
                                                value="{{ $project->participating_entity_id }}">
                                        @endif
                                    </div>
                                </div>

                                @if(!($hideActivities ?? false))
                                    @php $inputName = ($readOnlyProjects ?? false) ? "projects[{$project->id}][activities_json]" : "projects[{$index}][activities_json]"; @endphp
                                    <input type="hidden" name="{{ $inputName }}" class="project-activities-input"
                                        value="{{ isset($project->activities) ? $project->activities->load('actions')->toJson() : '[]' }}">
                                @endif
                                @php $goalsInputName = ($readOnlyProjects ?? false) ? "projects[{$project->id}][goals_json]" : "projects[{$index}][goals_json]"; @endphp
                                <input type="hidden" name="{{ $goalsInputName }}" class="project-goals-input"
                                    value="{{ isset($project->goals) ? $project->goals->toJson() : '[]' }}">
                            </div>
                @endforeach
            @endif
        </div>
    </div>

    @if(!($readOnlyProjects ?? false))
        <div class="project-table-footer">
            <button type="button" class="btn-add-project" id="addProjectBtn">
                <i class="fas fa-plus"></i> إضافة مشروع جديد
            </button>
        </div>
    @endif
</div>

<template id="projectCardTemplate">
    <div class="project-card project-animated-card">
        <div class="card-header">
            <div class="project-name">
                <input type="text" name="projects[INDEX][name]" class="form-control" placeholder="اسم المشروع" required>
            </div>
            <div class="project-priority">
                <label>الأولوية</label>
                <select name="projects[INDEX][priority_id]" class="form-select select2-dynamic">
                    <option value="">-- نفس أولوية الخطة --</option>
                    @php
                        $prioritiesList = $priorities ?? \App\Models\Priority::where('is_enabled', true)->get();
                    @endphp
                    @foreach($prioritiesList as $priority)
                        <option value="{{ $priority->id }}">{{ $priority->priority }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-actions">
                <button type="button" class="btn-manage-goals btn btn-sm btn-outline-info"
                    title="إدارة الأهداف الخاصة">
                    <i class="fas fa-bullseye"></i>
                    <span class="goal-count-badge badge bg-info text-white rounded-pill ms-1">0</span>
                </button>
                @if(!($hideActivities ?? false))
                    <button type="button" class="btn-manage-activities btn btn-sm btn-outline-warning"
                        title="إدارة الأنشطة">
                        <i class="fas fa-list-check"></i>
                        <span class="activity-count-badge badge bg-warning text-dark rounded-pill ms-1">0</span>
                    </button>
                @endif
                <button type="button" class="btn-remove-card remove-card btn btn-sm btn-outline-danger" title="حذف">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>

        <div class="card-row first-row">
            <div class="field-group">
                <label>الأهمية *</label>
                <select name="projects[INDEX][importance]" class="form-select" required>
                    <option value="normal">عادي</option>
                    <option value="important">هام</option>
                    <option value="very_important">هام جداً</option>
                </select>
            </div>
            <div class="field-group">
                <label>الحالة *</label>
                <select name="projects[INDEX][status]" class="form-select" required>
                    <option value="new">جديد</option>
                    <option value="terminated">مرحل</option>
                </select>
            </div>
            <div class="field-group">
                <label>نوع التكلفة *</label>
                <select name="projects[INDEX][cost_type]" class="form-select" required>
                    <option value="YER">ريال يمني</option>
                    <option value="USD">دولار أمريكي</option>
                    <option value="EUR">يورو</option>
                </select>
            </div>
            <div class="field-group">
                <label>التكلفة *</label>
                <input type="number" name="projects[INDEX][cost]" class="form-control text-end" value="0" step="0.01"
                    required>
            </div>
        </div>

        <div class="card-row second-row">
            <div class="field-group">
                <label>المؤشرات</label>
                <textarea name="projects[INDEX][indicators]" class="form-control" rows="1"></textarea>
            </div>
            <div class="field-group">
                <label>المخرجات</label>
                <textarea name="projects[INDEX][outputs]" class="form-control" rows="1"></textarea>
            </div>
            <div class="field-group">
                <label>الأساس</label>
                <input type="text" name="projects[INDEX][baseline]" class="form-control">
            </div>
            <div class="field-group">
                <label>القيمة المستهدفة</label>
                <input type="number" name="projects[INDEX][target_value]" class="form-control" value="0" step="0.01">
            </div>
            <div class="field-group">
                <label>توفر التمويل</label>
                <select name="projects[INDEX][funding_availability]" class="form-select funding-availability-select">
                    <option value="0">غير متوفر</option>
                    <option value="1">متوفر</option>
                </select>
            </div>
            <div class="field-group funding-source-container d-none">
                <label>مصدر التمويل</label>
                <select name="projects[INDEX][funding_source_id]"
                    class="form-select funding-source-select select2-dynamic">
                    <option value="">اختر المصدر...</option>
                    @foreach($fundingSources as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-group">
                <label>الجهة المشاركة *</label>
                <select name="projects[INDEX][participating_entity_id]" class="form-select select2-dynamic" required>
                    <option value="">اختر الجهة...</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if(!($hideActivities ?? false))
            <input type="hidden" name="projects[INDEX][activities_json]" class="project-activities-input" value="[]">
        @endif
        <input type="hidden" name="projects[INDEX][goals_json]" class="project-goals-input" value="[]">
    </div>
</template>

<style>
    :root {
        --primary-color: #2563eb;
        --secondary-color: #64748b;
        --danger-color: #ef4444;
        --success-color: #10b981;
        --bg-light: #f8fafc;
        --border-color: #e2e8f0;
    }

    .project-table-container {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
        overflow: hidden;
    }

    .project-table-header {
        background: #1e293b;
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .header-badge {
        background: rgba(255, 255, 255, 0.1);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .project-cards-wrapper {
        padding: 16px;
        max-height: 600px;
        overflow-y: auto;
        background: var(--bg-light);
    }

    .project-cards {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .project-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
    }

    .project-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: var(--primary-color);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }

    .project-name {
        flex: 2;
        min-width: 200px;
    }

    .project-priority {
        flex: 1;
        min-width: 150px;
    }

    .project-priority label {
        font-size: 0.7rem;
        margin-bottom: 2px;
        color: var(--secondary-color);
    }

    .card-actions {
        display: flex;
        gap: 8px;
    }

    .card-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 12px;
    }

    .field-group {
        flex: 1 1 200px;
        min-width: 150px;
    }

    .field-group label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--secondary-color);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .field-group .form-control,
    .field-group .form-select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 0.85rem;
        transition: border-color 0.2s;
    }

    .field-group .form-control:focus,
    .field-group .form-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .funding-source-container {
        transition: opacity 0.2s;
    }

    /* أزرار الإجراءات */
    .btn-remove-card {
        background: #fee2e2;
        color: var(--danger-color);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .btn-remove-card:hover {
        background: var(--danger-color);
        color: white;
    }

    .btn-manage-activities {
        background: #e0f2fe;
        color: #0369a1;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-manage-activities:hover {
        background: #0ea5e9;
        color: white;
    }

    .activity-count-badge {
        background: #0369a1;
        color: white;
        font-size: 0.7rem;
        padding: 1px 6px;
        border-radius: 10px;
        min-width: 18px;
    }

    .btn-manage-activities:hover .activity-count-badge {
        background: white;
        color: #0ea5e9;
    }

    .btn-manage-goals {
        background: #e0f2fe;
        color: #0284c7;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-manage-goals:hover {
        background: #0284c7;
        color: white;
    }

    .goal-count-badge {
        background: #0284c7;
        color: white;
        font-size: 0.7rem;
        padding: 1px 6px;
        border-radius: 10px;
        min-width: 18px;
    }

    .btn-manage-goals:hover .goal-count-badge {
        background: white;
        color: #0284c7;
    }

    /* تذييل */
    .project-table-footer {
        padding: 15px 20px;
        background: var(--bg-light);
        border-top: 1px solid var(--border-color);
    }

    .btn-add-project {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-add-project:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .project-animated-card {
        animation: slideIn 0.3s ease-out forwards;
    }

    /* تجاوب للشاشات الصغيرة */
    @media (max-width: 768px) {
        .card-row {
            flex-direction: column;
            gap: 8px;
        }

        .field-group {
            width: 100%;
        }

        .card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .project-name,
        .project-priority {
            width: 100%;
        }
    }
</style>

@if(!($hideActivities ?? false))
    @include('planning.plans.partials._activities_modal')
@endif
@include('planning.plans.partials._goals_modal')

@push('scripts')
    <script>
        $(document).ready(function () {
            const cardsContainer = document.querySelector('#projectsCards');
            const addBtn = document.getElementById('addProjectBtn');
            const countBadge = document.getElementById('projectCount');
            const template = document.getElementById('projectCardTemplate')?.innerHTML || '';

            let cardIndex = {{ isset($projects) ? $projects->count() : 0 }};

            function updateProjectCount() {
                const count = document.querySelectorAll('.project-card').length;
                if (countBadge) countBadge.textContent = count;
            }

            function initSelect2(element) {
                if (typeof $.fn.select2 !== 'undefined') {
                    $(element).find('.select2-dynamic').select2({
                        placeholder: "اختر...",
                        allowClear: true,
                        width: '100%',
                        dir: 'rtl',
                        dropdownParent: $(element).closest('.project-table-container').length ? $(element).closest('.project-table-container') : $(document.body)
                    });
                }
            }

            function setupCardEvents(card) {
                // إظهار/إخفاء مصدر التمويل
                const availabilitySelect = card.querySelector('.funding-availability-select');
                const sourceContainer = card.querySelector('.funding-source-container');

                if (availabilitySelect && sourceContainer) {
                    availabilitySelect.addEventListener('change', function () {
                        if (this.value === '1') {
                            sourceContainer.classList.remove('d-none');
                            sourceContainer.classList.add('project-animated-card');
                        } else {
                            sourceContainer.classList.add('d-none');
                            const sourceSelect = sourceContainer.querySelector('select');
                            if (sourceSelect) {
                                $(sourceSelect).val(null).trigger('change');
                            }
                        }
                    });
                }

                // حذف البطاقة
                const removeBtn = card.querySelector('.remove-card');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function () {
                        const cards = document.querySelectorAll('.project-card');
                        if (cards.length > 1) {
                            card.style.opacity = '0';
                            card.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                card.remove();
                                updateProjectCount();
                            }, 300);
                        } else {
                            alert('لا يمكن حذف آخر مشروع. يجب وجود مشروع واحد على الأقل.');
                        }
                    });
                }

                // إدارة الأنشطة
                const manageBtn = card.querySelector('.btn-manage-activities');
                if (manageBtn && typeof ActivitiesManager !== 'undefined') {
                    manageBtn.addEventListener('click', function () {
                        ActivitiesManager.open(card);
                    });
                }

                // إدارة الأهداف
                const manageGoalsBtn = card.querySelector('.btn-manage-goals');
                if (manageGoalsBtn && typeof GoalsManager !== 'undefined') {
                    manageGoalsBtn.addEventListener('click', function () {
                        GoalsManager.open(card);
                    });
                }

                initSelect2(card);
            }

            function addCard() {
                if (!template) return;
                const html = template.replace(/INDEX/g, cardIndex);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newCard = tempDiv.firstElementChild;

                cardsContainer.appendChild(newCard);
                setupCardEvents(newCard);
                cardIndex++;
                updateProjectCount();

                // تمرير سلس لأول البطاقات الجديدة
                newCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // تهيئة البطاقات الموجودة
            document.querySelectorAll('.project-card').forEach(card => {
                setupCardEvents(card);
            });

            // زر الإضافة
            if (addBtn) {
                addBtn.addEventListener('click', addCard);
            }

            // إضافة بطاقة افتراضية إذا لم توجد بطاقات (اختياري)
            if (cardIndex === 0 && template) {
                addCard();
            }

            updateProjectCount();

            // Form Global Validation for Operational Plan
            const planForm = document.getElementById('planForm');
            if (planForm) {
                planForm.addEventListener('submit', function (e) {
                    let isValid = true;
                    let errorMsgs = [];

                    document.querySelectorAll('.project-card').forEach((card, i) => {
                        const projectNameInput = card.querySelector('input[name*="[name]"]');
                        const projectName = projectNameInput ? projectNameInput.value : `مشروع ${i + 1}`;
                        const activitiesInput = card.querySelector('.project-activities-input');

                        if (activitiesInput && activitiesInput.value) {
                            try {
                                const activities = JSON.parse(activitiesInput.value);
                                if (activities.length > 0) {
                                    // 1. Check activities weight
                                    const totalWeight = activities.reduce((sum, act) => sum + (parseFloat(act.weight) || 0), 0);
                                    if (Math.abs(totalWeight - 100) > 0.1) {
                                        isValid = false;
                                        errorMsgs.push(`المشروع "${projectName}": مجموع أوزان الأنشطة يجب أن يكون 100% (المجموع الحالي: ${totalWeight.toFixed(1)}%)`);
                                    }

                                    // 2. Check actions weight for each activity
                                    activities.forEach(act => {
                                        if (act.actions && act.actions.length > 0) {
                                            const totalActionsWeight = act.actions.reduce((sum, action) => sum + (parseFloat(action.weight) || 0), 0);
                                            if (Math.abs(totalActionsWeight - 100) > 0.1) {
                                                isValid = false;
                                                errorMsgs.push(`النشاط "${act.name}" في مشروع "${projectName}": مجموع أوزان الإجراءات يجب أن يكون 100% (المجموع الحالي: ${totalActionsWeight.toFixed(1)}%)`);
                                            }
                                        }
                                    });
                                }
                            } catch (err) {
                                console.error('Error parsing activities JSON:', err);
                            }
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في التحقق من الأوزان',
                            html: '<div class="text-start">' + errorMsgs.join('<br>') + '</div>',
                            confirmButtonText: 'حسناً'
                        });
                    }
                });
            }
        });
    </script>
@endpush