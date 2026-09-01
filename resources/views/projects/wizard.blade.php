@extends('layouts.app')

@section('title', 'معالج إنشاء المشروع')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/project-wizard.css') }}">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="page-title">
                    @if(isset($project))
                        تعديل المشروع: {{ $project->project_name }}
                    @else
                        إنشاء مشروع جديد
                    @endif
                </h1>
                <div class="page-subtitle">
                    استخدم هذا المعالج لإنشاء أو تعديل مشروع بطريقة منظمة خطوة بخطوة
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form class="multi-step-form" id="projectWizardForm" 
                  action="{{ isset($project) ? route('projects.update', $project) : route('projects.store') }}" 
                  method="POST"
                  @if(isset($project)) data-project-id="{{ $project->id }}" @endif>
                @csrf
                @if(isset($project))
                    @method('PUT')
                @endif
                <!-- Step Progress -->
                <div class="step-progress">
                    <div class="steps-container">
                            <div class="step active">
                                <div class="step-number">1</div>
                                <div class="step-title">بيانات المشروع والموقع</div>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <div class="step-title">تفاصيل المشروع والأهداف</div>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <div class="step-title">البيانات المالية</div>
                            </div>
                            <div class="step">
                                <div class="step-number">4</div>
                                <div class="step-title">الجهات المشاركة</div>
                            </div>
                            <div class="step">
                                <div class="step-number">5</div>
                                <div class="step-title">خطة التنفيذ</div>
                            </div>
                        </div>
                    </div>

                <!-- Form Content -->
                <div class="form-content">
                        <!-- Step 1: Project Data and Location -->
                        <div class="step-content active" data-step="1">
                            <h3>الخطوة الأولى: بيانات المشروع والموقع</h3>
                            
                            <div class="form-group">
                                <label>اسم المشروع *</label>
                                <input type="text" id="project_name" class="form-control wizard-input" 
                                       data-step="step1" data-field="project_name">
                                <div id="project_name-error" class="invalid-feedback"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>البرنامج *</label>
                                        <select id="program_id" class="form-control wizard-input" 
                                                data-step="step1" data-field="program_id">
                                            <option value="">اختر البرنامج</option>
                                        </select>
                                        <div id="program_id-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>المجال *</label>
                                        <select id="domain_id" class="form-control domain-select wizard-input" 
                                                data-step="step1" data-field="domain_id">
                                            <option value="">اختر المجال</option>
                                        </select>
                                        <div id="domain_id-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>المجال الفرعي *</label>
                                        <select id="subdomain_id" class="form-control subdomain-select wizard-input" 
                                                data-step="step1" data-field="subdomain_id">
                                            <option value="">اختر المجال الفرعي</option>
                                        </select>
                                        <div id="subdomain_id-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>التدخل *</label>
                                        <select id="intervention_id" class="form-control wizard-input" 
                                                data-step="step1" data-field="intervention_id">
                                            <option value="">اختر التدخل</option>
                                        </select>
                                        <div id="intervention_id-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>تاريخ البداية (ميلادي) *</label>
                                        <input type="date" id="start_date_gregorian" class="form-control wizard-input" 
                                               data-step="step1" data-field="start_date_gregorian">
                                        <div id="start_date_gregorian-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>تاريخ البداية (هجري) *</label>
                                        <input type="text" id="start_date_hijri" class="form-control wizard-input" 
                                               data-step="step1" data-field="start_date_hijri">
                                        <div id="start_date_hijri-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>تاريخ النهاية (ميلادي) *</label>
                                        <input type="date" id="end_date_gregorian" class="form-control wizard-input" 
                                               data-step="step1" data-field="end_date_gregorian">
                                        <div id="end_date_gregorian-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>تاريخ النهاية (هجري) *</label>
                                        <input type="text" id="end_date_hijri" class="form-control wizard-input" 
                                               data-step="step1" data-field="end_date_hijri">
                                        <div id="end_date_hijri-error" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>عدد المستفيدين *</label>
                                <input type="number" id="number_of_beneficiaries" class="form-control wizard-input" 
                                       data-step="step1" data-field="number_of_beneficiaries">
                                <div id="number_of_beneficiaries-error" class="invalid-feedback"></div>
                            </div>

                            <!-- Locations -->
                            <div class="locations-section">
                                <h4>المواقع</h4>
                                <div id="locations-container">
                                    <!-- Locations will be rendered here -->
                                </div>
                                <button type="button" class="btn btn-secondary add-location">إضافة موقع</button>
                            </div>
                        </div>

                        <!-- Step 2: Project Details and Objectives -->
                        <div class="step-content" data-step="2">
                            <h3>الخطوة الثانية: تفاصيل المشروع والأهداف</h3>
                            
                            <div class="form-group">
                                <label>هل المشروع جزء من خطة؟ *</label>
                                <div class="form-check">
                                    <input type="radio" id="is_part_of_plan_yes" name="is_part_of_plan" value="true" 
                                           class="form-check-input wizard-input" data-step="step2" data-field="is_part_of_plan">
                                    <label class="form-check-label" for="is_part_of_plan_yes">نعم</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" id="is_part_of_plan_no" name="is_part_of_plan" value="false" 
                                           class="form-check-input wizard-input" data-step="step2" data-field="is_part_of_plan">
                                    <label class="form-check-label" for="is_part_of_plan_no">لا</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>اسم الخطة</label>
                                <input type="text" id="plan_name" class="form-control wizard-input" 
                                       data-step="step2" data-field="plan_name">
                            </div>

                            <div class="form-group">
                                <label>وصف المشروع *</label>
                                <textarea id="project_description" class="form-control wizard-input" 
                                          data-step="step2" data-field="project_description" rows="4"></textarea>
                                <div id="project_description-error" class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label>مبررات المشروع *</label>
                                <textarea id="project_justification" class="form-control wizard-input" 
                                          data-step="step2" data-field="project_justification" rows="4"></textarea>
                                <div id="project_justification-error" class="invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label>أهداف المشروع *</label>
                                <textarea id="project_goals" class="form-control wizard-input" 
                                          data-step="step2" data-field="project_goals" rows="4"></textarea>
                                <div id="project_goals-error" class="invalid-feedback"></div>
                            </div>

                            <!-- Main Objectives -->
                            <div class="objectives-section">
                                <h4>الأهداف الرئيسية</h4>
                                <div id="main_objectives-container">
                                    <!-- Main objectives will be rendered here -->
                                </div>
                                <button type="button" class="btn btn-secondary add-objective" data-type="main_objectives">إضافة هدف رئيسي</button>
                            </div>

                            <!-- Specific Objectives -->
                            <div class="objectives-section">
                                <h4>الأهداف المحددة</h4>
                                <div id="specific_objectives-container">
                                    <!-- Specific objectives will be rendered here -->
                                </div>
                                <button type="button" class="btn btn-secondary add-objective" data-type="specific_objectives">إضافة هدف محدد</button>
                            </div>
                        </div>

                        <!-- Step 3: Financial Data -->
                        <div class="step-content" data-step="3">
                            <h3>الخطوة الثالثة: البيانات المالية</h3>
                            
                            <div class="financial-section">
                                <h4>البنود المالية</h4>
                                <div id="financial-items-container">
                                    <!-- Financial items will be rendered here -->
                                </div>
                                <button type="button" class="btn btn-secondary add-financial-item">إضافة بند مالي</button>
                            </div>
                        </div>

                        <div class="step-content" data-step="4">
                            <h3>الخطوة الرابعة: الجهات المشاركة</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الجهات المنفذة</label>
                                        <select id="executing_entities" name="executing_entities[]" class="form-control select2" multiple data-placeholder="اختر الجهات المنفذة">
                                            @foreach($authorities as $authority)
                                                <option value="{{ $authority->id }}" 
                                                    {{ isset($selectedExecuting) && in_array($authority->id, $selectedExecuting) ? 'selected' : '' }}>
                                                    {{ $authority->agency_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الجهات الممولة</label>
                                        <select id="funding_entities" name="funding_entities[]" class="form-control select2" multiple data-placeholder="اختر الجهات الممولة">
                                            @foreach($authorities as $authority)
                                                <option value="{{ $authority->id }}" 
                                                    {{ isset($selectedFunding) && in_array($authority->id, $selectedFunding) ? 'selected' : '' }}>
                                                    {{ $authority->agency_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الجهات المشاركة</label>
                                        <select id="participating_entities" name="participating_entities[]" class="form-control select2" multiple data-placeholder="اختر الجهات المشاركة">
                                            @foreach($authorities as $authority)
                                                <option value="{{ $authority->id }}" 
                                                    {{ isset($selectedParticipating) && in_array($authority->id, $selectedParticipating) ? 'selected' : '' }}>
                                                    {{ $authority->agency_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الجهات المشرفة</label>
                                        <select id="supervising_entities" name="supervising_entities[]" class="form-control select2" multiple data-placeholder="اختر الجهات المشرفة">
                                            @foreach($authorities as $authority)
                                                <option value="{{ $authority->id }}" 
                                                    {{ isset($selectedSupervising) && in_array($authority->id, $selectedSupervising) ? 'selected' : '' }}>
                                                    {{ $authority->agency_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Implementation Plan -->
                        <div class="step-content" data-step="5">
                            <h3>الخطوة الخامسة: خطة التنفيذ</h3>
                            
                            <div class="form-group">
                                <label>خطة التنفيذ</label>
                                <textarea id="implementation_plan" class="form-control wizard-input" 
                                          data-step="step5" data-field="implementation_plan" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label>خطة المتابعة</label>
                                <textarea id="monitoring_plan" class="form-control wizard-input" 
                                          data-step="step5" data-field="monitoring_plan" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label>إدارة المخاطر</label>
                                <textarea id="risk_management" class="form-control wizard-input" 
                                          data-step="step5" data-field="risk_management" rows="4"></textarea>
                            </div>

                            <div class="form-group">
                                <label>خطة الاستدامة</label>
                                <textarea id="sustainability_plan" class="form-control wizard-input" 
                                          data-step="step5" data-field="sustainability_plan" rows="4"></textarea>
                            </div>
                        </div>
                    </div>

                <!-- Navigation -->
                <div class="form-navigation">
                    <button type="button" class="btn-prev btn-step btn-step-secondary" style="display: none;">
                        <i class="fas fa-arrow-right"></i> السابق
                    </button>
                    
                    <div class="flex-grow-1"></div>
                    
                    <button type="button" class="btn-save-draft btn-step btn-step-info" style="margin-left: 10px; margin-right: 10px;">
                        <i class="fas fa-save"></i> حفظ كمسودة
                    </button>
                    
                    <button type="button" class="btn-next btn-step btn-step-primary">
                        التالي <i class="fas fa-arrow-left"></i>
                    </button>
                    
                    <button type="button" class="btn-submit btn-step btn-step-success" style="display: none;" data-original-text="{{ isset($project) ? 'تحديث المشروع' : 'إنشاء المشروع' }}">
                        <i class="fas fa-check"></i> {{ isset($project) ? 'تحديث المشروع' : 'إنشاء المشروع' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@include('projects.partials.unit-fetch-script')
@endsection

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .toast-container {
        z-index: 9999;
    }

    .toast {
        min-width: 300px;
    }

    .toast.show {
        opacity: 1;
    }

    .toast-success {
        border-left: 4px solid #28a745;
    }

    .toast-error {
        border-left: 4px solid #dc3545;
    }

    .toast-warning {
        border-left: 4px solid #ffc107;
    }

    .toast-info {
        border-left: 4px solid #17a2b8;
    }

    /* RTL Support */
    [dir="rtl"] .toast-container {
        left: 0;
        right: auto;
    }

    /* Loading Spinner */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Custom scrollbar for long forms */
    .wizard-content {
        max-height: 70vh;
        overflow-y: auto;
    }

    .wizard-content::-webkit-scrollbar {
        width: 6px;
    }

    .wizard-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .wizard-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .wizard-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .page-subtitle {
            font-size: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/project-wizard.js') }}"></script>
<script>
// Toast notification system using PHP Flasher
class ToastManager {
    show(message, type = 'info') {
        const method = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
        if (typeof flasher !== 'undefined') {
            try {
                flasher[method](message);
                return;
            } catch (e) {
                console.error('Flasher error:', e);
            }
        }
        if (window.AppUtils && window.AppUtils.Utils) {
            window.AppUtils.Utils.showToast(message, type);
            return;
        }
        console.log(`[${type.toUpperCase()}]: ${message}`);
    }

    success(message) {
        this.show(message, 'success');
    }

    error(message) {
        this.show(message, 'error');
    }

    warning(message) {
        this.show(message, 'warning');
    }

    info(message) {
        this.show(message, 'info');
    }
}

// Initialize toast manager
document.addEventListener('DOMContentLoaded', function() {
    window.$toast = new ToastManager();
    
    // Add global error handler
    window.addEventListener('unhandledrejection', (event) => {
        console.error('Unhandled promise rejection:', event.reason);
        window.$toast.error('حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.');
    });
});

// Add loading indicator for fetch requests
let loadingCount = 0;

// Override fetch to add loading indicator
const originalFetch = window.fetch;
window.fetch = function(...args) {
    loadingCount++;
    document.body.classList.add('loading');
    
    return originalFetch.apply(this, args)
        .then(response => {
            loadingCount--;
            if (loadingCount === 0) {
                document.body.classList.remove('loading');
            }
            return response;
        })
        .catch(error => {
            loadingCount--;
            if (loadingCount === 0) {
                document.body.classList.remove('loading');
            }
            throw error;
        });
};
</script>

<style>
/* Loading indicator styles */
body.loading {
    cursor: wait;
}

body.loading * {
    pointer-events: none;
}

body.loading::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
}

body.loading::before {
    content: '';
    position: fixed;
    top: 50%;
    left: 50%;
    width: 50px;
    height: 50px;
    margin: -25px 0 0 -25px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 9999;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script src="{{ asset('js/project-wizard.js') }}"></script>
@endpush