<div class="notes-container">

    {{-- ===== 1. عمود ملاحظات التنفيذ ===== --}}
    <div class="notes-column exec-column">
        <div class="notes-section-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="section-icon-box green">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 1.02rem;">ملاحظات التنفيذ</h5>
                        <p class="text-muted mb-0 small">تقارير الإنجاز ونسبة التقدم</p>
                    </div>
                </div>
                @can('task.execution-note.create', $task)
                    <button class="btn-add-note exec" type="button" data-bs-toggle="collapse"
                        data-bs-target="#createExecutionNoteCollapse">
                        <i class="fas fa-plus-circle"></i>
                        <span>تحديث الإنجاز</span>
                    </button>
                @endcan
            </div>
        </div>

        {{-- ===== نموذج إضافة ملاحظة تنفيذ ===== --}}
        @can('task.execution-note.create', $task)
            <div class="collapse" id="createExecutionNoteCollapse">
                <div class="note-form-card exec">
                    <div class="note-form-header exec">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-chart-line"></i>
                            <span class="fw-bold">إضافة تقرير إنجاز جديد</span>
                        </div>
                        <button type="button" class="btn-close-form exec" data-bs-toggle="collapse"
                            data-bs-target="#createExecutionNoteCollapse">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('projects.tasks.execution-notes.store', [$project->id, $task->id]) }}"
                        method="POST">
                        @csrf
                        <div class="note-form-body">
                            {{-- تقرير الإنجاز --}}
                            <div class="form-field">
                                <label class="form-field-label">
                                    <span class="field-icon green"><i class="fas fa-align-right"></i></span>
                                    تقرير الإنجاز / تفاصيل الملاحظة
                                    
                                </label>
                                <textarea name="note" class="form-field-input" rows="3"
                                    placeholder="تفاصيل ما تم إنجازه في هذه الخطوة..."></textarea>
                            </div>

                            {{-- نسبة التقدم --}}
                            <div class="form-field">
                                <label class="form-field-label">
                                    <span class="field-icon green"><i class="fas fa-percent"></i></span>
                                    نسبة تقدم متابعة المهمة
                                    <span class="optional-mark">(%)</span>
                                </label>

                                @if($task->executiveAction)
                                    <div class="executive-note-hint">
                                        <i class="fas fa-info-circle"></i>
                                        <span>الإنجاز الرسمي يؤخذ من سجلات التنفيذ المرتبطة بالإجراء التنفيذي.</span>
                                    </div>
                                @endif

                                <div class="range-slider-wrapper">
                                    <input type="range" class="range-slider exec-range" name="progress_percentage" min="0"
                                        max="100" step="5" id="exec-percentage-range" value="50"
                                        oninput="updateExecRange(this)">
                                    <div class="range-labels">
                                        <span>0%</span>
                                        <span>25%</span>
                                        <span>50%</span>
                                        <span>75%</span>
                                        <span>100%</span>
                                    </div>
                                    <div class="range-value-badge exec" id="exec-percentage-label">50%</div>
                                </div>
                            </div>
                        </div>
                        <div class="note-form-footer">
                            <button type="button" class="btn-form-cancel" data-bs-toggle="collapse"
                                data-bs-target="#createExecutionNoteCollapse">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                            <button type="submit" class="btn-form-submit exec">
                                <i class="fas fa-check-circle"></i> حفظ الملاحظة والتقدم
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        {{-- ===== قائمة ملاحظات التنفيذ ===== --}}
        <div class="notes-list">
            @forelse($task->executionNotes as $execNote)
                <div class="exec-note-card">
                    <div class="exec-note-header">
                        <div class="note-user-info">
                            <div class="note-user-avatar green">
                                {{ mb_strtoupper(mb_substr($execNote->creator?->name ?? 'م', 0, 1, 'UTF-8')) }}
                            </div>
                            <div class="note-user-meta">
                                <span class="note-user-name">{{ $execNote->creator?->name ?? 'مستخدم' }}</span>
                                <span class="note-date">
                                    <i class="far fa-clock"></i>
                                    {{ $execNote->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        @if($execNote->progress_percentage !== null)
                            <div class="exec-progress-badge" data-progress="{{ $execNote->progress_percentage }}">
                                <span class="exec-progress-value">{{ $execNote->progress_percentage }}%</span>
                            </div>
                        @endif
                    </div>

                    <div class="exec-note-body">
                        <p class="exec-note-text">{!! nl2br(e($execNote->note)) !!}</p>
                    </div>

                    @if($execNote->progress_percentage !== null)
                        <div class="exec-note-progress">
                            <div class="exec-progress-bar">
                                <div class="exec-progress-fill" style="width: {{ $execNote->progress_percentage }}%;"></div>
                            </div>
                            <div class="exec-progress-footer">
                                <span class="exec-progress-label">
                                    <i class="fas fa-chart-line"></i>
                                    نسبة الإنجاز
                                </span>
                                <span class="exec-progress-percent">{{ $execNote->progress_percentage }}%</span>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-notes">
                    <div class="empty-notes-icon green">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h6>لا توجد ملاحظات تنفيذ</h6>
                    <p>لم يتم تسجيل أي تقارير إنجاز لهذه المهمة حتى الآن.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===== 2. عمود ملاحظات المستندات ===== --}}
    <div class="notes-column doc-column">
        <div class="notes-section-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="section-icon-box cyan">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 1.02rem;">ملاحظات المستندات</h5>
                        <p class="text-muted mb-0 small">الملاحظات المرتبطة بالوثائق والملفات</p>
                    </div>
                </div>
                @can('task.document-note.create', $task)
                    <button class="btn-add-note doc" type="button" data-bs-toggle="collapse"
                        data-bs-target="#createDocNoteCollapse">
                        <i class="fas fa-plus-circle"></i>
                        <span>إضافة ملاحظة</span>
                    </button>
                @endcan
            </div>
        </div>

        {{-- ===== نموذج إضافة ملاحظة مستند ===== --}}
        @can('task.document-note.create', $task)
            <div class="collapse" id="createDocNoteCollapse">
                <div class="note-form-card doc">
                    <div class="note-form-header doc">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-note-sticky"></i>
                            <span class="fw-bold">إضافة ملاحظة مستند جديدة</span>
                        </div>
                        <button type="button" class="btn-close-form doc" data-bs-toggle="collapse"
                            data-bs-target="#createDocNoteCollapse">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('projects.tasks.document-notes.store', [$project->id, $task->id]) }}"
                        method="POST">
                        @csrf
                        <div class="note-form-body">
                            <div class="form-field">
                                <label class="form-field-label">
                                    <span class="field-icon cyan"><i class="fas fa-align-right"></i></span>
                                    ملاحظة المستند
                                    
                                </label>
                                <textarea name="note" class="form-field-input" rows="3"
                                    placeholder="ملاحظة حول مستند أو ملف للمهمة..."></textarea>
                            </div>
                        </div>
                        <div class="note-form-footer">
                            <button type="button" class="btn-form-cancel" data-bs-toggle="collapse"
                                data-bs-target="#createDocNoteCollapse">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                            <button type="submit" class="btn-form-submit doc">
                                <i class="fas fa-check-circle"></i> حفظ الملاحظة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        {{-- ===== قائمة ملاحظات المستندات ===== --}}
        <div class="notes-list">
            @forelse($task->documentNotes as $docNote)
                <div class="doc-note-card">
                    <div class="doc-note-header">
                        <div class="note-user-info">
                            <div class="note-user-avatar cyan">
                                {{ mb_strtoupper(mb_substr($docNote->creator?->name ?? 'م', 0, 1, 'UTF-8')) }}
                            </div>
                            <div class="note-user-meta">
                                <span class="note-user-name">{{ $docNote->creator?->name ?? 'مستخدم' }}</span>
                                <span class="note-date">
                                    <i class="far fa-clock"></i>
                                    {{ $docNote->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <span class="doc-note-type-badge">
                            <i class="fas fa-file-alt"></i>
                            مستند
                        </span>
                    </div>
                    <div class="doc-note-body">
                        <p class="doc-note-text">{!! nl2br(e($docNote->note)) !!}</p>
                    </div>
                </div>
            @empty
                <div class="empty-notes">
                    <div class="empty-notes-icon cyan">
                        <i class="fas fa-file-circle-exclamation"></i>
                    </div>
                    <h6>لا توجد ملاحظات مستندات</h6>
                    <p>لم يتم تسجيل أي ملاحظات متعلقة بالمستندات حتى الآن.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===== الأنماط ===== --}}
<style>
    /* ===============================
       تخطيط الصفحة
       =============================== */
    .notes-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 991.98px) {
        .notes-container {
            grid-template-columns: 1fr;
        }
    }

    .notes-column {
        display: flex;
        flex-direction: column;
    }

    /* ===============================
       رأس القسم
       =============================== */
    .notes-section-header {
        background: #fff;
        border-radius: 14px;
        padding: 1.15rem 1.35rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.15rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .section-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .section-icon-box.green {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        color: #16a34a;
    }

    .section-icon-box.cyan {
        background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
        color: #0891b2;
    }

    /* ===============================
       زر الإضافة
       =============================== */
    .btn-add-note {
        border: none;
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.25s ease;
        color: #fff;
    }

    .btn-add-note.exec {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
    }

    .btn-add-note.exec:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(34, 197, 94, 0.3);
    }

    .btn-add-note.doc {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
    }

    .btn-add-note.doc:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(6, 182, 212, 0.3);
    }

    /* ===============================
       نموذج الإضافة
       =============================== */
    .note-form-card {
        background: #fff;
        border-radius: 14px;
        margin-bottom: 1.25rem;
        overflow: hidden;
        border: 1.5px solid;
        animation: slideDown 0.3s ease;
    }

    .note-form-card.exec {
        border-color: #86efac;
        box-shadow: 0 4px 16px rgba(34, 197, 94, 0.06);
    }

    .note-form-card.doc {
        border-color: #67e8f9;
        box-shadow: 0 4px 16px rgba(6, 182, 212, 0.06);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .note-form-header {
        padding: 0.85rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid;
    }

    .note-form-header.exec {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-color: #bbf7d0;
        color: #166534;
    }

    .note-form-header.doc {
        background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
        border-color: #a5f3fc;
        color: #155e75;
    }

    .btn-close-form {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.75rem;
    }

    .btn-close-form.exec {
        background: rgba(22, 163, 74, 0.1);
        color: #16a34a;
    }

    .btn-close-form.doc {
        background: rgba(8, 145, 178, 0.1);
        color: #0891b2;
    }

    .btn-close-form.exec:hover {
        background: #16a34a;
        color: #fff;
    }

    .btn-close-form.doc:hover {
        background: #0891b2;
        color: #fff;
    }

    .note-form-body {
        padding: 1.25rem;
    }

    .note-form-footer {
        padding: 0.9rem 1.25rem;
        background: #fafbfc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 0.6rem;
    }

    /* ===============================
       حقول النموذج
       =============================== */
    .form-field {
        margin-bottom: 1rem;
    }

    .form-field:last-child {
        margin-bottom: 0;
    }

    .form-field-label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.5rem;
    }

    .field-icon {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        flex-shrink: 0;
    }

    .field-icon.green {
        background: #f0fdf4;
        color: #16a34a;
    }

    .field-icon.cyan {
        background: #ecfeff;
        color: #0891b2;
    }

    .required-mark {
        color: #ef4444;
        font-size: 0.75rem;
    }

    .optional-mark {
        color: #94a3b8;
        font-size: 0.72rem;
        font-weight: 500;
    }

    .form-field-input {
        width: 100%;
        padding: 0.6rem 0.9rem;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        font-size: 0.88rem;
        color: #334155;
        transition: all 0.25s ease;
        resize: vertical;
    }

    .form-field-input:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
        outline: none;
    }

    .form-field-input:hover {
        border-color: #cbd5e1;
    }

    /* ===============================
       تلميح الإجراء التنفيذي
       =============================== */
    .executive-note-hint {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.6rem 0.85rem;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 1px solid #fde68a;
        border-radius: 8px;
        font-size: 0.78rem;
        color: #92400e;
        margin-bottom: 0.85rem;
    }

    .executive-note-hint i {
        color: #f59e0b;
        margin-top: 2px;
    }

    /* ===============================
       Range Slider محسّن
       =============================== */
    .range-slider-wrapper {
        position: relative;
        padding: 0.5rem 0 2rem;
    }

    .range-slider {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 10px;
        background: linear-gradient(90deg, #dcfce7 0%, #bbf7d0 100%);
        outline: none;
        cursor: pointer;
    }

    .range-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);
        transition: transform 0.2s ease;
    }

    .range-slider::-webkit-slider-thumb:hover {
        transform: scale(1.15);
    }

    .range-slider::-moz-range-thumb {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);
    }

    .range-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 0.5rem;
        padding: 0 2px;
    }

    .range-labels span {
        font-size: 0.68rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .range-value-badge {
        position: absolute;
        top: -8px;
        padding: 0.25rem 0.65rem;
        border-radius: 7px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
        transition: left 0.15s ease;
        pointer-events: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .range-value-badge.exec {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }

    .range-value-badge::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #16a34a;
    }

    /* ===============================
       أزرار النموذج
       =============================== */
    .btn-form-cancel {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        padding: 0.55rem 1.15rem;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-form-cancel:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .btn-form-submit {
        border: none;
        color: #fff;
        padding: 0.55rem 1.35rem;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .btn-form-submit.exec {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
    }

    .btn-form-submit.exec:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(34, 197, 94, 0.3);
    }

    .btn-form-submit.doc {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
    }

    .btn-form-submit.doc:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(6, 182, 212, 0.3);
    }

    /* ===============================
       قائمة الملاحظات
       =============================== */
    .notes-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    /* ===============================
       بطاقة ملاحظة التنفيذ
       =============================== */
    .exec-note-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        animation: fadeInUp 0.4s ease backwards;
    }

    .exec-note-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        border-color: #86efac;
    }

    .exec-note-header {
        padding: 0.9rem 1.15rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .note-user-info {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .note-user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .note-user-avatar.green {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }

    .note-user-avatar.cyan {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }

    .note-user-meta {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .note-user-name {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e293b;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .note-date {
        font-size: 0.7rem;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .note-date i {
        font-size: 0.6rem;
    }

    /* شارة نسبة التقدم */
    .exec-progress-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .exec-progress-badge[data-progress] {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25);
    }

    .exec-progress-badge[data-progress="100"] {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }

    .exec-note-body {
        padding: 1rem 1.15rem;
    }

    .exec-note-text {
        font-size: 0.85rem;
        color: #334155;
        line-height: 1.7;
        margin: 0;
    }

    /* شريط التقدم */
    .exec-note-progress {
        padding: 0.85rem 1.15rem;
        background: #fafbfc;
        border-top: 1px dashed #e2e8f0;
    }

    .exec-progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .exec-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
        border-radius: 10px;
        transition: width 0.6s ease;
        position: relative;
    }

    .exec-progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .exec-progress-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .exec-progress-label {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .exec-progress-label i {
        color: #22c55e;
        font-size: 0.65rem;
    }

    .exec-progress-percent {
        font-size: 0.82rem;
        font-weight: 700;
        color: #16a34a;
    }

    /* ===============================
       بطاقة ملاحظة المستند
       =============================== */
    .doc-note-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        position: relative;
        animation: fadeInUp 0.4s ease backwards;
    }

    .doc-note-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #06b6d4 0%, #0891b2 100%);
        border-radius: 0 14px 14px 0;
    }

    .doc-note-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        border-color: #67e8f9;
    }

    .doc-note-header {
        padding: 0.9rem 1.15rem;
        padding-right: calc(1.15rem + 4px);
        background: linear-gradient(135deg, #ecfeff 0%, #f8fafc 100%);
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .doc-note-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.65rem;
        background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
        color: #155e75;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .doc-note-type-badge i {
        font-size: 0.65rem;
    }

    .doc-note-body {
        padding: 1rem 1.15rem;
        padding-right: calc(1.15rem + 4px);
    }

    .doc-note-text {
        font-size: 0.85rem;
        color: #334155;
        line-height: 1.7;
        margin: 0;
    }

    /* ===============================
       حالة عدم وجود ملاحظات
       =============================== */
    .empty-notes {
        text-align: center;
        padding: 3rem 1.5rem;
        background: #fff;
        border-radius: 14px;
        border: 2px dashed #e2e8f0;
    }

    .empty-notes-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .empty-notes-icon.green {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        color: #16a34a;
    }

    .empty-notes-icon.cyan {
        background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
        color: #0891b2;
    }

    .empty-notes h6 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.3rem;
    }

    .empty-notes p {
        font-size: 0.82rem;
        color: #94a3b8;
        margin: 0;
    }

    /* ===============================
       Animations
       =============================== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .exec-note-card:nth-child(1) {
        animation-delay: 0.05s;
    }

    .exec-note-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .exec-note-card:nth-child(3) {
        animation-delay: 0.15s;
    }

    .exec-note-card:nth-child(4) {
        animation-delay: 0.2s;
    }

    .doc-note-card:nth-child(1) {
        animation-delay: 0.05s;
    }

    .doc-note-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .doc-note-card:nth-child(3) {
        animation-delay: 0.15s;
    }

    .doc-note-card:nth-child(4) {
        animation-delay: 0.2s;
    }
</style>

<script>
    // تحديث موقع ونص شارة الـ range slider
    function updateExecRange(input) {
        const value = input.value;
        const label = document.getElementById('exec-percentage-label');
        label.innerText = value + '%';

        // حساب الموقع
        const min = parseFloat(input.min);
        const max = parseFloat(input.max);
        const percent = (value - min) / (max - min);
        const thumbWidth = 22;
        const offset = percent * (input.offsetWidth - thumbWidth) + (thumbWidth / 2);
        label.style.left = `calc(${percent * 100}% - ${percent * 10}px + ${thumbWidth / 2}px)`;
        label.style.transform = 'translateX(-50%)';
    }

    // تهيئة الموقع عند التحميل
    document.addEventListener('DOMContentLoaded', function () {
        const range = document.getElementById('exec-percentage-range');
        if (range) {
            updateExecRange(range);
        }
    });
</script>