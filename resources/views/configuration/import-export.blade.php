@extends('layouts.app')

@section('title', 'مركز استيراد وتصدير البيانات')

@section('content')
@include('configuration.shared_styles')

<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="page-header-box mb-4">
            <h2 class="page-main-title">
                <i class="fas fa-database me-2 text-primary"></i> مركز استيراد وتصدير البيانات
            </h2>
            <p class="text-muted small">إدارة مركزية لجميع بيانات النظام. يمكنك استيراد أو تصدير البيانات بسهولة.</p>
            <div class="section-divider mt-2"></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="main-card h-100 p-3 border-0 shadow-sm rounded-4">
                    <h5 class="fw-bold mb-3 px-2" style="color: #001f3f;">
                        <i class="fas fa-layer-group me-2 text-primary"></i> أنواع البيانات
                    </h5>
                    <div class="list-group list-group-flush" id="entityList">
                        @foreach($entities as $entity)
                        <button type="button" 
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 mb-1 border-0 rounded-3 entity-item" 
                                data-entity="{{ $entity['key'] }}"
                                data-title="{{ $entity['title'] }}">
                            <div class="d-flex align-items-center">
                                <div class="bg-light text-primary me-3 rounded p-2" style="width: 40px; text-align: center;">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <span class="fw-semibold small">{{ $entity['title'] }}</span>
                            </div>
                            <span class="badge bg-navy text-white rounded-pill px-2 py-1 small shadow-sm">{{ $entity['count'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div id="emptyState" class="main-card py-5 text-center d-flex align-items-center justify-content-center h-100 rounded-4 border-0">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                                <i class="fas fa-mouse-pointer fa-3x text-muted opacity-50"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">اختر نوع البيانات للبدء</h4>
                        <p class="text-muted w-75 mx-auto small">قم باختيار أحد الكيانات من القائمة الجانبية لعرض خيارات الاستيراد والتصدير المتاحة.</p>
                    </div>
                </div>

                <div id="contentPanel" class="main-card h-100 rounded-4 border-0 shadow-sm" style="display: none;">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning-subtle text-warning rounded p-2 me-3">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-primary" id="selectedTitle">إدارة البيانات</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 border bg-light h-100 d-flex flex-column shadow-sm hover-lift">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-file-export me-2 text-primary"></i>تصدير البيانات</h6>
                                    <p class="small text-muted mb-4">تحميل جميع البيانات المسجلة بتنسيق Excel.</p>
                                    <div class="mt-auto">
                                        <a href="#" id="exportBtn" class="btn btn-navy-gold w-100 fw-bold shadow-sm">تصدير Excel</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-4 rounded-4 border bg-light h-100 d-flex flex-column shadow-sm hover-lift">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-file-invoice me-2 text-success"></i>قوالب الاستيراد</h6>
                                    <p class="small text-muted mb-4">تحميل نموذج فارغ لتعبئة البيانات.</p>
                                    <div class="mt-auto d-flex gap-2">
                                        <a href="#" id="templateExcelBtn" class="btn btn-outline-success flex-grow-1 fw-bold">Excel</a>
                                        <a href="#" id="templateCsvBtn" class="btn btn-outline-secondary flex-grow-1 fw-bold">CSV</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-4 border bg-white shadow-sm position-relative">
                            <h6 class="fw-bold mb-4 d-flex align-items-center">
                                <span class="bg-warning-subtle text-warning rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </span>
                                استيراد بيانات جديدة
                            </h6>
                            <form id="importForm" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <input type="file" name="file" class="form-control custom-field" accept=".xlsx,.xls,.csv" required id="importFileInput">
                                    <div class="form-text small mt-2"><i class="fas fa-info-circle me-1"></i> الحد الأقصى: 5 ميجابايت.</div>
                                </div>
                                <button type="button" id="previewBtn" class="btn btn-warning text-dark fw-bold w-100 py-3 shadow-sm rounded-pill">
                                    <i class="fas fa-eye me-2"></i>معاينة البيانات
                                </button>
                            </form>
                        </div>

                        <div id="previewContainer" class="mt-4 border rounded-4 overflow-hidden shadow-sm" style="display: none;">
                            <div class="bg-light p-3 border-bottom">
                                <h6 class="fw-bold mb-0 text-primary small">معاينة أول 10 سجلات</h6>
                            </div>
                            <div class="table-responsive bg-white" style="max-height: 300px;">
                                <table class="table custom-table table-sm mb-0">
                                    <thead class="sticky-top bg-white"><tr id="previewHeaders"></tr></thead>
                                    <tbody id="previewRows"></tbody>
                                </table>
                            </div>
                            <div class="p-4 bg-light border-top">
                                <div class="row align-items-end g-3">
                                    <div class="col-md-7">
                                        <select class="form-select custom-field" id="importOperation">
                                            <option value="both">إضافة الجديد وتحديث الموجود</option>
                                            <option value="insert">إضافة الجديد فقط</option>
                                            <option value="update">تحديث الموجود فقط</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <button type="button" id="processBtn" class="btn btn-success fw-bold w-100 py-2 rounded-pill shadow-sm">تأكيد الاستيراد</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .entity-item { transition: all 0.3s ease; border-right: 4px solid transparent !important; }
    .entity-item:hover { background-color: #f8f9fa; transform: translateX(-5px); }
    .entity-item.active { background-color: #e6f0f9 !important; border-right-color: #001f3f !important; color: #001f3f; font-weight: bold; }
    .bg-navy { background-color: #001f3f; }
    .bg-warning-subtle { background-color: #fff3cd; }
    .hover-lift:hover { transform: translateY(-5px); }
</style>

@section('scripts')
<script>
$(document).ready(function() {
    let currentEntity = null; let currentFilePath = null;
    $('.entity-item').on('click', function() {
        $('.entity-item').removeClass('active'); $(this).addClass('active');
        currentEntity = $(this).data('entity'); const title = $(this).data('title');
        $('#emptyState').hide(); $('#contentPanel').fadeIn();
        $('#selectedTitle').text('إدارة ' + title); $('#previewContainer').hide(); $('#importForm')[0].reset();
        const baseUrl = '{{ url("config") }}/' + currentEntity;
        $('#exportBtn').attr('href', baseUrl + '/export-excel');
        $('#templateExcelBtn').attr('href', baseUrl + '/download-template?format=xlsx');
        $('#templateCsvBtn').attr('href', baseUrl + '/download-template?format=csv');
    });

    $('#previewBtn').on('click', function() {
        if (!currentEntity) return;
        const fileInput = document.getElementById('importFileInput');
        if (fileInput.files.length === 0) { flasher.error('يرجى اختيار ملف'); return; }
        const formData = new FormData(); formData.append('file', fileInput.files[0]); formData.append('_token', '{{ csrf_token() }}');
        showLoader('قراءة البيانات...');
        $.ajax({
            url: '{{ url("config") }}/' + currentEntity + '/preview-import',
            type: 'POST', data: formData, contentType: false, processData: false,
            success: function(res) {
                hideLoader(); currentFilePath = res.filePath;
                let hH = ''; res.headers.forEach(h => hH += `<th class="text-start">${h}</th>`); $('#previewHeaders').html(hH);
                let rH = ''; res.rows.forEach(r => { rH += '<tr>'; r.forEach(c => rH += `<td class="small text-muted">${c||''}</td>`); rH += '</tr>'; });
                $('#previewRows').html(rH); $('#previewContainer').fadeIn();
            },
            error: function(xhr) { hideLoader(); flasher.error('خطأ في معالجة الملف'); }
        });
    });

    $('#processBtn').on('click', function() {
        if (!currentEntity || !currentFilePath) return;
        const mapping = {}; $('#previewHeaders th').each(function() { const h = $(this).text(); mapping[h] = h; });
        showLoader('جاري الحفظ...');
        $.ajax({
            url: '{{ url("config") }}/' + currentEntity + '/process-import',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', file_path: currentFilePath, operation: $('#importOperation').val(), mapping: mapping },
            success: function(res) { hideLoader(); alert('تم بنجاح: ' + res.successful + ' سجل'); location.reload(); },
            error: function() { hideLoader(); flasher.error('خطأ في الحفظ'); }
        });
    });
});
</script>
@endsection
@endsection
