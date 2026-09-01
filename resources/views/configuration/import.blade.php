@extends('layouts.app')

@section('title', 'استيراد ' . $title)

@section('content')
@include('configuration.shared_styles')

<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="page-header-box mb-4">
            <h2 class="page-main-title">
                <i class="fas fa-file-import me-2 text-primary"></i> استيراد {{ $title }}
            </h2>
            <p class="text-muted small">قم برفع ملف Excel أو CSV لتحديث بيانات {{ $title }} في النظام.</p>
            <div class="section-divider mt-2"></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="main-card h-100 p-4 border-0 shadow-sm rounded-4">
                    <h5 class="fw-bold mb-4" style="color: #001f3f;">
                        <i class="fas fa-info-circle me-2 text-primary"></i> تعليمات الاستيراد
                    </h5>
                    <div class="d-flex mb-4">
                        <span class="badge bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">1</span>
                        <div>
                            <span class="fw-bold d-block mb-1">حمّل النموذج</span>
                            <p class="text-muted small mb-2">استخدم النموذج القياسي لضمان مطابقة الأعمدة.</p>
                            <div class="d-flex gap-2">
                                <a href="{{ route('config.download-template', ['entity' => $entity, 'format' => 'xlsx']) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </a>
                                <a href="{{ route('config.download-template', ['entity' => $entity, 'format' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-csv me-1"></i> CSV
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <span class="badge bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">2</span>
                        <div>
                            <span class="fw-bold d-block mb-1">املأ البيانات وارفعه</span>
                            <p class="text-muted small mb-0">تأكد من صحة البيانات المعطاة في الملف.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="main-card p-4 border-0 shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h5 class="mb-0 fw-bold" style="color: #001f3f;">رفع الملف</h5>
                        <div class="d-flex gap-3 align-items-center">
                            @if($entity === 'internal-entities')
                            <div class="d-flex gap-2 align-items-center me-3">
                                <label class="small text-muted mb-0 fw-bold">الجهة المشرفة:</label>
                                <select id="authority_id" class="form-select form-select-sm custom-field" style="width: 180px;">
                                    <option value="">-- اختر --</option>
                                    @foreach($authorities as $auth)
                                        <option value="{{ $auth->id }}">{{ $auth->agency_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="d-flex gap-2 align-items-center">
                                <label class="small text-muted mb-0">طريقة الإدراج:</label>
                                <select id="importOperation" class="form-select form-select-sm custom-field" style="width: 140px;">
                                    <option value="both">تحديث وإضافة</option>
                                    <option value="insert">إضافة فقط</option>
                                    <option value="update">تحديث فقط</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <form id="importForm" class="text-center p-5 border border-dashed rounded-4 bg-light mb-4" id="dropZone">
                        <input type="file" id="importFileInput" class="d-none" accept=".xlsx,.xls,.csv">
                        <div class="mb-3">
                            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center border shadow-sm" style="width: 80px; height: 80px;">
                                <i class="fas fa-cloud-upload-alt fa-2x text-primary opacity-75"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">اسحب الملف هنا أو اضغط للرفع</h5>
                        <p class="text-muted small">XLSX, XLS, CSV (Max 5MB)</p>
                        <button type="button" class="btn btn-navy-gold px-5 mt-3 rounded-pill fw-bold shadow-sm" onclick="$('#importFileInput').click()">
                            اختيار ملف
                        </button>
                    </form>

                    <div id="fileInfo" class="alert alert-info py-2 px-3 rounded-pill d-none mb-4 shadow-sm border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-excel me-2"></i>
                                <span id="fileNameDisplay" class="small fw-semibold"></span>
                            </div>
                            <button type="button" class="btn-close small" onclick="resetForm()"></button>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="button" id="previewBtn" class="btn btn-outline-primary rounded-pill px-5 fw-bold shadow-sm hvr-grow">
                            <i class="fas fa-eye me-2"></i> معاينة البيانات
                        </button>
                    </div>
                </div>

                <div id="previewContainer" class="mt-4 main-card p-0 overflow-hidden" style="display: none;">
                    <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-primary">معاينة البيانات (أول 10 صفوف)</h6>
                        <span class="badge bg-warning text-dark rounded-pill px-3">تأكد من مراجعة البيانات قبل الحفظ</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table mb-0">
                            <thead>
                                <tr id="previewHeaders"></tr>
                            </thead>
                            <tbody id="previewRows"></tbody>
                        </table>
                    </div>
                    <div class="p-4 border-top text-center bg-light">
                        <button type="button" id="processBtn" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm fw-bold">
                            <i class="fas fa-save me-2"></i> اعتماد وحفظ البيانات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resultModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px;">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-4" style="color: #001f3f;">تم الاستيراد بنجاح</h3>
                
                <div class="row g-2 mb-4">
                    <div class="col-4"><div class="p-3 bg-light rounded-3 border"><h4 class="fw-bold mb-0" id="totalProcessed">0</h4><small class="text-muted">إجمالي</small></div></div>
                    <div class="col-4"><div class="p-3 bg-success-subtle rounded-3 border border-success"><h4 class="fw-bold text-success mb-0" id="countSuccessful">0</h4><small class="text-success small fw-bold">نجاح</small></div></div>
                    <div class="col-4"><div class="p-3 bg-danger-subtle rounded-3 border border-danger"><h4 class="fw-bold text-danger mb-0" id="countFailed">0</h4><small class="text-danger small fw-bold">فشل</small></div></div>
                </div>

                <div id="errorSummary" class="text-start p-3 bg-light rounded-3 mb-4 d-none border">
                    <h6 class="fw-bold text-danger mb-2 small"><i class="fas fa-exclamation-circle me-1"></i> أخطاء:</h6>
                    <div id="errorList" class="small text-muted" style="max-height: 150px; overflow-y: auto;"></div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-navy-gold py-3 fw-bold rounded-pill">العودة</a>
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
    .bg-success-subtle { background-color: #d1e7dd; }
    .bg-danger-subtle { background-color: #f8d7da; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    let currentFilePath = null;
    const entity = '{{ $entity }}';
    const configColumns = @json($config['columns']);

    $('#importFileInput').on('change', function() {
        if (this.files.length > 0) {
            $('#fileNameDisplay').text(this.files[0].name);
            $('#fileInfo').removeClass('d-none');
            $('#dropZone').addClass('opacity-50');
        }
    });

    window.resetForm = function() {
        $('#importForm')[0].reset();
        $('#fileInfo').addClass('d-none');
        $('#dropZone').removeClass('opacity-50');
        $('#previewContainer').hide();
        currentFilePath = null;
    };

    $('#previewBtn').on('click', function() {
        const fileInput = document.getElementById('importFileInput');
        if (fileInput.files.length === 0) {
            flasher.error('يرجى اختيار ملف أولاً');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        showLoader('جاري التحليل...');
        $.ajax({
            url: '{{ url("config") }}/' + entity + '/preview-import',
            type: 'POST', data: formData, contentType: false, processData: false,
            success: function(response) {
                hideLoader(); renderPreview(response);
                currentFilePath = response.filePath;
                $('#previewContainer').fadeIn();
            },
            error: function(xhr) { hideLoader(); flasher.error('خطأ في معالجة الملف'); }
        });
    });

    function renderPreview(data) {
        let hHtml = ''; data.headers.forEach(h => hHtml += `<th class="text-start">${h}</th>`);
        $('#previewHeaders').html(hHtml);
        let rHtml = ''; data.rows.forEach(row => {
            rHtml += '<tr>'; 
            data.headers.forEach(h => {
                rHtml += `<td class="text-muted small">${row[h] || '-'}</td>`;
            });
            rHtml += '</tr>';
        });
        $('#previewRows').html(rHtml);
    }

    $('#processBtn').on('click', function() {
        if (!currentFilePath) return;
        const fileHeaders = []; $('#previewHeaders th').each(function() { fileHeaders.push($(this).text().trim()); });
        const mapping = {};
        
        // Improved mapping logic with Arabic header support
        configColumns.forEach(idCol => {
            if (fileHeaders.includes(idCol)) {
                mapping[idCol] = idCol;
            } else if (idCol.endsWith('_id')) {
                const nCol = idCol.replace('_id', '_name');
                if (fileHeaders.includes(nCol)) mapping[nCol] = nCol;
            } else {
                // Try common Arabic patterns
                fileHeaders.forEach(fh => {
                    const normalized = fh.toLowerCase().trim();
                    if ((normalized.includes('الاسم') || normalized.includes('اسم')) && idCol === 'name') mapping[idCol] = fh;
                    if ((normalized.includes('الحالة') || normalized.includes('حالة')) && idCol === 'is_active') mapping[idCol] = fh;
                    if ((normalized.includes('الرمز') || normalized.includes('رمز')) && idCol === 'code') mapping[idCol] = fh;
                    if ((normalized.includes('الوصف') || normalized.includes('وصف')) && idCol === 'description') mapping[idCol] = fh;
                });
            }
        });

        showLoader('جاري الحفظ...');
        $.ajax({
            url: '{{ url("config") }}/' + entity + '/process-import',
            type: 'POST',
            data: { 
                _token: '{{ csrf_token() }}', 
                file_path: currentFilePath, 
                operation: $('#importOperation').val(), 
                mapping: mapping,
                authority_id: $('#authority_id').length ? $('#authority_id').val() : null
            },
            success: function(response) { hideLoader(); showResults(response); },
            error: function() { hideLoader(); flasher.error('خطأ في الحفظ'); }
        });
    });

    function showResults(data) {
        $('#totalProcessed').text(data.total); $('#countSuccessful').text(data.successful); $('#countFailed').text(data.failed);
        if (data.failed > 0) {
            let eHtml = ''; for (const l in data.errors) eHtml += `<div><b>السطر ${l}:</b> ${data.errors[l].join(', ')}</div>`;
            $('#errorList').html(eHtml); $('#errorSummary').removeClass('d-none');
        } else $('#errorSummary').addClass('d-none');
        new bootstrap.Modal(document.getElementById('resultModal')).show();
    }
});
</script>
@endpush
@endsection
