@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/modern-reports.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    .custom-card-check {
        transition: all 0.25s ease;
        cursor: pointer;
        border: 1px solid #e2e8f0 !important;
        background: #fff;
    }
    .custom-card-check:hover {
        background-color: #f8faf8;
        border-color: var(--report-primary) !important;
        transform: translateY(-2px);
    }
    .active-check {
        border-color: var(--report-primary) !important;
        background-color: #f0f7f0 !important;
        box-shadow: 0 4px 12px rgba(44, 95, 45, 0.08);
    }
    .text-report-primary { color: var(--report-primary) !important; }
    .bg-report-primary { background-color: var(--report-primary) !important; }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    /* Select2 Green Theme Overrides */
    .select2-container--bootstrap-5 .select2-selection--single:focus,
    .select2-container--bootstrap-5 .select2-selection--multiple:focus {
        border-color: var(--report-primary);
        box-shadow: 0 0 0 0.25rem rgba(44, 95, 45, 0.15);
    }
    .select2-container--bootstrap-5 .select2-dropdown .select2-results__option--highlighted[aria-selected] {
        background-color: var(--report-primary);
    }
</style>
@endpush

@section('content')
<div class="report-container pt-3">
    <div class="row align-items-center mb-4 no-print">
        <div class="col">
            <h2 class="mb-1 text-report-primary font-weight-bold">
                <i class="fas fa-print me-2"></i>مركز طباعة التقارير الرسمية
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('projects.reports.index') }}" class="text-report-primary">التقارير</a></li>
                    <li class="breadcrumb-item active">خيارات الطباعة</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto">
            <a href="{{ route('projects.reports.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-right me-2"></i>عودة للمركز الرئيسي
            </a>
        </div>
    </div>

    

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0 border-top border-4 border-report-primary">
                <div class="card-header bg-white py-4 px-4 border-bottom-0">
                    <h4 class="mb-0 text-dark font-weight-bold">تجهيز وتصدير الوثائق</h4>
                    <p class="text-muted small mb-0 mt-1">اختر التنسيق والمشاريع المطلوبة لإنشاء تقرير PDF متوافق مع الهوية الرسمية</p>
                </div>
                <div class="card-body p-4 pt-0">
                    @can('reports.print')
                    <form action="{{ route('projects.reports.print_generate') }}" method="POST" target="_blank">
                        @csrf
                        
                        <!-- Report Type Selection -->
                        <div class="mb-5 mt-4">
                            <label class="form-label fw-bold mb-3 text-dark">1. نطاق التقرير</label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check custom-card-check h-100 p-4 rounded-3 active-check shadow-sm">
                                        <input class="form-check-input" type="radio" name="report_type" id="typeIndividual" value="individual" checked onchange="toggleProjectSelection()">
                                        <label class="form-check-label w-100 cursor-pointer" for="typeIndividual">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="icon-circle bg-light text-report-primary me-3" style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-file-alt fa-lg"></i>
                                                </div>
                                                <span class="fw-bold text-dark fs-5">مشروع فردي</span>
                                            </div>
                                            <small class="text-muted d-block line-height-sm">طباعة تقرير تفصيلي شامل لمشروع واحد محدد بجميع بياناته.</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check custom-card-check h-100 p-4 rounded-3 shadow-sm">
                                        <input class="form-check-input" type="radio" name="report_type" id="typeGroup" value="group" onchange="toggleProjectSelection()">
                                        <label class="form-check-label w-100 cursor-pointer" for="typeGroup">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="icon-circle bg-light text-success me-3" style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-copy fa-lg"></i>
                                                </div>
                                                <span class="fw-bold text-dark fs-5">مجموعة مشاريع</span>
                                            </div>
                                            <small class="text-muted d-block line-height-sm">تحديد عدة مشاريع مخصصة لدمجها في وثيقة تقرير واحدة.</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check custom-card-check h-100 p-4 rounded-3 shadow-sm">
                                        <input class="form-check-input" type="radio" name="report_type" id="typeAll" value="all" onchange="toggleProjectSelection()">
                                        <label class="form-check-label w-100 cursor-pointer" for="typeAll">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="icon-circle bg-light text-warning me-3" style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-folder-open fa-lg"></i>
                                                </div>
                                                <span class="fw-bold text-dark fs-5">كل المشاريع</span>
                                            </div>
                                            <small class="text-muted d-block line-height-sm">إصدار تقرير هيراركي يشمل كافة المشاريع المسجلة في النظام.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selection Area with Animation background -->
                        <div class="p-4 rounded-3 mb-4 bg-light border border-dashed" id="selectionSection">
                            <!-- Project Selection (Individual) -->
                            <div class="mb-0" id="individualSelection">
                                <label for="project_id" class="form-label fw-bold text-dark mb-2">2. تحديد المشروع</label>
                                <select class="form-select select2" name="project_id" id="project_id">
                                    <option value="">-- اضغط للبحث عن اسم أو رقم المشروع --</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">
                                            #{{ $project->form_number }} - {{ $project->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Project Selection (Group) -->
                            <div class="mb-0 d-none" id="groupSelection">
                                <label for="project_ids" class="form-label fw-bold text-dark mb-2">2. تحديد المشاريع المختارة</label>
                                <select class="form-select select2-multiple" name="project_ids[]" id="project_ids" multiple>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">
                                            #{{ $project->form_number }} - {{ $project->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-2 text-report-primary">
                                    <i class="fas fa-info-circle me-1"></i>يمكنك كتابة الأسماء أو الأرقام للاختيار المتعدد.
                                </div>
                            </div>

                            <div id="allSelectedInfo" class="d-none py-2 text-center">
                                <div class="badge bg-report-primary rounded-pill px-4 py-2 fs-6">
                                    <i class="fas fa-check-double me-2"></i>سيتم تضمين جميع المشاريع ({{ count($projects) }}) في التقرير
                                </div>
                            </div>
                        </div>

                        <!-- Report Orientation -->
                        <div class="mb-5">
                            <label class="form-label fw-bold mb-3 text-dark">3. تنسيق وإخراج الصفحة</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check custom-card-check p-4 rounded-3 active-check shadow-sm">
                                        <input class="form-check-input" type="radio" name="orientation" id="orientLandscape" value="L" checked>
                                        <label class="form-check-label w-100 cursor-pointer" for="orientLandscape">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle bg-white text-report-primary me-3 border shadow-sm" style="width: 50px; height: 35px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-arrows-alt-h"></i>
                                                </div>
                                                <div>
                                                    <strong class="text-dark fs-5">عرض أفقي (Landscape)</strong>
                                                    <div class="small text-muted mt-1">مثالي للتقارير التي تحتوي على جداول بيانات مالية وفنية ممتدة.</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check custom-card-check p-4 rounded-3 shadow-sm">
                                        <input class="form-check-input" type="radio" name="orientation" id="orientPortrait" value="P">
                                        <label class="form-check-label w-100 cursor-pointer" for="orientPortrait">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle bg-white text-dark me-3 border shadow-sm" style="width: 35px; height: 50px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-arrows-alt-v"></i>
                                                </div>
                                                <div>
                                                    <strong class="text-dark fs-5">اتجاه عمودي (Portrait)</strong>
                                                    <div class="small text-muted mt-1">التنسيق القياسي للمذكرات والتقارير الإدارية الموجزة.</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert bg-soft-report-primary border-0 rounded-3 p-3 mb-5 d-flex align-items-center" style="background-color: rgba(44, 95, 45, 0.05);">
                            <i class="fas fa-certificate text-report-primary fs-3 me-3"></i>
                            <div class="small text-dark">
                                جميع التقارير المصدرة من هذا المركز تتضمن الترويسة الرسمية للجمهورية والوزارة، وتعتبر وثائق رسمية صالحة للتقديم والأرشفة.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="reset" class="btn btn-light rounded-pill px-4 border">
                                <i class="fas fa-undo me-2"></i>إعادة الضبط
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 font-weight-bold bg-report-primary border-0 shadow-lg">
                                <i class="fas fa-file-pdf me-2"></i>إنشاء ملف التقرير الفوري
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-5">
                        <div class="icon-circle bg-light text-danger mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-lock fa-3x"></i>
                        </div>
                        <h4 class="text-dark font-weight-bold">عفواً، لا تملك صلاحية الطباعة</h4>
                        <p class="text-muted">يرجى التواصل مع مسؤول النظام لطلب صلاحية تصدير التقارير الرسمية.</p>
                        <a href="{{ route('projects.reports.index') }}" class="btn btn-primary rounded-pill px-4 mt-3 bg-report-primary border-0">العودة للتقارير</a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: "bootstrap-5",
            dir: "rtl",
            width: '100%',
            placeholder: 'البحث عن مشروع...',
            dropdownParent: $('body')
        });

        $('.select2-multiple').select2({
            theme: "bootstrap-5",
            dir: "rtl",
            width: '100%',
            placeholder: 'البحث واختيار عدة مشاريع...',
            closeOnSelect: false,
            allowClear: true,
            dropdownParent: $('body')
        });

        // Initialize state
        toggleProjectSelection();
        
        // Visual feedback for radio cards
        $('input[type="radio"]').on('change', function() {
            // Remove active class from all in same group
            let name = $(this).attr('name');
            $('input[name="' + name + '"]').closest('.custom-card-check').removeClass('active-check');
            
            // Add to selected
            if($(this).is(':checked')) {
                $(this).closest('.custom-card-check').addClass('active-check');
            }
        });
    });

    function toggleProjectSelection() {
        const type = $('input[name="report_type"]:checked').val();
        
        // Hide all first
        $('#individualSelection').addClass('d-none');
        $('#groupSelection').addClass('d-none');
        $('#allSelectedInfo').addClass('d-none');
        $('#selectionSection').removeClass('d-none');
        
        if (type === 'individual') {
            $('#individualSelection').removeClass('d-none');
            $('#project_id').prop('required', false);
            $('#project_ids').prop('required', false);
        } else if (type === 'group') {
            $('#groupSelection').removeClass('d-none');
            $('#project_id').prop('required', false);
            $('#project_ids').prop('required', false);
        } else {
            // All
            $('#allSelectedInfo').removeClass('d-none');
            $('#project_id').prop('required', false);
            $('#project_ids').prop('required', false);
        }
    }
</script>
@endsection

