<div>
    {{-- ===== رأس القسم ===== --}}
    <div class="att-header">
        <div class="att-header-right">
            <div class="att-header-icon">
                <i class="fas fa-paperclip"></i>
            </div>
            <div>
                <h5 class="att-title">المرفقات</h5>
                <p class="att-subtitle">{{ $task->attachments->count() }} ملف مرتبط بهذه المهمة</p>
            </div>
        </div>
        @can('task.attachment.upload', $task)
            <button class="att-upload-trigger" type="button" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-plus"></i>
                <span>رفع ملف</span>
            </button>
        @endcan
    </div>

    @can('task.attachment.upload', $task)
        {{-- ===== منطقة الرفع المدمجة ===== --}}
        <div class="att-upload-area" id="uploadArea">
            <form action="{{ route('projects.tasks.attachments.store', [$project->id, $task->id]) }}" method="POST"
                enctype="multipart/form-data" id="uploadForm" class="att-upload-form">
                @csrf
                <input type="file" name="attachment" id="fileInput" class="att-file-input">

                {{-- الحالة الافتراضية --}}
                <div class="att-upload-default" id="uploadDefault">
                    <div class="att-upload-icon">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </div>
                    <div class="att-upload-text">
                        <strong>اسحب الملف هنا</strong> أو <span class="att-link">تصفح من جهازك</span>
                    </div>
                    <div class="att-upload-hint">PDF, Word, Excel, صور — حتى 10MB</div>
                </div>

                {{-- حالة المعاينة --}}
                <div class="att-upload-selected d-none" id="uploadSelected">
                    <div class="att-selected-icon" id="selectedIcon">
                        <i class="fas fa-file"></i>
                    </div>
                    <div class="att-selected-info">
                        <div class="att-selected-name" id="selectedName"></div>
                        <div class="att-selected-size" id="selectedSize"></div>
                    </div>
                    <button type="button" class="att-selected-remove" id="removeFileBtn">
                        <i class="fas fa-xmark"></i>
                    </button>
                    <button type="submit" class="att-selected-upload" id="uploadBtn" disabled>
                        <i class="fas fa-arrow-up"></i>
                        <span>رفع</span>
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- ===== قائمة المرفقات ===== --}}
    <div class="att-list">
        @forelse($task->attachments as $attachment)
            @php
                $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                $iconClass = 'fa-file';
                $colorClass = 'neutral';

                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    $iconClass = 'fa-file-image';
                    $colorClass = 'image';
                } elseif ($ext === 'pdf') {
                    $iconClass = 'fa-file-pdf';
                    $colorClass = 'pdf';
                } elseif (in_array($ext, ['doc', 'docx'])) {
                    $iconClass = 'fa-file-word';
                    $colorClass = 'word';
                } elseif (in_array($ext, ['xls', 'xlsx'])) {
                    $iconClass = 'fa-file-excel';
                    $colorClass = 'excel';
                } elseif (in_array($ext, ['ppt', 'pptx'])) {
                    $iconClass = 'fa-file-powerpoint';
                    $colorClass = 'ppt';
                } elseif (in_array($ext, ['zip', 'rar', '7z'])) {
                    $iconClass = 'fa-file-zipper';
                    $colorClass = 'archive';
                }
            @endphp

            <div class="att-item">
                <div class="att-item-icon {{ $colorClass }}">
                    <i class="fas {{ $iconClass }}"></i>
                </div>

                <div class="att-item-info">
                    <a href="javascript:void(0)"
                        onclick="previewFile('{{ asset('storage/' . $attachment->file_path) }}', '{{ $ext }}', '{{ addslashes($attachment->file_name) }}')"
                        class="att-item-name" title="{{ $attachment->file_name }}">
                        {{ $attachment->file_name }}
                    </a>
                    <div class="att-item-meta">
                        <span>{{ strtoupper($ext) }}</span>
                        <span class="att-meta-dot">•</span>
                        <span>{{ $attachment->file_size >= 1048576
            ? round($attachment->file_size / 1048576, 2) . ' MB'
            : round($attachment->file_size / 1024, 1) . ' KB' }}</span>
                        <span class="att-meta-dot">•</span>
                        <span>{{ $attachment->uploadedBy->name ?? 'مستخدم' }}</span>
                        <span class="att-meta-dot">•</span>
                        <span>{{ $attachment->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="att-item-actions">
                    <button type="button" class="att-action-btn"
                        onclick="previewFile('{{ asset('storage/' . $attachment->file_path) }}', '{{ $ext }}', '{{ addslashes($attachment->file_name) }}')"
                        title="معاينة">
                        <i class="fas fa-eye"></i>
                    </button>
                    <a href="{{ asset('storage/' . $attachment->file_path) }}" class="att-action-btn" download
                        title="تحميل">
                        <i class="fas fa-download"></i>
                    </a>
                    @can('task.edit', $task)
                        <form
                            action="{{ route('projects.tasks.attachments.destroy', [$project->id, $task->id, $attachment->id]) }}"
                            method="POST" class="d-inline"
                            onsubmit="return confirmAction(this, 'هل أنت متأكد من حذف هذا المرفق؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="att-action-btn danger" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <div class="att-empty">
                <div class="att-empty-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h6>لا توجد مرفقات</h6>
                <p>لم يتم رفع أي ملفات لهذه المهمة حتى الآن</p>
            </div>
        @endforelse
    </div>

    {{-- File Preview Modal --}}
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content preview-modal">
                <div class="preview-modal-header">
                    <div class="preview-modal-title">
                        <i class="fas fa-file-alt"></i>
                        <span id="previewModalTitle">معاينة الملف</span>
                    </div>
                    <button type="button" class="preview-modal-close" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
                <div class="preview-modal-body">
                    <div id="previewContent"></div>
                </div>
                <div class="preview-modal-footer">
                    <a href="#" id="downloadPreviewBtn" class="preview-download-btn" target="_blank" download>
                        <i class="fas fa-download"></i>
                        <span>تحميل الملف</span>
                    </a>
                    <button type="button" class="preview-close-btn" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===============================
       المتغيرات
       =============================== */
    .att-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .att-header-right {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .att-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f0f4ff;
        color: #4f6ef7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .att-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1d23;
        margin: 0;
    }

    .att-subtitle {
        font-size: 0.78rem;
        color: #9099a8;
        margin: 0.15rem 0 0;
    }

    .att-upload-trigger {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1rem;
        background: #4f6ef7;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .att-upload-trigger:hover {
        background: #3d5ce5;
    }

    /* ===============================
       منطقة الرفع
       =============================== */
    .att-upload-area {
        background: #fff;
        border: 1.5px dashed #d5dbe5;
        border-radius: 12px;
        margin-bottom: 1.25rem;
        transition: all 0.25s;
    }

    .att-upload-area:hover,
    .att-upload-area.dragover {
        border-color: #4f6ef7;
        background: #f8faff;
    }

    .att-upload-form {
        margin: 0;
    }

    .att-file-input {
        position: absolute;
        width: 0;
        height: 0;
        opacity: 0;
        pointer-events: none;
    }

    /* الحالة الافتراضية */
    .att-upload-default {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        cursor: pointer;
        text-align: center;
    }

    .att-upload-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #f0f4ff;
        color: #4f6ef7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        margin-bottom: 0.75rem;
    }

    .att-upload-text {
        font-size: 0.88rem;
        color: #5f6672;
        margin-bottom: 0.3rem;
    }

    .att-upload-text strong {
        color: #1a1d23;
    }

    .att-link {
        color: #4f6ef7;
        font-weight: 600;
        cursor: pointer;
    }

    .att-link:hover {
        text-decoration: underline;
    }

    .att-upload-hint {
        font-size: 0.75rem;
        color: #9099a8;
    }

    /* حالة التحديد */
    .att-upload-selected {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1rem 1.25rem;
    }

    .att-selected-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #4f6ef7;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .att-selected-info {
        flex: 1;
        min-width: 0;
    }

    .att-selected-name {
        font-size: 0.88rem;
        font-weight: 600;
        color: #1a1d23;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .att-selected-size {
        font-size: 0.75rem;
        color: #9099a8;
        margin-top: 0.15rem;
    }

    .att-selected-remove {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e5e8ee;
        background: #fff;
        color: #9099a8;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .att-selected-remove:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    .att-selected-upload {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1.15rem;
        background: #4f6ef7;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .att-selected-upload:hover:not(:disabled) {
        background: #3d5ce5;
    }

    .att-selected-upload:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ===============================
       قائمة المرفقات
       =============================== */
    .att-list {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        overflow: hidden;
    }

    .att-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #f1f3f7;
        transition: background 0.15s;
    }

    .att-item:last-child {
        border-bottom: none;
    }

    .att-item:hover {
        background: #fafbfc;
    }

    /* أيقونة نوع الملف */
    .att-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .att-item-icon.pdf {
        background: #fef2f2;
        color: #dc2626;
    }

    .att-item-icon.word {
        background: #eff6ff;
        color: #2563eb;
    }

    .att-item-icon.excel {
        background: #f0fdf4;
        color: #16a34a;
    }

    .att-item-icon.ppt {
        background: #fff7ed;
        color: #ea580c;
    }

    .att-item-icon.image {
        background: #faf5ff;
        color: #a855f7;
    }

    .att-item-icon.archive {
        background: #f5f7fa;
        color: #5f6672;
    }

    .att-item-icon.neutral {
        background: #f5f7fa;
        color: #9099a8;
    }

    /* معلومات الملف */
    .att-item-info {
        flex: 1;
        min-width: 0;
    }

    .att-item-name {
        display: block;
        font-size: 0.88rem;
        font-weight: 600;
        color: #1a1d23;
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-bottom: 0.2rem;
        transition: color 0.15s;
    }

    .att-item-name:hover {
        color: #4f6ef7;
    }

    .att-item-meta {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.73rem;
        color: #9099a8;
        flex-wrap: wrap;
    }

    .att-meta-dot {
        color: #d5dbe5;
        font-size: 0.6rem;
    }

    /* أزرار الإجراءات */
    .att-item-actions {
        display: flex;
        gap: 0.3rem;
        flex-shrink: 0;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .att-item:hover .att-item-actions {
        opacity: 1;
    }

    .att-action-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: none;
        background: transparent;
        color: #5f6672;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
    }

    .att-action-btn:hover {
        background: #f0f4ff;
        color: #4f6ef7;
    }

    .att-action-btn.danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* ===============================
       حالة فارغة
       =============================== */
    .att-empty {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .att-empty-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 1rem;
        background: #f5f7fa;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #9099a8;
    }

    .att-empty h6 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1d23;
        margin: 0 0 0.3rem;
    }

    .att-empty p {
        font-size: 0.82rem;
        color: #9099a8;
        margin: 0;
    }

    /* ===============================
       Modal المعاينة
       =============================== */
    .preview-modal {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.3);
    }

    .preview-modal-header {
        padding: 1rem 1.25rem;
        background: #fff;
        border-bottom: 1px solid #eef0f4;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .preview-modal-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.92rem;
        font-weight: 600;
        color: #1a1d23;
    }

    .preview-modal-title i {
        color: #4f6ef7;
    }

    .preview-modal-close {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: #f5f7fa;
        color: #5f6672;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
    }

    .preview-modal-close:hover {
        background: #eef0f4;
        color: #1a1d23;
    }

    .preview-modal-body {
        background: #1a1d23;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .preview-modal-body #previewContent {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-modal-body img {
        max-width: 100%;
        max-height: 75vh;
        object-fit: contain;
        border-radius: 6px;
    }

    .preview-modal-body iframe {
        width: 100%;
        height: 75vh;
        border: none;
        border-radius: 6px;
        background: #fff;
    }

    .preview-modal-footer {
        padding: 0.85rem 1.25rem;
        background: #fff;
        border-top: 1px solid #eef0f4;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }

    .preview-download-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 1.1rem;
        background: #4f6ef7;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }

    .preview-download-btn:hover {
        background: #3d5ce5;
        color: #fff;
    }

    .preview-close-btn {
        padding: 0.5rem 1.1rem;
        background: #fff;
        color: #5f6672;
        border: 1px solid #e5e8ee;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .preview-close-btn:hover {
        background: #f5f7fa;
        border-color: #d5dbe5;
    }

    /* ===============================
       تجاوب
       =============================== */
    @media (max-width: 576px) {
        .att-item {
            padding: 0.75rem;
            gap: 0.65rem;
        }

        .att-item-actions {
            opacity: 1;
        }

        .att-item-icon {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }

        .att-item-name {
            font-size: 0.82rem;
        }

        .att-item-meta {
            font-size: 0.68rem;
        }
    }
</style>

<script>
    // ===== منطق رفع الملفات =====
    document.addEventListener('DOMContentLoaded', function () {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadDefault = document.getElementById('uploadDefault');
        const uploadSelected = document.getElementById('uploadSelected');
        const selectedIcon = document.getElementById('selectedIcon');
        const selectedName = document.getElementById('selectedName');
        const selectedSize = document.getElementById('selectedSize');
        const removeBtn = document.getElementById('removeFileBtn');
        const uploadBtn = document.getElementById('uploadBtn');

        if (!uploadArea || !fileInput) return;

        // النقر على المنطقة الافتراضية يفتح المتصفح
        uploadDefault.addEventListener('click', () => fileInput.click());

        // Drag & Drop
        ['dragenter', 'dragover'].forEach(evt => {
            uploadArea.addEventListener(evt, (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            uploadArea.addEventListener(evt, (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });
        });
        uploadArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showPreview(files[0]);
            }
        });

        // عند اختيار ملف
        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                showPreview(this.files[0]);
            }
        });

        // إزالة الملف
        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                fileInput.value = '';
                uploadDefault.classList.remove('d-none');
                uploadSelected.classList.add('d-none');
                uploadBtn.disabled = true;
            });
        }

        function showPreview(file) {
            const maxSize = 10 * 1024 * 1024;
            if (file.size > maxSize) {
                alert(`الملف "${file.name}" يتجاوز الحد الأقصى (10 ميجابايت)`);
                fileInput.value = '';
                return;
            }

            const ext = file.name.split('.').pop().toLowerCase();
            let iconClass = 'fa-file';
            if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) iconClass = 'fa-file-image';
            else if (ext === 'pdf') iconClass = 'fa-file-pdf';
            else if (['doc', 'docx'].includes(ext)) iconClass = 'fa-file-word';
            else if (['xls', 'xlsx'].includes(ext)) iconClass = 'fa-file-excel';
            else if (['ppt', 'pptx'].includes(ext)) iconClass = 'fa-file-powerpoint';
            else if (['zip', 'rar', '7z'].includes(ext)) iconClass = 'fa-file-zipper';

            selectedIcon.innerHTML = `<i class="fas ${iconClass}"></i>`;
            selectedName.textContent = file.name;
            selectedSize.textContent = file.size >= 1048576
                ? (file.size / 1048576).toFixed(2) + ' MB'
                : (file.size / 1024).toFixed(1) + ' KB';

            uploadDefault.classList.add('d-none');
            uploadSelected.classList.remove('d-none');
            uploadBtn.disabled = false;
        }
    });

    // ===== معاينة الملف =====
    function previewFile(url, type, name) {
        document.getElementById('previewModalTitle').innerText = name;
        const contentDiv = document.getElementById('previewContent');
        const downloadBtn = document.getElementById('downloadPreviewBtn');
        downloadBtn.href = url;

        contentDiv.innerHTML = '<div class="spinner-border text-light" role="status"></div>';

        const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

        if (imageTypes.includes(type.toLowerCase())) {
            contentDiv.innerHTML = `<img src="${url}" alt="${name}">`;
            showPreviewModal();
        } else if (type.toLowerCase() === 'pdf') {
            contentDiv.innerHTML = `<iframe src="${url}"></iframe>`;
            showPreviewModal();
        } else {
            window.open(url, '_blank');
        }
    }

    function showPreviewModal() {
        const modalEl = document.getElementById('filePreviewModal');
        if (modalEl.parentNode !== document.body) {
            document.body.appendChild(modalEl);
        }
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>