<!-- Approval Form -->
<div class="approval-form-wrapper" data-project-id="{{ $project->id }}">

    <div class="mt-4 pt-3 border-top">
        <div class="section-header">
            <i class="fas fa-pen-square"></i>
            تقديم الموافقة أو الرفض
        </div>

        <form id="approvalForm" enctype="multipart/form-data" data-project-id="{{ $project->id }}">
            @csrf
            <div class="row g-3">
                <!-- Drop Selection Dropdown -->
                <div class="col-md-12">
                    <label for="drop" class="form-label fw-semibold">
                        <i class="fas fa-layer-group text-primary"></i> المرحلة الحالية للاعتماد
                    </label>
                    <select id="drop" name="drop" class="form-select" required onchange="handleDropChange()">
                        <option value="">-- اختر المرحلة --</option>
                        @if($approvalStages && count($approvalStages) > 0)
                            @foreach($approvalStages as $stage)
                                @php
                                    $isCurrent = $stage['is_current_stage'] ?? false;
                                @endphp
                                <option
                                    value="{{ $stage['drop'] }}"
                                    data-entity-id="{{ $stage['entity_id'] }}"
                                    data-drop-order="{{ $stage['drop_order'] }}"
                                    data-stage-id="{{ $stage['drop'] }}"
                                    {{ $isCurrent ? 'selected' : '' }}
                                    style="{{ $isCurrent ? 'font-weight:700;color:#003d7a;' : 'color:#6c757d;' }}"
                                >
                                    @if($isCurrent)
                                        ◉ المرحلة {{ $stage['drop_order'] }}: {{ $stage['stage_name'] }} — الجهة الحالية
                                    @else
                                        ○ المرحلة {{ $stage['drop_order'] }}: {{ $stage['stage_name'] }}
                                    @endif
                                </option>
                            @endforeach
                        @else
                            {{-- 
                                Fallback: Display ONLY the submitting project's entity and its parent chain up to root.
                                Never display unrelated entities.
                            --}}
                            @php
                                $currentStageEntityId = null;
                                if ($project->current_stage && str_starts_with($project->current_stage, 'entity_')) {
                                    $currentStageEntityId = (int) str_replace('entity_', '', $project->current_stage);
                                }
                                $originEntityId = $project->creator_entity_id 
                                    ?? $project->internal_entity_id 
                                    ?? $currentStageEntityId 
                                    ?? optional($project->createdBy)->entity_id 
                                    ?? optional($project->createdBy)->creator_entity_id;

                                if (!$originEntityId && $project->created_by_entity) {
                                    if (is_numeric($project->created_by_entity)) {
                                        $originEntityId = (int) $project->created_by_entity;
                                    } else {
                                        $found = \App\Models\InternalEntity::withoutGlobalScopes()
                                            ->where('name', trim($project->created_by_entity))
                                            ->first() ?? \App\Models\InternalEntity::withoutGlobalScopes()
                                            ->where('name', 'like', '%' . trim($project->created_by_entity) . '%')
                                            ->first();
                                        if ($found) $originEntityId = $found->id;
                                    }
                                }

                                if (!$originEntityId) {
                                    $imp = \App\Models\ProjectImplementingEntity::where('project_id', $project->id)
                                        ->whereNotNull('internal_entity_id')
                                        ->first();
                                    if ($imp) $originEntityId = $imp->internal_entity_id;
                                }

                                if (!$originEntityId) {
                                    $sup = \App\Models\ProjectSupervisingAuthority::where('project_id', $project->id)
                                        ->whereNotNull('authority_id')
                                        ->first();
                                    if ($sup) $originEntityId = $sup->authority_id;
                                }

                                if (!$originEntityId) {
                                    $originEntityId = auth()->user()?->entity_id ?? auth()->user()?->creator_entity_id;
                                }

                                if (!$originEntityId) {
                                    $firstEntity = \App\Models\InternalEntity::withoutGlobalScopes()->where('is_active', true)->first();
                                    if ($firstEntity) $originEntityId = $firstEntity->id;
                                }
                                
                                $chainEntities = collect();
                                if ($originEntityId) {
                                    $originEntity = \App\Models\InternalEntity::withoutGlobalScopes()->find($originEntityId);
                                    if ($originEntity) {
                                        $chainEntities = $originEntity->getApprovalChainToRoot();
                                    }
                                }
                            @endphp
                            @forelse($chainEntities as $index => $ent)
                                <option
                                    value="entity_{{ $ent->id }}"
                                    data-entity-id="{{ $ent->id }}"
                                    data-drop-order="{{ $index + 1 }}"
                                    data-stage-id="entity_{{ $ent->id }}"
                                    {{ ($currentStageEntityId == $ent->id || $originEntityId == $ent->id) ? 'selected' : '' }}
                                    style="{{ ($currentStageEntityId == $ent->id || $originEntityId == $ent->id) ? 'font-weight:700;color:#003d7a;' : '' }}"
                                >
                                    المرحلة {{ $index + 1 }}: {{ $ent->name }}
                                </option>
                            @empty
                                <option value="" disabled>تعذّر تحديد سلسلة اعتمادات الجهة والآباء</option>
                            @endforelse
                        @endif

                    </select>
                    <small style="color: #6c757d; margin-top: 0.25rem; display: block;">
                        <i class="fas fa-info-circle"></i>
                        المرحلة المحددة هي الجهة المسؤولة عن الاعتماد حالياً
                    </small>
                </div>






                <!-- Status Selection Dropdown -->
                <div class="form-group" style="flex: 1;">
                    <label for="status" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-tasks" style="color: #28a745;"></i> حالة الموافقة
                    </label>
                    <select id="status" name="status" class="form-control" required style="height: 40px; padding: 0.5rem; border-radius: 6px;" onchange="handleStatusChange()">
                        <option value="">-- اختر الحالة --</option>
                        <option value="approved">✓ موافق</option>
                        <option value="financial_technical_review">⚙ مراجعة مالية وفنية</option>
                        <option value="need_action">⚠ يحتاج إلى إجراء</option>
                        <option value="rejected">✕ مرفوض</option>
                        <option value="referral">📤 إحالة</option>
                        
                        @php
                            $wasReturned = \App\Models\ProjectActivityHistory::where('project_id', $project->id)
                                ->whereIn('action_type', ['rejected', 'need_action'])
                                ->exists();
                        @endphp
                        
                        @if($wasReturned)
                            <option value="resubmitted">⟳ إعادة تقديم</option>
                        @endif
                    </select>
                </div>
            </div>

            <!-- Sub-Department Selection (Conditional for specific entities) -->
            <!-- <div class="form-group" id="subDepartmentGroup" style="display: none; margin-top: 1.5rem;">
                <label for="sub_department_id" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                    <i class="fas fa-sitemap" style="color: #6f42c1;"></i> اختر الجهة الفرعية المختصة
                    <span style="color: #dc3545;">*</span>
                </label>
                <select id="sub_department_id" name="sub_department_id" class="form-control" style="height: 40px; padding: 0.5rem; border-radius: 6px;" onchange="handleSubDepartmentChange()">
                    <option value="">-- اختر الجهة الفرعية --</option>
                </select>
                <small style="color: #6c757d; margin-top: 0.25rem; display: block;">
                    <i class="fas fa-info-circle"></i> يرجى تحديد القسم أو الإدارة المختصة داخل هذا المركز.
                </small>
            </div> -->

            {{-- entity_id is filled by JS from the selected drop option on DOMContentLoaded / handleDropChange --}}
            <input type="hidden" id="entity_id" name="entity_id" value="">

            <!-- Notes Field (Conditional) -->
            <div class="form-group" id="notesGroup" style="display: none; margin-top: 1.5rem;">
                <label for="notes" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                    <i class="fas fa-comment-dots" style="color: #ffc107;"></i> الملاحظات أو سبب الرفض
                    <span style="color: #dc3545; display: none;" id="notesRequired">*</span>
                </label>
                <textarea id="notes" name="notes" class="form-control" rows="4" placeholder="أدخل ملاحظاتك أو سبب رفضك للمشروع..." style="padding: 0.75rem; border-radius: 6px; resize: vertical;"></textarea>
                <small style="color: #6c757d; margin-top: 0.5rem; display: block;" id="notesHint">الحقل اختياري</small>
            </div>

            <!-- Attachment Field (Conditional) -->
            <div class="form-group" id="attachmentGroup" style="display: none; margin-top: 1.5rem;">
                <label for="attachment" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                    <i class="fas fa-paperclip" style="color: #17a2b8;"></i> إرفاق ملف
                    <span style="color: #6c757d;">(اختياري)</span>
                </label>
                <div style="position: relative; border: 2px dashed #dee2e6; border-radius: 6px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.3s ease;" id="uploadArea" onclick="document.getElementById('attachment').click()">
                    <input type="file" id="attachment" name="attachment" style="display: none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ppt,.pptx,.xls,.xlsx" onchange="handleFileSelect(event)">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #6c757d; margin-bottom: 0.5rem; display: block;"></i>
                    <p style="margin: 0; color: #6c757d; font-weight: 500;">اسحب الملف هنا أو انقر للاختيار</p>
                    <small style="color: #6c757d; display: block; margin-top: 0.5rem;">الحجم الأقصى: 20 MB</small>
                    <span id="fileName" style="color: #28a745; font-weight: 600; margin-top: 0.75rem; display: none; font-size: 0.9rem;"></span>
                </div>
            </div>

            <!-- Referral Fields (Conditional - shown when status is 'referral') -->
            <div id="referralFieldsGroup" style="display: none; margin-top: 1.5rem;">
                <!-- Department Selection -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="referredDepartments" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-building" style="color: #007bff;"></i> اختر الجهة/الجهات للإحالة
                        <span style="color: #dc3545;">*</span>
                    </label>
                    <select id="referredDepartments" name="referred_entity_ids[]" class="form-control" multiple style="min-height: 120px; padding: 0.5rem; border-radius: 6px;">
                        <option value="">جاري تحميل الجهات...</option>
                    </select>
                    <small style="color: #6c757d; margin-top: 0.5rem; display: block;">
                        <i class="fas fa-info-circle"></i> يمكنك اختيار أكثر من جهة (استخدم Ctrl للاختيار المتعدد)
                    </small>
                </div>

                <!-- Referral Text -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="referralText" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-pen" style="color: #28a745;"></i> نص الإحالة
                        <span style="color: #dc3545;">*</span>
                    </label>
                    <textarea id="referralText" name="referral_text" class="form-control" rows="4" placeholder="اكتب تفاصيل الإحالة هنا..." style="padding: 0.75rem; border-radius: 6px; resize: vertical;"></textarea>
                    <small style="color: #6c757d; margin-top: 0.5rem; display: block;">الحد الأدنى 10 أحرف</small>
                </div>

                <!-- Referral Attachments -->
                <div class="form-group">
                    <label for="referralAttachments" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                        <i class="fas fa-paperclip" style="color: #17a2b8;"></i> مرفقات الإحالة
                        <span style="color: #6c757d;">(اختياري)</span>
                    </label>
                    <div style="position: relative; border: 2px dashed #dee2e6; border-radius: 6px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.3s ease;" id="referralUploadArea" onclick="document.getElementById('referralAttachments').click()">
                        <input type="file" id="referralAttachments" name="referral_attachments[]" multiple style="display: none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ppt,.pptx,.xls,.xlsx" onchange="handleReferralFileSelect(event)">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #6c757d; margin-bottom: 0.5rem; display: block;"></i>
                        <p style="margin: 0; color: #6c757d; font-weight: 500;">اسحب الملفات هنا أو انقر للاختيار</p>
                        <small style="color: #6c757d; display: block; margin-top: 0.5rem;">يمكنك إرفاق أكثر من ملف</small>
                        <div id="referralFilesList" style="margin-top: 1rem; text-align: right;"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                <!-- Submit Actions (Initially hidden, shown by JS based on selection) -->
                <button type="button" class="btn btn-success" id="submitBtn" onclick="submitApproval()" style="padding: 0.75rem 2.5rem; border-radius: 6px; font-weight: 700; display: none; align-items: center; gap: 0.75rem; box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2);">
                    <i class="fas fa-check"></i> موافق (OK)
                </button>
                
                <button type="button" class="btn btn-warning" id="returnBtn" onclick="submitApproval()" style="padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; display: none; align-items: center; gap: 0.5rem; color: #212529;">
                    <i class="fas fa-reply"></i> يحتاج إجراء (Requires Action)
                </button>
                
                <button type="button" class="btn btn-info" id="resubmitActionBtn" onclick="submitApproval()" style="padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; display: none; align-items: center; gap: 0.5rem; color: white;">
                    <i class="fas fa-undo"></i> إعادة تقديم (Resubmit)
                </button>
                
                <button type="button" class="btn" id="reviewActionBtn" onclick="submitApproval()" style="padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; display: none; align-items: center; gap: 0.5rem; background-color: #6f42c1; border-color: #6f42c1; color: white;">
                    <i class="fas fa-users-cog"></i> إرسال للمراجعة  
                </button>
                
                <button type="button" class="btn btn-primary" id="referralActionBtn" onclick="submitReferral()" style="padding: 0.75rem 2rem; border-radius: 6px; font-weight: 600; display: none; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-share"></i> إرسال الإحالة
                </button>

                <!-- Status Selectors (Vertical Divider if needed) -->
                <div id="statusSelectors" style="display: none; flex-wrap: wrap; gap: 0.75rem; border-right: 2px solid #eaedf2; padding-right: 1.5rem; margin-right: 0.5rem;">
                    <button type="button" class="btn btn-outline-success" id="selectApprovedBtn" title="OK" onclick="selectStatus('approved')">
                        <i class="fas fa-check"></i> موافق (OK)
                    </button>
                    <button type="button" class="btn btn-outline-warning" id="selectNeedActionBtn" title="Requires Action" onclick="selectStatus('need_action')">
                        <i class="fas fa-exclamation-triangle"></i> يحتاج إجراء (Requires Action)
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="selectRejectedBtn" title="Rejected" onclick="selectStatus('rejected')">
                        <i class="fas fa-times"></i> مرفوض (Rejected)
                    </button>
                    
                    {{-- Only show Resubmit button if project was previously returned --}}
                    @php
                        $wasReturned = \App\Models\ProjectActivityHistory::where('project_id', $project->id)
                            ->whereIn('action_type', ['rejected', 'need_action'])
                            ->exists();
                    @endphp
                    
                    <button type="button" class="btn btn-outline-info" id="selectResubmitBtn" title="Resubmit" onclick="selectStatus('resubmitted')" style="{{ $wasReturned ? '' : 'display: none;' }}">
                        <i class="fas fa-redo"></i> إعادة تقديم (Resubmit)
                    </button>
                    
                    <button type="button" class="btn btn-outline-primary" id="selectReviewBtn" style="color: #6f42c1; border-color: #6f42c1;" title="Review" onclick="selectStatus('financial_technical_review')">
                        <i class="fas fa-user-shield"></i> مراجعة مالية وفنية
                    </button>
                </div>

            </div>

            <!-- Loading Spinner (Hidden by default) -->
            <div id="loadingSpinner" style="display: none; text-align: center; margin-top: 1rem;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">جاري المعالجة...</span>
                </div>
                <p style="color: #6c757d; margin-top: 0.5rem;">جاري معالجة طلبك...</p>
            </div>

            <!-- Status Message (Hidden by default) -->
            <div id="statusMessage" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: 6px;"></div>

            <!-- Referral Response Modal (Overlay) -->
            <div id="referralResponseModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
                <div style="background: white; width: 90%; max-width: 600px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                    <div style="background: #003d7a; color: white; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; font-weight: 600;"><i class="fas fa-reply"></i> الرد على الإحالة</h4>
                        <button type="button" onclick="closeReferralModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
                    </div>
                    <div style="padding: 1.5rem;">
                        <form id="referralResponseForm">
                            <input type="hidden" id="modal_referral_id">
                            
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">رأيك / الرد <span style="color: #dc3545;">*</span></label>
                                <textarea id="modal_response_text" class="form-control" rows="5" style="border-radius: 6px;" placeholder="اكتب تفاصيل الرد هنا..."></textarea>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">الحالة <span style="color: #dc3545;">*</span></label>
                                <select id="modal_response_status" class="form-control" style="border-radius: 6px;">
                                    <option value="responded">تم الرد (Responded)</option>
                                    <option value="returned">إرجاع للمُحيل (Returned)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">مرفق (اختياري)</label>
                                <input type="file" id="modal_response_attachment" class="form-control" style="border-radius: 6px;">
                            </div>
                        </form>
                    </div>
                    <div style="padding: 1rem 1.5rem; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeReferralModal()" style="padding: 0.5rem 1.5rem; border-radius: 6px;">إلغاء</button>
                        <button type="button" class="btn btn-primary" onclick="submitReferralResponse()" style="padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 600;">إرسال الرد</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Global variables
let selectedStatus = '';
let selectedDrop = '';
let selectedStageId = '';
let selectedEntityId = '';
let hasRequiredAction = false;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load unified table
    loadUnifiedApprovalTable();

    // Setup form event listeners
    setupFormEventListeners();

    // Auto-initialise state from the pre-selected drop option
    // (the Blade template marks the current stage as `selected` + non-current as `disabled`)
    const dropSelect = document.getElementById('drop');
    if (dropSelect && dropSelect.value) {
        // Trigger the change handler so all globals and the hidden entity_id are set
        handleDropChange();
        // Re-apply status-based visibility in case a status was already chosen
        updateFormVisibility();
    }
});

// Handle drop selection change
function handleDropChange() {
    const dropSelect = document.getElementById('drop');
    selectedDrop = dropSelect.value;
    const selectedOption = dropSelect.options[dropSelect.selectedIndex];
    selectedStageId = selectedOption.getAttribute('data-stage-id');
    selectedEntityId = selectedOption.getAttribute('data-entity-id');
    
    // Update the hidden entity_id field with the selected entity
    const entityIdField = document.getElementById('entity_id');
    if (entityIdField && selectedEntityId) {
        entityIdField.value = selectedEntityId;
    }
    
    // Check if drop is selected to show/hide status selectors
    updateFormVisibility();
    
    // Check if we need to show sub-departments for this entity
    checkSubDepartmentRequirement();
}

// Handle sub-department change
function handleSubDepartmentChange() {
    const subDeptSelect = document.getElementById('sub_department_id');
    const selectedSubDeptId = subDeptSelect.value;
    
    if (selectedSubDeptId) {
        selectedEntityId = selectedSubDeptId;
        const entityIdField = document.getElementById('entity_id');
        if (entityIdField) {
            entityIdField.value = selectedSubDeptId;
        }
    } else {
        // Revert to stage entity if none selected
        const dropSelect = document.getElementById('drop');
        const selectedOption = dropSelect.options[dropSelect.selectedIndex];
        selectedEntityId = selectedOption ? selectedOption.getAttribute('data-entity-id') : '';
        const entityIdField = document.getElementById('entity_id');
        if (entityIdField) {
            entityIdField.value = selectedEntityId;
        }
    }
}

// Helper function to safely hide an element
function hideElement(el) {
    if (el) el.style.display = 'none';
}

// Helper function to safely show an element
function showElement(el, display = 'block') {
    if (el) el.style.display = display;
}

// Helper function to safely set style
function setElementStyle(el, property, value) {
    if (el) el.style[property] = value;
}

// Helper function to safely set text content
function setElementText(el, text) {
    if (el) el.textContent = text;
}

// Check if the selected stage requires sub-department selection
async function checkSubDepartmentRequirement() {
    // Check if the sub-department UI even exists (user may have removed it)
    const subDeptGroup = document.getElementById('subDepartmentGroup');
    if (!subDeptGroup) {
        // UI doesn't exist, skip all sub-department logic
        return;
    }
    
    const dropSelect = document.getElementById('drop');
    const selectedOption = dropSelect ? dropSelect.options[dropSelect.selectedIndex] : null;
    if (!selectedOption || !selectedOption.value) {
        hideElement(subDeptGroup);
        return;
    }
    
    const stageName = selectedOption.textContent;
    const stageEntityId = selectedOption.getAttribute('data-entity-id');
    
    // Target departments that require sub-selection (مركز نظم المعلومات, مركز تقنية المعلومات, etc.)
    const requiresSubDept = stageName.includes('مركز نظم المعلومات') || 
                          stageName.includes('مركز تقنية المعلومات') ||
                          stageName.includes('Information Systems Center') ||
                          stageName.includes('IT Center');
    
    if (requiresSubDept && stageEntityId) {
        showElement(subDeptGroup);
        await loadSubDepartments(stageEntityId);
    } else {
        hideElement(subDeptGroup);
        const subDeptSelect = document.getElementById('sub_department_id');
        if (subDeptSelect) subDeptSelect.innerHTML = '<option value="">-- اختر الجهة الفرعية --</option>';
    }
}

// Load sub-departments for the selected stage entity
async function loadSubDepartments(entityId) {
    const select = document.getElementById('sub_department_id');
    if (!select) return; // UI doesn't exist
    
    select.innerHTML = '<option value="">جاري تحميل الأقسام...</option>';
    
    try {
        const response = await fetch(`/api/entities/${entityId}/departments?only_children=true`);
        const data = await response.json();
        
        if (data.success && data.departments && data.departments.length > 0) {
            select.innerHTML = '<option value="">-- اختر الجهة الفرعية --</option>';
            data.departments.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.name; // Use short name for sub-dropdown
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">لا توجد أقسام فرعية متاحة</option>';
            // If no sub-departments, hide the group
            hideElement(document.getElementById('subDepartmentGroup'));
        }
    } catch (error) {
        console.error('Error loading sub-departments:', error);
        select.innerHTML = '<option value="">خطأ في تحميل الأقسام</option>';
    }
}

// Handle status selection change
function handleStatusChange() {
    const statusSelect = document.getElementById('status');
    selectedStatus = statusSelect.value;
    
    updateFormBasedOnStatus();
}

// Select status from button
function selectStatus(status) {
    document.getElementById('status').value = status;
    selectedStatus = status;
    updateFormBasedOnStatus();
}

// Update form based on selected status
function updateFormBasedOnStatus() {
    const notesGroup = document.getElementById('notesGroup');
    const attachmentGroup = document.getElementById('attachmentGroup');
    const notesRequired = document.getElementById('notesRequired');
    const notesHint = document.getElementById('notesHint');
    const notesField = document.getElementById('notes');
    
    // Reset all buttons
    hideAllActionButtons();
    
    // Handle different statuses
    switch(selectedStatus) {
        case 'approved':
            // لا يحتاج إلى ملاحظات أو مرفقات، إخفاء الحقول
            hideElement(notesGroup);
            hideElement(attachmentGroup);
            if (notesRequired) notesRequired.style.display = 'none';
            if (notesHint) notesHint.textContent = '';
            if (notesField) notesField.required = false;
            showElement(document.getElementById('submitBtn'), 'flex');
            // Show info message about automatic progression
            showMessage('عند الموافقة، سيتم الانتقال تلقائياً للمرحلة التالية دون الحاجة لإدخال ملاحظات أو مرفقات.', 'info');
            break;
            
        case 'financial_technical_review':
            // Simultaneous review - no notes or attachments required for transfer
            hideElement(notesGroup);
            hideElement(attachmentGroup);
            if (notesRequired) notesRequired.style.display = 'none';
            if (notesHint) notesHint.textContent = '';
            if (notesField) notesField.required = false;
            showElement(document.getElementById('reviewActionBtn'), 'flex');
            // Show info message about simultaneous review
            showMessage('سيتم إرسال المشروع للمراجعة المالية والفنية بشكل متزامن. المشروع سيبقى في نفس المرحلة حتى يكمل كلا المراجعين مهامهم.', 'info');
            break;
            
        case 'need_action':
        case 'rejected':
            // يحتاج إلى ملاحظات إجبارية
            showElement(notesGroup);
            showElement(attachmentGroup);
            if (notesRequired) notesRequired.style.display = 'inline';
            if (notesHint) notesHint.textContent = 'السبب إلزامي (10 أحرف على الأقل)';
            if (notesField) notesField.required = true;
            showElement(document.getElementById('returnBtn'), 'flex');
            break;
            
        case 'resubmitted':
            // إعادة تقديم - ملاحظات اختيارية
            showElement(notesGroup);
            showElement(attachmentGroup);
            if (notesRequired) notesRequired.style.display = 'none';
            if (notesHint) notesHint.textContent = 'يمكنك إضافة ملاحظات عن الإجراءات المتخذة';
            if (notesField) notesField.required = false;
            showElement(document.getElementById('resubmitActionBtn'), 'flex');
            break;
            
        case 'referral':
            // إحالة - إظهار حقول الإحالة
            hideElement(notesGroup);
            hideElement(attachmentGroup);
            showElement(document.getElementById('referralFieldsGroup'));
            showElement(document.getElementById('referralActionBtn'), 'flex');
            
            // Load departments for the SELECTED STAGE's entity (not the user's entity)
            const dropSelect = document.getElementById('drop');
            const selectedOption = dropSelect.options[dropSelect.selectedIndex];
            const stageEntityId = selectedOption ? selectedOption.getAttribute('data-entity-id') : null;
            const stageName = selectedOption ? selectedOption.textContent : '';
            
            // Check if this center should only refer to its own sub-departments
            const onlyChildren = stageName.includes('مركز نظم المعلومات') || 
                               stageName.includes('مركز تقنية المعلومات') ||
                               stageName.includes('Information Systems Center') ||
                               stageName.includes('IT Center');
            
            console.log('Referral selected. Stage:', stageName, 'Entity ID:', stageEntityId, 'Only children:', onlyChildren);
            
            if (stageEntityId) {
                loadDepartments(stageEntityId, onlyChildren);
            } else {
                showMessage('الرجاء اختيار المرحلة أولاً لتحميل الجهات المتاحة للإحالة', 'warning');
            }
            
            showMessage(onlyChildren ? 'يمكنك الإحالة فقط للأقسام التابعة لهذا المركز' : 'اختر الجهة/الجهات المراد الإحالة إليها واكتب نص الإحالة', 'info');
            break;
            
        default:
            hideElement(notesGroup);
            hideElement(attachmentGroup);
            hideAllActionButtons();
    }
}

// Update form visibility based on selections
function updateFormVisibility() {
    const dropSelected = selectedDrop !== '';
    const statusSelected = selectedStatus !== '';
    const statusSelectors = document.getElementById('statusSelectors');
    
    if (dropSelected) {
        statusSelectors.style.display = 'flex';
    } else {
        statusSelectors.style.display = 'none';
        hideElement(document.getElementById('notesGroup'));
        hideElement(document.getElementById('attachmentGroup'));
        hideAllActionButtons();
    }
    
    if (dropSelected && statusSelected) {
        updateFormBasedOnStatus();
    }
}

// Hide all action buttons
function hideAllActionButtons() {
    const actionButtons = ['submitBtn', 'returnBtn', 'resubmitActionBtn', 'reviewActionBtn', 'referralActionBtn'];
    actionButtons.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) btn.style.display = 'none';
    });
}

// Handle file selection
function handleFileSelect(event) {
    const fileInput = event.target;
    const fileName = document.getElementById('fileName');
    const uploadArea = document.getElementById('uploadArea');

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const fileSize = file.size / 1024 / 1024; // Convert to MB

        // Check file size (max 20MB)
        if (fileSize > 20) {
            showMessage('حجم الملف أكبر من 20 ميجابايت المسموح بها', 'danger');
            fileInput.value = '';
            if (fileName) {
                fileName.textContent = '';
                fileName.style.display = 'none';
            }
            if (uploadArea) uploadArea.style.borderColor = '#dee2e6';
            return;
        }

        if (fileName) {
            fileName.textContent = `✓ ${file.name} (${fileSize.toFixed(2)} MB)`;
            fileName.style.display = 'block';
        }
        if (uploadArea) uploadArea.style.borderColor = '#28a745';
    } else {
        if (fileName) {
            fileName.textContent = '';
            fileName.style.display = 'none';
        }
        if (uploadArea) uploadArea.style.borderColor = '#dee2e6';
    }
}

// Submit approval form
async function submitApproval() {
    if (!validateForm()) return;

    showLoading(true);

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('drop', selectedDrop);
    formData.append('entity_id', selectedEntityId);
    formData.append('status', selectedStatus);

    const notesField = document.getElementById('notes');
    if (notesField && notesField.value.trim()) {
        formData.append('notes', notesField.value.trim());
    }

    const attachmentField = document.getElementById('attachment');
    if (attachmentField && attachmentField.files.length > 0) {
        formData.append('attachment', attachmentField.files[0]);
    }

    try {
        const projectId = document.querySelector('[data-project-id]').getAttribute('data-project-id');
        const response = await fetch(`/api/projects/${projectId}/approve`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        if (data.success) {
            let successMessage = data.message;

            if (selectedStatus === 'approved') {
                // Check if this was the final approval (project moved to in_execution)
                if (data.project_status === 'in_execution') {
                    successMessage = '🎉 تمت الموافقة النهائية! انتقل المشروع إلى مرحلة التنفيذ.';
                } else {
                    successMessage += ' سيتم الانتقال تلقائياً للمرحلة التالية.';
                }
            } else if (selectedStatus === 'financial_technical_review') {
                successMessage += ' تم إرسال المشروع للمراجعين المالي والفني بشكل متزامن.';
            } else if (selectedStatus === 'need_action' || selectedStatus === 'rejected') {
                successMessage += ' تم إرجاع المشروع للمرحلة السابقة.';
            }

            showMessage(successMessage, 'success');
            resetForm();

            // Reload unified table
            setTimeout(() => { loadUnifiedApprovalTable(); }, 1000);

            // Post-approval navigation
            if (selectedStatus === 'approved') {
                if (data.project_status === 'in_execution') {
                    // Final approval: reload the page (approval form is no longer shown)
                    setTimeout(() => location.reload(), 2500);
                } else {
                    // Fetch fresh approval stages and update the dropdown
                    setTimeout(async () => {
                        try {
                            const stagesResp = await fetch(`/api/projects/${projectId}/approval-stages`);
                            const stagesData = await stagesResp.json();

                            if (stagesData.success && stagesData.approval_stages) {
                                updateStageDropdown(stagesData.approval_stages, stagesData.current_stage || data.next_drop);
                                showMessage('تم الانتقال تلقائياً للمرحلة التالية. يمكنك الآن مراجعة المشروع في هذه المرحلة.', 'info');
                            } else {
                                setTimeout(() => location.reload(), 1000);
                            }
                        } catch (err) {
                            console.error('Error updating stage dropdown:', err);
                            setTimeout(() => location.reload(), 1000);
                        }
                    }, 1500);
                }
            }
        } else {
            showMessage(data.message || 'حدث خطأ أثناء المعالجة', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('حدث خطأ في الاتصال بالخادم', 'danger');
    } finally {
        showLoading(false);
    }
}


// Validate form before submission
function validateForm() {
    // Check if drop is selected
    if (!selectedDrop) {
        showMessage('الرجاء اختيار المرحلة من القائمة المنسدلة أعلاه', 'warning');
        // Highlight the drop select field
        const dropSelect = document.getElementById('drop');
        if (dropSelect) {
            dropSelect.focus();
            dropSelect.style.borderColor = '#ffc107';
            setTimeout(() => {
                dropSelect.style.borderColor = '';
            }, 3000);
        }
        return false;
    }
    
    // Check if status is selected
    if (!selectedStatus) {
        showMessage('الرجاء اختيار حالة الموافقة', 'warning');
        // Highlight the status select field
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
            statusSelect.focus();
            statusSelect.style.borderColor = '#ffc107';
            setTimeout(() => {
                statusSelect.style.borderColor = '';
            }, 3000);
        }
        return false;
    }
    
    // Validate notes for need_action and rejected
    const notesField = document.getElementById('notes');
    if (['need_action', 'rejected'].includes(selectedStatus)) {
        if (!notesField.value.trim() || notesField.value.trim().length < 10) {
            showMessage('الرجاء إدخال سبب الرفض أو الإجراء المطلوب (10 أحرف على الأقل)', 'warning');
            notesField.focus();
            notesField.style.borderColor = '#ffc107';
            setTimeout(() => {
                notesField.style.borderColor = '';
            }, 3000);
            return false;
        }
    }
    
    // Validate sub-department if visible
    const subDeptGroup = document.getElementById('subDepartmentGroup');
    if (subDeptGroup && subDeptGroup.style.display !== 'none') {
        const subDeptSelect = document.getElementById('sub_department_id');
        if (subDeptSelect && !subDeptSelect.value) {
            showMessage('الرجاء اختيار القسم أو الإدارة الفرعية المختصة', 'warning');
            subDeptSelect.focus();
            subDeptSelect.style.borderColor = '#ffc107';
            setTimeout(() => {
                subDeptSelect.style.borderColor = '';
            }, 3000);
            return false;
        }
    }
    
    // Validate entity ID
    if (!selectedEntityId) {
        showMessage('لا توجد صلاحية لهذه المرحلة', 'danger');
        return false;
    }
    
    return true;
}

// Reset form after successful submission
function resetForm() {
    // Reset dropdowns
    const drop = document.getElementById('drop');
    if (drop) drop.selectedIndex = 0;
    
    const status = document.getElementById('status');
    if (status) status.selectedIndex = 0;
    
    // Reset fields
    const notes = document.getElementById('notes');
    if (notes) notes.value = '';
    
    const attachment = document.getElementById('attachment');
    if (attachment) attachment.value = '';
    
    const fileName = document.getElementById('fileName');
    if (fileName) {
        fileName.textContent = '';
        fileName.style.display = 'none';
    }
    
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) uploadArea.style.borderColor = '#dee2e6';
    
    // Reset sub-department selection
    const subDeptSelect = document.getElementById('sub_department_id');
    if (subDeptSelect) {
        subDeptSelect.selectedIndex = 0;
    }
    hideElement(document.getElementById('subDepartmentGroup'));
    
    // Reset referral fields
    const referralText = document.getElementById('referralText');
    if (referralText) referralText.value = '';
    
    const referralAttachments = document.getElementById('referralAttachments');
    if (referralAttachments) referralAttachments.value = '';
    
    const referralFilesList = document.getElementById('referralFilesList');
    if (referralFilesList) referralFilesList.innerHTML = '';
    
    const referredDepartments = document.getElementById('referredDepartments');
    if (referredDepartments) referredDepartments.selectedIndex = -1;
    
    // Reset variables
    selectedStatus = '';
    selectedDrop = '';
    selectedEntityId = '';
    
    // Hide form elements
    hideElement(document.getElementById('notesGroup'));
    hideElement(document.getElementById('attachmentGroup'));
    hideElement(document.getElementById('referralFieldsGroup'));
    hideAllActionButtons();
    hideElement(document.getElementById('statusSelectors'));
}

// Load unified approval table
async function loadUnifiedApprovalTable() {
    try {
        const projectId = document.querySelector('[data-project-id]').getAttribute('data-project-id');
        const response = await fetch(`/api/projects/${projectId}/movement-log`);
        const data = await response.json();
        
        if (data.success) {
            renderUnifiedTable(data.movement_log, data.approval_history, data.project_status);
        } else {
            document.getElementById('unifiedApprovalTable').innerHTML = `
                <div style="text-align: center; color: #dc3545; padding: 2rem;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    ${data.message || 'حدث خطأ في تحميل البيانات'}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading table:', error);
        document.getElementById('unifiedApprovalTable').innerHTML = `
            <div style="text-align: center; color: #dc3545; padding: 2rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                خطأ في الاتصال بالخادم
            </div>
        `;
    }
}

// Render unified table
function renderUnifiedTable(movementLog, approvalHistory, projectStatus) {
    let html = `
        <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <!-- Project Status Summary -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-bottom: 1px solid #dee2e6;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h4 style="margin: 0; color: #003d7a; font-weight: 600;">
                            حالة المشروع: <span style="color: ${getStatusColor(projectStatus.overall_status)}">${projectStatus.overall_status_arabic}</span>
                        </h4>
                        <p style="margin: 0.5rem 0 0 0; color: #6c757d; font-size: 0.9rem;">
                            المرحلة الحالية: ${projectStatus.current_stage_arabic}
                        </p>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: #007bff;">${projectStatus.progress_percentage}%</div>
                            <div style="font-size: 0.8rem; color: #6c757d;">إنجاز المراحل</div>
                        </div>
                        <div style="width: 150px; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                            <div style="width: ${projectStatus.progress_percentage}%; height: 100%; background: #007bff; transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for different views -->
            <div style="border-bottom: 1px solid #dee2e6;">
                <div style="display: flex; border-bottom: 2px solid #007bff;">
                    <button class="tab-button active" onclick="switchTab('movement')" style="flex: 1; padding: 1rem; background: none; border: none; font-weight: 600; color: #007bff; cursor: pointer; border-bottom: 2px solid transparent;">
                        <i class="fas fa-history"></i> سجل الحركة الكامل
                    </button>
                    <button class="tab-button" onclick="switchTab('approvals')" style="flex: 1; padding: 1rem; background: none; border: none; font-weight: 600; color: #6c757d; cursor: pointer;">
                        <i class="fas fa-check-circle"></i> الموافقات المنجزة
                    </button>
                    <button class="tab-button" onclick="switchTab('pending')" style="flex: 1; padding: 1rem; background: none; border: none; font-weight: 600; color: #6c757d; cursor: pointer;">
                        <i class="fas fa-clock"></i> الموافقات المعلقة
                    </button>
                    <button class="tab-button" onclick="switchTab('actions')" style="flex: 1; padding: 1rem; background: none; border: none; font-weight: 600; color: #6c757d; cursor: pointer;">
                        <i class="fas fa-exclamation-circle"></i> الإجراءات المطلوبة
                    </button>
                    <button class="tab-button" onclick="switchTab('referrals')" style="flex: 1; padding: 1rem; background: none; border: none; font-weight: 600; color: #6c757d; cursor: pointer;">
                        <i class="fas fa-share-square"></i> الإحالات
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div id="tabContent" style="padding: 0;">
                <!-- Movement Log Tab (Default) -->
                <div id="movementTab" class="tab-content" style="display: block;">
                    ${renderMovementLog(movementLog)}
                </div>
                
                <!-- Approvals Tab -->
                <div id="approvalsTab" class="tab-content" style="display: none;">
                    ${renderApprovalsList(approvalHistory)}
                </div>
                
                <!-- Pending Tab -->
                <div id="pendingTab" class="tab-content" style="display: none;">
                    <div style="padding: 1.5rem; min-height: 200px;">
                        <div id="pendingContent">
                            <div style="text-align: center; padding: 3rem; color: #6c757d;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                جاري تحميل الموافقات المعلقة...
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions Tab -->
                <div id="actionsTab" class="tab-content" style="display: none;">
                    <div style="padding: 1.5rem; min-height: 200px;">
                        <div id="actionsContent">
                            <div style="text-align: center; padding: 3rem; color: #6c757d;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                جاري تحميل الإجراءات المطلوبة...
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Referrals Tab -->
                <div id="referralsTab" class="tab-content" style="display: none;">
                    <div style="padding: 1.5rem; min-height: 200px;">
                        <div id="referralsContent">
                            <div style="text-align: center; padding: 3rem; color: #6c757d;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                جاري تحميل الإحالات...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('unifiedApprovalTable').innerHTML = html;
    
    // Load pending approvals and actions
    loadPendingApprovals();
    loadRequiredActions();
}

// Render movement log
function renderMovementLog(movementLog) {
    if (!movementLog || movementLog.length === 0) {
        return `
            <div style="text-align: center; padding: 3rem; color: #6c757d;">
                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                لا توجد سجلات حركة للمشروع
            </div>
        `;
    }
    
    let html = `
        <div style="max-height: 500px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">التاريخ والوقت</th>
                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">النوع</th>
                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">المرحلة</th>
                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">المستخدم</th>
                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">الحالة</th>
                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600; color: #495057;">الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    movementLog.forEach((log, index) => {
        const rowColor = index % 2 === 0 ? '#ffffff' : '#f8f9fa';
        const statusColor = getStatusColor(log.status);
        const iconClass = log.activity_type === 'approval_movement' ? 
            getApprovalIcon(log.status) : 
            (log.icon_class || 'fas fa-history');
        
        html += `
            <tr style="background: ${rowColor};">
                <td style="padding: 1rem; border-bottom: 1px solid #e9ecef; font-size: 0.85rem; color: #6c757d;">
                    ${formatDate(log.timestamp)}
                    ${log.elapsed_string ? `<br><small style="color: #adb5bd;">${log.elapsed_string}</small>` : ''}
                </td>
                <td style="padding: 1rem; border-bottom: 1px solid #e9ecef;">
                    <span style="display: flex; align-items: center; gap: 0.5rem; justify-content: flex-end;">
                        <span>${getActivityTypeArabic(log.activity_type)}</span>
                        <i class="${iconClass}" style="color: ${statusColor};"></i>
                    </span>
                </td>
                <td style="padding: 1rem; border-bottom: 1px solid #e9ecef;">
                    ${log.stage || '-'}
                </td>
                <td style="padding: 1rem; border-bottom: 1px solid #e9ecef;">
                    ${log.reviewer || '-'}
                    ${log.authority && log.authority !== '-' ? `<br><small style="color: #6c757d;">${log.authority}</small>` : ''}
                </td>
                <td style="padding: 1rem; border-bottom: 1px solid #e9ecef;">
                    <span style="color: ${statusColor}; font-weight: 600;">
                        ${log.status_arabic || log.status}
                    </span>
                </td>
                <td style="padding: 1rem; border-bottom: 1px solid #e9ecef; max-width: 300px;">
                    <div style="font-weight: 500; margin-bottom: 0.25rem;">
                        ${log.notes ? log.notes : '<span style="color: #adb5bd; font-style: italic;">لا توجد ملاحظات</span>'}
                    </div>
                    ${log.attachment ? `
                        <div style="margin-top: 0.5rem; display: inline-block;">
                            <a href="${log.attachment.url}" target="_blank" style="background: #e7f3ff; color: #007bff; padding: 0.25rem 0.75rem; border-radius: 20px; text-decoration: none; font-size: 0.8rem; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fas fa-paperclip"></i> 
                                ${log.attachment.name}
                            </a>
                        </div>
                    ` : ''}
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem; background: #f8f9fa; text-align: center; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 0.9rem;">
            إجمالي السجلات: ${movementLog.length}
        </div>
    `;
    
    return html;
}

// Load pending approvals
async function loadPendingApprovals() {
    try {
        const projectId = document.querySelector('[data-project-id]').getAttribute('data-project-id');
        const response = await fetch(`/api/projects/${projectId}/pending-approvals`);
        const data = await response.json();
        
        if (data.success) {
            renderPendingApprovals(data.pending_approvals);
        } else {
            document.getElementById('pendingContent').innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message}
                </div>
            `;
        }
    } catch (error) {
        document.getElementById('pendingContent').innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #dc3545;">
                <i class="fas fa-exclamation-triangle"></i> خطأ في تحميل البيانات
            </div>
        `;
    }
}

// Load required actions
async function loadRequiredActions() {
    try {
        const projectId = document.querySelector('[data-project-id]').getAttribute('data-project-id');
        const response = await fetch(`/api/projects/${projectId}/transactions-requiring-action`);
        const data = await response.json();
        
        if (data.success) {
            renderRequiredActions(data.transactions);
        } else {
            document.getElementById('actionsContent').innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message}
                </div>
            `;
        }
    } catch (error) {
        document.getElementById('actionsContent').innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #dc3545;">
                <i class="fas fa-exclamation-triangle"></i> خطأ في تحميل البيانات
            </div>
        `;
    }
}

// Load required actions functions and other helpers are defined above

function showMessage(message, type) {
    const messageDiv = document.getElementById('statusMessage');
    if (!messageDiv) return;
    
    // Define icon and color based on type
    let icon, bgColor, borderColor, textColor;
    
    switch(type) {
        case 'success':
            icon = 'check-circle';
            bgColor = '#d4edda';
            borderColor = '#c3e6cb';
            textColor = '#155724';
            break;
        case 'warning':
            icon = 'exclamation-triangle';
            bgColor = '#fff3cd';
            borderColor = '#ffeaa7';
            textColor = '#856404';
            break;
        case 'info':
            icon = 'info-circle';
            bgColor = '#d1ecf1';
            borderColor = '#bee5eb';
            textColor = '#0c5460';
            break;
        case 'danger':
        default:
            icon = 'times-circle';
            bgColor = '#f8d7da';
            borderColor = '#f5c6cb';
            textColor = '#721c24';
            break;
    }
    
    messageDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-${icon}" 
               style="color: ${textColor}; font-size: 1.2rem;"></i>
            <span>${message}</span>
        </div>
    `;
    messageDiv.style.display = 'block';
    messageDiv.style.backgroundColor = bgColor;
    messageDiv.style.border = `1px solid ${borderColor}`;
    messageDiv.style.color = textColor;
    
    // Auto-hide after 5 seconds (except for info messages which stay longer)
    const hideDelay = type === 'info' ? 8000 : 5000;
    setTimeout(() => {
        if (messageDiv) messageDiv.style.display = 'none';
    }, hideDelay);
}

function showLoading(show) {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.style.display = show ? 'block' : 'none';
}

// Update stage dropdown with fresh data
function updateStageDropdown(approvalStages, currentDrop) {
    console.log('updateStageDropdown called with:', {
        approvalStages,
        currentDrop,
        numStages: approvalStages ? approvalStages.length : 0
    });
    
    const dropSelect = document.getElementById('drop');
    if (!dropSelect) {
        console.error('drop select element not found!');
        return;
    }
    
    if (!approvalStages || approvalStages.length === 0) {
        console.error('No approval stages provided!');
        return;
    }
    
    // Clear existing options except the first placeholder
    dropSelect.innerHTML = '<option value="">-- اختر المرحلة --</option>';
    console.log('Cleared dropdown, adding', approvalStages.length, 'options');
    
    // Add new options
    let selectedFound = false;
    approvalStages.forEach((stage, index) => {
        const option = document.createElement('option');
        option.value = stage.drop;
        option.setAttribute('data-stage-id', stage.stage_id || '');
        option.setAttribute('data-entity-id', stage.entity_id);
        option.setAttribute('data-drop-order', stage.drop_order);
        
        const isCurrent = stage.is_current_stage || stage.drop === currentDrop;
        option.textContent = `المرحلة ${stage.drop_order}: ${stage.stage_name}${isCurrent ? ' (المرحلة الحالية)' : ''}`;
        
        if (isCurrent) {
            option.selected = true;
            selectedFound = true;
            console.log('Setting stage as current:', stage.stage_name, '(drop:', stage.drop, ')');
        }
        
        dropSelect.appendChild(option);
    });
    
    console.log('Added all options, selected found:', selectedFound);
    
    // Trigger change event to update selectedDrop and selectedEntityId
    if (currentDrop) {
        const selectedOption = dropSelect.querySelector(`option[value="${currentDrop}"]`);
        if (selectedOption) {
            dropSelect.value = currentDrop;
            console.log('Manually setting dropdown value to:', currentDrop);
            handleDropChange();
        } else {
            console.warn('Could not find option with value:', currentDrop);
        }
    }
    
    console.log('updateStageDropdown completed');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('ar-SA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusColor(status) {
    const colors = {
        'approved': '#28a745',
        'rejected': '#dc3545',
        'pending': '#ffc107',
        'need_action': '#fd7e14',
        'resubmitted': '#17a2b8',
        'financial_technical_review': '#6f42c1',
        'completed': '#28a745',
        'in_progress': '#007bff'
    };
    return colors[status] || '#6c757d';
}

function getApprovalIcon(status) {
    const icons = {
        'approved': 'fas fa-check-circle',
        'rejected': 'fas fa-times-circle',
        'pending': 'fas fa-clock',
        'need_action': 'fas fa-exclamation-circle',
        'resubmitted': 'fas fa-redo',
        'financial_technical_review': 'fas fa-users-cog'
    };
    return icons[status] || 'fas fa-history';
}

function getActivityTypeArabic(type) {
    const types = {
        'approval_movement': 'حركة موافقة',
        'activity_log': 'سجل نشاط',
        'financial_review': 'مراجعة مالية',
        'technical_review': 'مراجعة فنية',
        'review_completion': 'اكتمال مراجعة',
        'stage_progression': 'تقدم مرحلة',
        'stage_regression': 'تراجع مرحلة',
        'project_completion': 'اكتمال مشروع'
    };
    return types[type] || type;
}

function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.style.display = 'none');
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active');
        button.style.color = '#6c757d';
        button.style.borderBottom = '2px solid transparent';
    });
    
    // Show selected tab content
    document.getElementById(tabName + 'Tab').style.display = 'block';
    
    // Add active class to selected tab button
    const activeButton = document.querySelector(`.tab-button[onclick="switchTab('${tabName}')"]`);
    if (activeButton) {
        activeButton.classList.add('active');
        activeButton.style.color = '#007bff';
        activeButton.style.borderBottom = '2px solid #007bff';
    }
}

// Setup form event listeners
function setupFormEventListeners() {
    // Drag and drop for file upload
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#007bff';
            this.style.backgroundColor = '#f8f9fa';
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = 'transparent';
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = 'transparent';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.getElementById('attachment');
                fileInput.files = files;
                handleFileSelect({ target: fileInput });
            }
        });
    }
}

// ==================== REFERRAL FUNCTIONS ====================

// Load departments for referral
async function loadDepartments(entityId, onlyChildren = false) {
    console.log('Loading departments for entity:', entityId, 'onlyChildren:', onlyChildren);
    
    if (!entityId) {
        console.error('No entity ID provided for loading departments');
        showMessage('لم يتم تحديد الجهة الحالية', 'warning');
        return;
    }
    
    // Show loading state in dropdown
    const select = document.getElementById('referredDepartments');
    select.innerHTML = '<option value="">جاري تحميل الجهات...</option>';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
        
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }
        
        const projectId = document.querySelector('[data-project-id]')?.getAttribute('data-project-id');
        let url = `/api/entities/${entityId}/departments?`;
        if (onlyChildren) {
            url += 'only_children=true&';
        }
        if (projectId) {
            url += `project_id=${projectId}`;
        }
        
        const response = await fetch(url, {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin'
        });
        
        console.log('Departments API response status:', response.status);
        
        if (!response.ok) {
            console.error('API error:', response.status, response.statusText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Departments data:', data);
        
        if (data.success) {
            select.innerHTML = '';
            
            if (data.departments && data.departments.length > 0) {
                // عرض اسم الجهة فقط
                data.departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    
                    if (dept.has_pending_referral) {
                        option.textContent = `${dept.name} (قيد المعالجة)`;
                        option.disabled = true;
                        option.style.color = '#999';
                        option.title = 'توجد إحالة قيد المعالجة لهذه الجهة';
                    } else {
                        option.textContent = dept.name;
                    }
                    
                    select.appendChild(option);
                });
                
                showMessage(`تم تحميل ${data.departments.length} جهة تابعة لـ ${data.current_entity?.name || 'الجهة الحالية'}`, 'success');
            } else {
                select.innerHTML = '<option value="">لا توجد جهات متاحة للإحالة</option>';
                showMessage('لا توجد جهات متاحة للإحالة في نفس الإدارة', 'warning');
            }
        } else {
            console.error('API returned success=false:', data.message);
            select.innerHTML = '<option value="">خطأ في تحميل الجهات</option>';
            showMessage(data.message || 'حدث خطأ في تحميل قائمة الجهات', 'danger');
        }
    } catch (error) {
        console.error('Error loading departments:', error);
        select.innerHTML = '<option value="">خطأ في تحميل الجهات</option>';
        showMessage('حدث خطأ في الاتصال بالخادم: ' + error.message, 'danger');
    }
}


// Handle referral file selection
function handleReferralFileSelect(event) {
    const fileInput = event.target;
    const filesList = document.getElementById('referralFilesList');
    const uploadArea = document.getElementById('referralUploadArea');
    
    filesList.innerHTML = '';
    
    if (fileInput.files.length > 0) {
        Array.from(fileInput.files).forEach((file, index) => {
            const fileSize = file.size / 1024 / 1024; // Convert to MB
            
            // Check file size (max 20MB per file)
            if (fileSize > 20) {
                showMessage(`الملف "${file.name}" أكبر من 20 ميجابايت المسموح بها`, 'danger');
                return;
            }
            
            const fileItem = document.createElement('div');
            fileItem.style.cssText = 'background: #e7f3ff; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;';
            fileItem.innerHTML = `
                <span style="color: #007bff; font-size: 0.9rem;">
                    <i class="fas fa-file"></i> ${file.name} (${fileSize.toFixed(2)} MB)
                </span>
            `;
            filesList.appendChild(fileItem);
        });
        
        uploadArea.style.borderColor = '#28a745';
    } else {
        uploadArea.style.borderColor = '#dee2e6';
    }
}

// Submit referral
async function submitReferral() {
    // Validate referral form
    if (!validateReferralForm()) {
        return;
    }
    
    // Show loading spinner
    showLoading(true);
    
    try {
        const projectId = document.querySelector('[data-project-id]').getAttribute('data-project-id');
        const formData = new FormData();
        
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('drop', selectedDrop);
        formData.append('stage_id', selectedStageId || '');
        formData.append('entity_id', selectedEntityId);
        formData.append('referral_text', document.getElementById('referralText').value.trim());
        
        // Add selected departments
        const departmentSelect = document.getElementById('referredDepartments');
        const selectedDepartments = Array.from(departmentSelect.selectedOptions).map(opt => opt.value);
        selectedDepartments.forEach(deptId => {
            formData.append('referred_entity_ids[]', deptId);
        });
        
        // Add attachments if any
        const attachmentInput = document.getElementById('referralAttachments');
        if (attachmentInput.files.length > 0) {
            Array.from(attachmentInput.files).forEach(file => {
                formData.append('attachments[]', file);
            });
        }
        
        const response = await fetch(`/api/projects/${projectId}/referrals`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Server error response:', errorText);
            throw new Error(`Server returned ${response.status}: ${errorText.substring(0, 200)}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            showMessage(data.message, 'success');
            
            // Reset form
            resetForm();
            
            // Reload referrals tab
            setTimeout(() => {
                loadReferrals();
            }, 1000);
        } else {
            showMessage(data.message || 'حدث خطأ أثناء إرسال الإحالة', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('حدث خطأ في الاتصال بالخادم: ' + error.message, 'danger');
    } finally {
        showLoading(false);
    }
}

// Validate referral form
function validateReferralForm() {
    const departmentSelect = document.getElementById('referredDepartments');
    const selectedDepartments = Array.from(departmentSelect.selectedOptions);
    
    if (selectedDepartments.length === 0) {
        showMessage('الرجاء اختيار جهة واحدة على الأقل للإحالة', 'warning');
        departmentSelect.focus();
        departmentSelect.style.borderColor = '#ffc107';
        setTimeout(() => {
            departmentSelect.style.borderColor = '';
        }, 3000);
        return false;
    }
    
    const referralText = document.getElementById('referralText');
    if (referralText && (!referralText.value.trim() || referralText.value.trim().length < 10)) {
        showMessage('الرجاء إدخال نص الإحالة (10 أحرف على الأقل)', 'warning');
        referralText.focus();
        referralText.style.borderColor = '#ffc107';
        setTimeout(() => {
            if (referralText) referralText.style.borderColor = '';
        }, 3000);
        return false;
    }
    
    return true;
}

// Load referrals for the project
async function loadReferrals() {
    try {
        const projectId = document.querySelector('[data-project-id]').getAttribute('data-project-id');
        const drop = selectedDrop || '';
        
        const url = new URL(`/api/projects/${projectId}/referrals`, window.location.origin);
        if (drop) {
            url.searchParams.append('drop', drop);
        }
        
        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            renderReferrals(data.referrals);
        } else {
            document.getElementById('referralsContent').innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading referrals:', error);
        document.getElementById('referralsContent').innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #dc3545;">
                <i class="fas fa-exclamation-triangle"></i> خطأ في تحميل الإحالات
            </div>
        `;
    }
}

// Render referrals list
function renderReferrals(referrals) {
    const container = document.getElementById('referralsContent');
    
    if (!referrals || referrals.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem; color: #6c757d;">
                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                لا توجد إحالات لهذا المشروع
            </div>
        `;
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: 1.5rem;">';
    
    referrals.forEach(referral => {
        const statusColor = referral.status === 'pending' ? '#ffc107' : (referral.status === 'responded' ? '#28a745' : '#6c757d');
        const statusIcon = referral.status === 'pending' ? 'clock' : (referral.status === 'responded' ? 'check-circle' : 'reply');
        
        html += `
            <div style="border: 1px solid #dee2e6; border-radius: 8px; padding: 1.5rem; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h5 style="margin: 0; color: #003d7a; font-weight: 600;">
                            <i class="fas fa-share"></i> إحالة من ${referral.referring_entity}
                        </h5>
                        <small style="color: #6c757d; margin-top: 0.25rem; display: block;">
                            <i class="fas fa-user"></i> ${referral.referring_user} | 
                            <i class="fas fa-calendar"></i> ${formatDate(referral.created_at)}
                        </small>
                    </div>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: white; background: ${statusColor};">
                        <i class="fas fa-${statusIcon}"></i> ${referral.status_arabic}
                    </span>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <strong style="color: #495057;">الجهة المُحال إليها:</strong> ${referral.referred_entity}
                </div>
                
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    <strong style="color: #495057; display: block; margin-bottom: 0.5rem;">نص الإحالة:</strong>
                    <p style="margin: 0; color: #212529; white-space: pre-wrap;">${referral.referral_text}</p>
                </div>
                
                ${referral.referral_attachment ? `
                    <div style="margin-bottom: 1rem;">
                        <a href="${referral.referral_attachment.url}" target="_blank" style="background: #e7f3ff; color: #007bff; padding: 0.5rem 1rem; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-paperclip"></i> ${referral.referral_attachment.name}
                        </a>
                    </div>
                ` : ''}
                
                ${referral.response_text ? `
                    <div style="border-top: 2px dashed #dee2e6; padding-top: 1rem; margin-top: 1rem;">
                        <div style="margin-bottom: 0.5rem;">
                            <strong style="color: #28a745;"><i class="fas fa-reply"></i> الرد:</strong>
                            <small style="color: #6c757d; margin-right: 0.5rem;">
                                بواسطة ${referral.responding_user} | ${formatDate(referral.responded_at)}
                            </small>
                        </div>
                        <div style="background: #f1f8f4; padding: 1rem; border-radius: 6px;">
                            <p style="margin: 0; color: #212529; white-space: pre-wrap;">${referral.response_text}</p>
                        </div>
                        ${referral.response_attachment ? `
                            <div style="margin-top: 0.5rem;">
                                <a href="${referral.response_attachment.url}" target="_blank" style="background: #d4edda; color: #155724; padding: 0.5rem 1rem; border-radius: 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-paperclip"></i> ${referral.response_attachment.name}
                                </a>
                            </div>
                        ` : ''}
                    </div>
                ` : referral.status === 'pending' ? `
                    <div style="border-top: 2px dashed #dee2e6; padding-top: 1rem; margin-top: 1rem;">
                        <button onclick="showResponseForm(${referral.id})" class="btn btn-sm btn-success" style="padding: 0.5rem 1rem;">
                            <i class="fas fa-reply"></i> الرد على الإحالة
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Show response form modal
function showResponseForm(referralId) {
    document.getElementById('modal_referral_id').value = referralId;
    document.getElementById('modal_response_text').value = '';
    document.getElementById('modal_response_attachment').value = '';
    document.getElementById('referralResponseModal').style.display = 'flex';
}

// Close referral response modal
function closeReferralModal() {
    document.getElementById('referralResponseModal').style.display = 'none';
}

// Submit referral response
async function submitReferralResponse() {
    const referralId = document.getElementById('modal_referral_id').value;
    const responseText = document.getElementById('modal_response_text').value.trim();
    const status = document.getElementById('modal_response_status').value;
    const attachment = document.getElementById('modal_response_attachment');
    
    if (!responseText || responseText.length < 10) {
        alert('الرجاء إدخال نص الرد (10 أحرف على الأقل)');
        return;
    }
    
    showLoading(true);
    
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('response_text', responseText);
        formData.append('status', status);
        
        if (attachment.files.length > 0) {
            formData.append('attachment', attachment.files[0]);
        }
        
        const response = await fetch(`/api/referrals/${referralId}/respond`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(data.message, 'success');
            closeReferralModal();
            // Reload referrals list
            loadReferrals();
        } else {
            alert(data.message || 'حدث خطأ أثناء إرسال الرد');
        }
    } catch (error) {
        console.error('Error submitting response:', error);
        alert('حدث خطأ في الاتصال بالخادم');
    } finally {
        showLoading(false);
    }
}

// Update switchTab function to load referrals when tab is selected
const originalSwitchTab = switchTab;
if (typeof originalSwitchTab === 'function') {
    window.switchTab = function(tabName) {
        originalSwitchTab(tabName);
        
        // Load referrals when referrals tab is selected
        if (tabName === 'referrals') {
            loadReferrals();
        }
    };
}

</script>