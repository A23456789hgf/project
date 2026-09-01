<style>
    /* تنسيقات عامة لتصغير النموذج */
    .compact-form .row {
        margin-bottom: 0.75rem !important;
    }
    .compact-form .form-label {
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 0.2rem;
        color: #444;
    }
    .compact-form .form-control-sm, 
    .compact-form .form-select-sm {
        font-size: 0.85rem;
        border-radius: 4px;
    }
    
    /* تخصيص Select2 */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da !important;
        min-height: 31px !important; 
        font-size: 0.85rem !important;
        border-radius: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px !important;
        padding-right: 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 28px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        color: #fff;
        border: none;
        font-size: 0.75rem;
        margin-top: 4px;
    }
    .select2-results__option {
        font-size: 0.85rem !important;
        text-align: right !important;
    }

    /* حقول التواريخ */
    input[readonly] {
        background-color: #f9f9f9 !important;
        color: #555;
        border-style: dashed;
    }
    .text-muted-xs {
        font-size: 0.7rem;
        display: block;
        margin-top: 2px;
    }
    
    /* نص توضيحي تحت الحقول */
    .date-note {
        font-size: 0.7rem;
        color: #6c757d;
        margin-top: 2px;
    }
</style>

<div class="container-fluid compact-form py-2" dir="rtl">
    <input type="hidden" name="project_type" id="type_new" value="{{ old('project_type', $project->project_type ?? 'new') }}">

    <div id="old-project-cost-step1" class="row g-2 mb-3 p-3 bg-light border rounded" style="{{ old('project_type', $project->project_type ?? 'new') == 'old' ? '' : 'display: none;' }}">
        <div class="col-12 mb-1">
            <h6 class="text-primary m-0"><i class="fas fa-history me-1"></i> حفظ المشروع القديم مباشرة (بدون الحاجة لملء باقي الخطوات)</h6>
            <small class="text-muted">يمكنك حفظ المشروع بالاسم والتكلفة فقط الآن، وإكمال إدخال باقي البيانات والجهات لاحقاً إذا رغبت في ذلك.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">السنة الهجرية</label>
            <input type="number" class="form-control form-control-sm" name="project_cost[hijri_year]" 
                   value="{{ old('project_cost.hijri_year', $project->cost->hijri_year ?? '') }}" placeholder="مثال: 1445">
        </div>
        <div class="col-md-4">
            <label class="form-label">إجمالي تكلفة المشروع <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control form-control-sm" name="project_cost[total_cost]" 
                   id="step1_total_cost"
                   value="{{ old('project_cost.total_cost', $project->cost->total_cost ?? '') }}" placeholder="0.00">
        </div>
        <div class="col-md-4">
            <label class="form-label">المبلغ المصروف</label>
            <input type="number" step="0.01" class="form-control form-control-sm" name="project_cost[spent_amount]" 
                   value="{{ old('project_cost.spent_amount', $project->cost->spent_amount ?? '') }}" placeholder="0.00">
        </div>
        <div class="col-12 mt-3" id="old-project-financing-container">
            <h6 class="text-primary mt-2 mb-2"><i class="fas fa-coins me-1"></i> مصادر التمويل / بيانات التمويل <span class="text-danger">*</span></h6>
        </div>
        <div class="col-12 mt-3 d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm px-3" onclick="FormManager.saveOldProject(true)">
                <i class="fas fa-check-circle me-1"></i> حفظ المشروع القديم (اعتماد نهائي)
            </button>
            <button type="button" class="btn btn-secondary btn-sm px-3" onclick="FormManager.saveOldProject(false)">
                <i class="fas fa-save me-1"></i> حفظ كمسودة (لإكمال البيانات والجهات لاحقاً)
            </button>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label">اسم المشروع <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" name="project_name" id="project_name_input"
                   value="{{ old('project_name', $project->project_name ?? '') }}" required autocomplete="off">
            <span id="project_name_feedback" class="text-danger small mt-1" style="display: none; font-size: 0.78rem;"></span>
        </div>
        <div class="col-md-4">
            <label class="form-label">البرنامج <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm select-search" name="program_id" id="program_id"
                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="program"
                    data-custom-wrapper-id="custom_program_wrapper" required>
                <option value="">اختر البرنامج</option>
                @php 
                    $selectedProgramId = old('program_id', $project->program_id ?? '');
                    $initialPrograms = $programs->take(10);
                    if ($selectedProgramId && !$initialPrograms->contains('id', $selectedProgramId)) {
                        $selectedProgram = $programs->firstWhere('id', $selectedProgramId) ?? \App\Models\Program::withoutGlobalScope(\App\Scopes\DomainScope::class)->find($selectedProgramId);
                        if ($selectedProgram) $initialPrograms->push($selectedProgram);
                    }
                @endphp
                @foreach($initialPrograms as $program)
                    <option value="{{ $program->id }}" {{ $selectedProgramId == $program->id ? 'selected' : '' }}>
                        {{ $program->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4" id="custom_program_wrapper" style="display: none;">
            <label class="form-label text-primary"><i class="fas fa-plus-circle me-1"></i> اسم البرنامج الجديد <span class="text-danger">*</span></label>
            <input type="text" name="custom_program_name" id="custom_program_name" 
                   class="form-control form-control-sm" 
                   placeholder="أدخل اسم البرنامج الجديد..." 
                   value="{{ old('custom_program_name') }}">
            <small class="text-muted" style="font-size: 0.75rem;">سيتم حفظه بانتظار المراجعة والاعتماد.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label">الأولوية</label>
            <select name="priority_id" id="priority_id" class="form-select form-select-sm select-search" 
                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="priority"
                    data-custom-wrapper-id="custom_priority_wrapper">
                <option value="">-- اختر --</option>
                @php 
                    $selectedPriorityId = old('priority_id', $project->priority_id ?? '');
                    $initialPriorities = $priorities->take(10);
                    if ($selectedPriorityId && !$initialPriorities->contains('id', $selectedPriorityId)) {
                        $selectedPriority = $priorities->firstWhere('id', $selectedPriorityId) ?? \App\Models\Priority::find($selectedPriorityId);
                        if ($selectedPriority) $initialPriorities->push($selectedPriority);
                    }
                @endphp
                @foreach($initialPriorities as $priority)
                    <option value="{{ $priority->id }}" {{ $selectedPriorityId == $priority->id ? 'selected' : '' }}>
                        {{ $priority->priority }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4" id="custom_priority_wrapper" style="display: none;">
            <label class="form-label text-primary"><i class="fas fa-plus-circle me-1"></i> الأولوية الجديدة <span class="text-danger">*</span></label>
            <input type="text" name="custom_priority_name" id="custom_priority_name" 
                   class="form-control form-control-sm" 
                   placeholder="أدخل الأولوية الجديدة..." 
                   value="{{ old('custom_priority_name') }}">
            <small class="text-muted" style="font-size: 0.75rem;">سيتم حفظه بانتظار المراجعة والاعتماد.</small>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label">المجال <span class="text-danger">*</span></label>
            <select name="domain_id" id="domain_id" class="form-select form-select-sm select-search" 
                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="domain"
                    data-custom-wrapper-id="custom_domain_wrapper" required>
                <option value="">اختر المجال</option>
                @php 
                    $selectedDomainId = old('domain_id', $project->domain_id ?? '');
                    $initialDomains = $domains->take(10);
                    if ($selectedDomainId && !$initialDomains->contains('id', $selectedDomainId)) {
                        $selectedDomain = $domains->firstWhere('id', $selectedDomainId) ?? \App\Models\Domain::withoutGlobalScope(\App\Scopes\DomainScope::class)->find($selectedDomainId);
                        if ($selectedDomain) $initialDomains->push($selectedDomain);
                    }
                @endphp
                @foreach($initialDomains as $domain)
                    <option value="{{ $domain->id }}" {{ $selectedDomainId == $domain->id ? 'selected' : '' }}>
                        {{ $domain->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4" id="custom_domain_wrapper" style="display: none;">
            <label class="form-label text-primary"><i class="fas fa-plus-circle me-1"></i> اسم المجال الجديد <span class="text-danger">*</span></label>
            <input type="text" name="custom_domain_name" id="custom_domain_name" 
                   class="form-control form-control-sm" 
                   placeholder="أدخل اسم المجال الجديد..." 
                   value="{{ old('custom_domain_name') }}">
            <small class="text-muted" style="font-size: 0.75rem;">سيتم حفظه بانتظار المراجعة والاعتماد.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label">المجال الفرعي <span class="text-danger">*</span></label>
            <select name="subdomain_id" id="subdomain_id" class="form-select form-select-sm select-search" 
                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="subdomain" 
                    data-ajax-params="domain_id=#domain_id"
                    data-custom-wrapper-id="custom_subdomain_wrapper" required>
                <option value="">اختر المجال الفرعي</option>
                @php 
                    $selectedSubdomainId = old('subdomain_id', $project->subdomain_id ?? '');
                    $initialSubdomains = $subdomains->take(10);
                    if ($selectedSubdomainId && !$initialSubdomains->contains('id', $selectedSubdomainId)) {
                        $selectedSubdomain = $subdomains->firstWhere('id', $selectedSubdomainId) ?? \App\Models\Subdomain::withoutGlobalScope(\App\Scopes\DomainScope::class)->find($selectedSubdomainId);
                        if ($selectedSubdomain) $initialSubdomains->push($selectedSubdomain);
                    }
                @endphp
                @foreach($initialSubdomains as $subdomain)
                    <option value="{{ $subdomain->id }}" {{ $selectedSubdomainId == $subdomain->id ? 'selected' : '' }}>
                        {{ $subdomain->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4" id="custom_subdomain_wrapper" style="display: none;">
            <label class="form-label text-primary"><i class="fas fa-plus-circle me-1"></i> اسم المجال الفرعي الجديد <span class="text-danger">*</span></label>
            <input type="text" name="custom_subdomain_name" id="custom_subdomain_name" 
                   class="form-control form-control-sm" 
                   placeholder="أدخل اسم المجال الفرعي الجديد..." 
                   value="{{ old('custom_subdomain_name') }}">
            <small class="text-muted" style="font-size: 0.75rem;">سيتم حفظه بانتظار المراجعة والاعتماد.</small>
        </div>

        <div class="col-md-4">
            <label class="form-label">نوع التدخل <span class="text-danger">*</span></label>
            <select name="intervention_id" id="intervention_id" class="form-select form-select-sm select-search" 
                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="intervention" 
                    data-ajax-params="subdomain_id=#subdomain_id"
                    data-custom-wrapper-id="custom_intervention_wrapper" required>
                <option value="">اختر نوع التدخل</option>
                @php 
                    $selectedInterventionId = old('intervention_id', $project->intervention_id ?? '');
                    $initialInterventions = $interventions->take(10);
                    if ($selectedInterventionId && !$initialInterventions->contains('id', $selectedInterventionId)) {
                        $selectedIntervention = $interventions->firstWhere('id', $selectedInterventionId) ?? \App\Models\Intervention::withoutGlobalScope(\App\Scopes\DomainScope::class)->find($selectedInterventionId);
                        if ($selectedIntervention) $initialInterventions->push($selectedIntervention);
                    }
                @endphp
                @foreach($initialInterventions as $intervention)
                    <option value="{{ $intervention->id }}" {{ $selectedInterventionId == $intervention->id ? 'selected' : '' }}>
                        {{ $intervention->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4" id="custom_intervention_wrapper" style="display: none;">
            <label class="form-label text-primary"><i class="fas fa-plus-circle me-1"></i> اسم التدخل الجديد <span class="text-danger">*</span></label>
            <input type="text" name="custom_intervention_name" id="custom_intervention_name" 
                   class="form-control form-control-sm" 
                   placeholder="أدخل اسم التدخل الجديد..." 
                   value="{{ old('custom_intervention_name') }}">
            <small class="text-muted" style="font-size: 0.75rem;">سيتم إرسال اسم التدخل للمراجعة والاعتماد بعد الحفظ.</small>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">بداية (ميلادي) <span class="text-danger">*</span></label>
            <input type="date" class="form-control form-control-sm" name="start_date_gregorian" 
                   id="start_date_gregorian" 
                   data-hijri-target="#start_date_hijri"
                   value="{{ old('start_date_gregorian', $project->start_date_gregorian ?? '') }}" required>
            <small class="date-note">سيتم عرض التاريخ الهجري تلقائياً</small>
        </div>
        <div class="col-md-3">
            <label class="form-label">بداية (هجري)</label>
            <input type="text" class="form-control form-control-sm" id="start_date_hijri" 
                   name="start_date_hijri" 
                   placeholder="DD/MM/YYYY"
                   value="{{ old('start_date_hijri', $project->start_date_hijri ?? '') }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label">نهاية (ميلادي) <span class="text-danger">*</span></label>
            <input type="date" class="form-control form-control-sm" name="end_date_gregorian" 
                   id="end_date_gregorian" 
                   data-hijri-target="#end_date_hijri"
                   value="{{ old('end_date_gregorian', $project->end_date_gregorian ?? '') }}" required>
            <small class="date-note">سيتم عرض التاريخ الهجري تلقائياً</small>
        </div>
        <div class="col-md-3">
            <label class="form-label">نهاية (هجري)</label>
            <input type="text" class="form-control form-control-sm" id="end_date_hijri" 
                   name="end_date_hijri" 
                   placeholder="DD/MM/YYYY"
                   value="{{ old('end_date_hijri', $project->end_date_hijri ?? '') }}" readonly>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label">عدد المستفيدين</label>
            <input type="number" class="form-control form-control-sm" name="number_of_beneficiaries" 
                   value="{{ old('number_of_beneficiaries', $project->number_of_beneficiaries ?? '') }}" 
                   min="1">
        </div>
        <div class="col-md-8">
            <label class="form-label">مجموعات المستفيدين <span class="text-danger">*</span></label>
            <select name="beneficiary_groups[]" class="form-select form-select-sm select-search" 
                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="beneficiary_group" multiple required>
                @php 
                    $selectedBeneficiaryGroupIds = (array)old('beneficiary_groups', isset($project) ? $project->beneficiaryGroups->pluck('id')->toArray() : []);
                    $initialGroups = $beneficiaryGroups->take(10);
                    foreach ($selectedBeneficiaryGroupIds as $selectedId) {
                        if (!$initialGroups->contains('id', $selectedId)) {
                            $selectedGroup = $beneficiaryGroups->firstWhere('id', $selectedId) ?? \App\Models\BeneficiaryGroup::find($selectedId);
                            if ($selectedGroup) $initialGroups->push($selectedGroup);
                        }
                    }
                @endphp
                @foreach($initialGroups as $group)
                    <option value="{{ $group->id }}" {{ in_array($group->id, $selectedBeneficiaryGroupIds) ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @include('projects.partials.tables.project_location')
</div>

<script>
$(document).ready(function() {

    // ─── Cascade Guard ───────────────────────────────────────────────────────
    // During page initialisation (draft resume / edit mode) Select2 fires
    // 'change' events while setting pre-selected values.  We must NOT let
    // those events cascade-reset the child dropdowns.
    let cascadeInitializing = true;

    // Restore child values once Select2 finishes its own init cycle.
    // Select2 typically finishes within 300-500 ms; 800 ms is safe.
    setTimeout(function () {
        cascadeInitializing = false;
    }, 800);

    // Domain → Subdomain reset (user interaction only)
    $('#domain_id').on('change', function () {
        if (cascadeInitializing) return;      // skip during page init
        $('#subdomain_id').val(null).trigger('change');
    });

    // Subdomain → Intervention reset (user interaction only)
    $('#subdomain_id').on('change', function () {
        if (cascadeInitializing) return;      // skip during page init
        $('#intervention_id').val(null).trigger('change');
    });

    // ─── Custom-other toggles are handled globally by app.blade.php ─────────

    // Function to validate dates
    function validateDates() {
        const start = $('#start_date_gregorian').val();
        const end = $('#end_date_gregorian').val();
        
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            
            if (endDate < startDate) {
                alert('⚠️ تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
                $('#end_date_gregorian').val('');
                return false;
            }
        }
        return true;
    }

    $('#start_date_gregorian, #end_date_gregorian').on('change input', function() {
        validateDates();
    });

    $('form').on('submit', function(e) {
        if (!validateDates()) {
            e.preventDefault();
            return false;
        }
        return true;
    });
});
</script>

<script>
// Beneficiary Entities Section Visibility — show only for "old" project type
(function () {
    'use strict';

    function toggleBeneficiarySection(isOld) {
        const section = document.getElementById('beneficiary-entities-section');
        if (!section) return;

        if (isOld) {
            section.style.display = '';
            // Re-enable all inputs/selects inside the section
            section.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                el.disabled = false;
            });
        } else {
            section.style.display = 'none';
            // Disable all inputs/selects so they are excluded from form submission
            section.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                el.disabled = true;
            });
        }

        // Re-sync the project entities aggregated table after visibility change
        setTimeout(function () {
            if (window.ProjectEntitiesManager) {
                window.ProjectEntitiesManager.syncEntities();
            }
        }, 100);
    }

    function toggleOldProjectFinancingSection(isOld) {
        var wrapper = document.getElementById('financing-section-wrapper');
        if (!wrapper) return;
        if (isOld) {
            var oldContainer = document.getElementById('old-project-financing-container');
            if (oldContainer && wrapper.parentNode !== oldContainer) {
                oldContainer.appendChild(wrapper);
            }
        } else {
            var step6Placeholder = document.getElementById('step6-financing-placeholder');
            if (step6Placeholder && wrapper.parentNode !== step6Placeholder) {
                step6Placeholder.appendChild(wrapper);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Set initial state
        var checkedRadio = document.querySelector('input[name="project_type"]:checked');
        var isOld = checkedRadio && checkedRadio.value === 'old';
        toggleBeneficiarySection(isOld);
        toggleOldProjectFinancingSection(isOld);

        // Listen for changes
        document.querySelectorAll('input[name="project_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isOld = this.value === 'old';
                toggleBeneficiarySection(isOld);
                toggleOldProjectFinancingSection(isOld);
                var box = document.getElementById('old-project-cost-step1');
                if (box) box.style.display = isOld ? '' : 'none';
                if (typeof toggleOldProjectCostFields === 'function') toggleOldProjectCostFields();
            });
        });

        document.addEventListener('input', function(e) {
            if (e.target && e.target.name && e.target.name.startsWith('project_cost[')) {
                document.querySelectorAll(`[name="${e.target.name}"]`).forEach(function(el) {
                    if (el !== e.target) el.value = e.target.value;
                });
            }
        });
    });

    // =========================================================================
    // Real-time Exact Project Name Duplicate Validation
    // =========================================================================
    function initProjectNameValidation() {
        var projectNameInput = document.getElementById('project_name_input');
        var projectNameFeedback = document.getElementById('project_name_feedback');
        var currentProjectId = '{{ $project->id ?? '' }}';
        var typingTimer;
        var doneTypingInterval = 400; // ms debounce

        if (projectNameInput && !projectNameInput.hasAttribute('data-validation-bound')) {
            projectNameInput.setAttribute('data-validation-bound', 'true');

            function performExactNameCheck() {
                var val = projectNameInput.value.trim();
                
                if (!val) {
                    if (projectNameFeedback) projectNameFeedback.style.display = 'none';
                    projectNameInput.classList.remove('is-invalid');
                    return;
                }

                fetch('{{ route('projects.check-name') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        project_name: val,
                        exclude_id: currentProjectId || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        if (projectNameFeedback) {
                            projectNameFeedback.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> ' + (data.message || 'يوجد مشروع مسجل مسبقاً بهذا الاسم!');
                            projectNameFeedback.style.display = 'block';
                        }
                        projectNameInput.classList.add('is-invalid');
                    } else {
                        if (projectNameFeedback) projectNameFeedback.style.display = 'none';
                        projectNameInput.classList.remove('is-invalid');
                    }
                })
                .catch(error => console.error('Error checking project name:', error));
            }

            projectNameInput.addEventListener('input', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(performExactNameCheck, doneTypingInterval);
            });
        }
    }

    // Initialize immediately (for AJAX loaded content)
    initProjectNameValidation();
    
    // Also initialize on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProjectNameValidation);
    }
})();
</script>