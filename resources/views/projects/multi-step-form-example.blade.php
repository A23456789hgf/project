@extends('layouts.app')

@section('styles')
<style>
    /* 1. إعدادات الصفحة الأساسية لتملأ الشاشة */
    html, body {
        height: 100%;
        margin: 0;
        overflow-y: auto; /* السماح بالتمرير عند الحاجة لضمان رؤية الأزرار */
        background-color: #f4f6f9;
        font-family: 'Cairo', system-ui, -apple-system, sans-serif;
    }

    /* حاوية الصفحة الرئيسية بتقنية Flexbox */
    .app-layout-wrapper {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 160px); /* ضبط الارتفاع ليتناسب مع الشاشة */
        width: 100%;
    }

    /* 2. منطقة المحتوى القابلة للتمرير */
    .content-scrollable-area {
        flex: 1; /* يأخذ المساحة المتبقية تلقائياً */
        overflow-y: auto;
        padding: 1.5rem;
        scroll-behavior: smooth;
        background-color: #fff;
        border-radius: 12px 12px 0 0;
        margin: 0 1rem;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.03);
    }

    /* تحسين شكل شريط التمرير */
    .content-scrollable-area::-webkit-scrollbar {
        width: 6px;
    }
    .content-scrollable-area::-webkit-scrollbar-track {
        background: transparent;
    }
    .content-scrollable-area::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .content-scrollable-area::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* 3. شريط الخطوات (Step Indicator) */
    .steps-container {
        padding: 1rem 1rem 0 1rem;
        background-color: #f4f6f9;
        z-index: 10;
        flex-shrink: 0; /* منع التقلص */
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        gap: 15px;
        padding-bottom: 15px;
        overflow-x: auto;
        scrollbar-width: none; /* إخفاء السكرول بار */
    }
    .step-indicator::-webkit-scrollbar { display: none; }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px 15px;
        min-width: 100px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        user-select: none;
    }

    .step-item.active {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        transform: translateY(-2px);
    }

    .step-item.completed {
        background: #f0fdf4;
        color: #16a34a;
        border-color: #bbf7d0;
    }
    
    .step-item.completed .step-icon {
        color: #16a34a;
    }
    
    .step-icon {
        font-size: 1.2rem;
        margin-bottom: 5px;
    }
    
    .step-text {
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    /* 4. تنسيق النماذج (Forms) */
    .step {
        display: none;
        animation: slideIn 0.3s ease-out;
        margin-bottom: 1.5rem;
    }
    
    .step.active {
        display: block;
    }

    /* 5. التذييل (Footer Action Bar) */
    .action-footer {
        background: #fff;
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        z-index: 100; /* رفع مستوى الطبقة لضمان الظهور */
        box-shadow: 0 -10px 25px rgba(0,0,0,0.05);
        position: sticky;
        bottom: 0;
    }

    .progress-wrapper {
        flex: 1;
        margin: 0 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .btn-navigation {
        min-width: 130px;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 6px;
        font-size: 0.9rem;
    }

    /* Example specific styles */
    .example-badge {
        background: #e2e8f0;
        color: #475569;
        font-weight: 600;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="app-layout-wrapper">
    
    <!-- Example Header -->
    <div class="px-4 py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-vial me-2"></i>نموذج تجريبي للمشروع (Multi-Step Example)
            </h5>
            <span class="example-badge">نسخة توضيحية</span>
        </div>
        <div class="text-muted small">
            <i class="fas fa-info-circle me-1"></i> يتم محاكاة العمليات هنا للأغراض التعليمية
        </div>
    </div>

    <div class="steps-container">
        <nav class="step-indicator">
            @foreach([
                ['icon' => 'fa-info-circle', 'text' => 'الأساسية'],
                ['icon' => 'fa-clipboard-list', 'text' => 'التفاصيل'],
                ['icon' => 'fa-shield-alt', 'text' => 'المخاطر'],
                ['icon' => 'fa-play', 'text' => 'التمهيدية'],
                ['icon' => 'fa-tasks', 'text' => 'التنفيذية'],
                ['icon' => 'fa-coins', 'text' => 'التمويل'],
                ['icon' => 'fa-check-double', 'text' => 'المراجعة']
            ] as $index => $step)
                <div class="step-item {{ $index == 0 ? 'active' : '' }}" 
                     onclick="goToStep({{ $index + 1 }})"
                     data-step="{{ $index + 1 }}">
                    <i class="fas {{ $step['icon'] }} step-icon"></i>
                    <span class="step-text">{{ $step['text'] }}</span>
                </div>
            @endforeach
        </nav>
    </div>

    <div class="content-scrollable-area" id="exampleScrollContainer">
        <form id="exampleForm" method="POST" novalidate>
            @csrf
            <input type="hidden" name="status" value="draft">
            <input type="hidden" id="project_id" name="project_id" value="{{ session('current_project_id', '') }}">

            <!-- Step 1: Basic Info -->
            <section class="step active" data-step="1">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">اسم المشروع </label>
                        <input type="text" class="form-control" name="project_name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">البرنامج </label>
                        <select class="form-select" name="program_id">
                            <option value="">اختر البرنامج</option>
                            <!-- Mock programs -->
                            <option value="1">برنامج التحول الوطني</option>
                            <option value="2">رؤية 2030</option>
                        </select>
                    </div>
                </div>
                <div class="alert alert-info mt-4">
                    <strong>ملاحظة:</strong> هذا النموذج يستخدم نفس تنسيق وبرمجة صفحة إنشاء المشاريع الرئيسية.
                </div>
            </section>

            <!-- Step 2: Details -->
            <section class="step" data-step="2">
                <div class="mb-3">
                    <label class="form-label">الهدف الرئيسي للمشروع</label>
                    <textarea class="form-control" name="main_objectives" rows="4"></textarea>
                </div>
            </section>

            <!-- Step 3: Risks -->
            <section class="step" data-step="3">
                <div class="mb-3">
                    <label class="form-label">إدارة المخاطر</label>
                    <div class="p-3 bg-light border rounded">
                        <p class="mb-0 text-muted">هنا يتم إدراج جداول المخاطر والجهات...</p>
                    </div>
                </div>
            </section>

            <!-- Step 4, 5, 6, 7 Placeholders -->
            @foreach(range(4, 7) as $stepNum)
            <section class="step" data-step="{{ $stepNum }}">
                <div class="text-center py-5">
                    <i class="fas fa-layer-group fa-3x text-light mb-3"></i>
                    <h5 class="text-muted">محتوى الخطوة {{ $stepNum }}</h5>
                    <p class="text-secondary">تمت مزامنة التخطيط والأزرار مع الصفحة الرئيسية</p>
                </div>
            </section>
            @endforeach
        </form>
    </div>

    <footer class="action-footer">
        <div class="d-flex">
            <button type="button" id="prevBtn" class="btn btn-secondary btn-navigation px-4" disabled>
                 السابق <i class="fas fa-chevron-right ms-1"></i>
            </button>
        </div>

        <div class="progress-wrapper">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted fw-bold">التقدم</small>
                <small class="text-primary fw-bold"><span id="currentStepDisplay">1</span> / 7</small>
            </div>
            <div class="progress" style="height: 6px; border-radius: 10px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 14.28%; border-radius: 10px;"></div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="button" id="saveBtn" class="btn btn-light border btn-navigation">
                <i class="fas fa-save text-muted me-1"></i> مسودة
            </button>
            <button type="button" id="nextBtn" class="btn btn-primary btn-navigation px-4">
                التالي <i class="fas fa-chevron-left me-1"></i>
            </button>
        </div>
    </footer>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const FormManager = {
        currentStep: 1,
        totalSteps: 7,
        
        init: function() {
            this.bindEvents();
            this.updateUI();
            this.updateProgressBar();
            console.log('✅ Example Form Manager Initialized');
        },
        
        bindEvents: function() {
            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const saveBtn = document.getElementById('saveBtn');
            const self = this;
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => this.nextStep());
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => this.prevStep());
            }
            
            if (saveBtn) {
                saveBtn.addEventListener('click', () => this.saveDraft());
            }
            
            document.querySelectorAll('.step-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    const step = parseInt(item.dataset.step);
                    this.goToStep(step);
                });
            });
        },
        
        nextStep: function() {
            if (this.currentStep < this.totalSteps) {
                this.goToStep(this.currentStep + 1);
            } else {
                this.submitForm();
            }
        },
        
        prevStep: function() {
            if (this.currentStep > 1) {
                this.goToStep(this.currentStep - 1);
            }
        },
        
        goToStep: function(step) {
            if (step < 1 || step > this.totalSteps) return;
            
            // Hide all steps
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
            
            // Show new step
            const nextStepEl = document.querySelector(`.step[data-step="${step}"]`);
            if (nextStepEl) {
                nextStepEl.classList.add('active');
                this.currentStep = step;
                this.updateUI();
                this.updateProgressBar();
                this.scrollToTop();
            }
        },
        
        updateUI: function() {
            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const currentStepDisplay = document.getElementById('currentStepDisplay');
            
            if (currentStepDisplay) currentStepDisplay.textContent = this.currentStep;
            
            if (prevBtn) prevBtn.disabled = (this.currentStep === 1);
            
            if (nextBtn) {
                if (this.currentStep === this.totalSteps) {
                    nextBtn.innerHTML = 'إرسال <i class="fas fa-check-double ms-1"></i>';
                    nextBtn.classList.remove('btn-primary');
                    nextBtn.classList.add('btn-success');
                } else {
                    nextBtn.innerHTML = 'التالي <i class="fas fa-chevron-left me-1"></i>';
                    nextBtn.classList.remove('btn-success');
                    nextBtn.classList.add('btn-primary');
                }
            }

            // Update step indicator
            document.querySelectorAll('.step-item').forEach(item => {
                const step = parseInt(item.dataset.step);
                item.classList.remove('active', 'completed');
                if (step === this.currentStep) {
                    item.classList.add('active');
                } else if (step < this.currentStep) {
                    item.classList.add('completed');
                }
            });
        },
        
        updateProgressBar: function() {
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                const percentage = (this.currentStep / this.totalSteps) * 100;
                progressBar.style.width = `${percentage}%`;
            }
        },
        
        scrollToTop: function() {
            const container = document.getElementById('exampleScrollContainer');
            if (container) container.scrollTop = 0;
        },
        
        saveDraft: function() {
            alert('تم محاكاة حفظ المسودة للخطوة ' + this.currentStep);
        },
        
        submitForm: function() {
            alert('تم محاكاة إرسال المشروع النهائي');
        }
    };

    FormManager.init();
});
</script>
@endsection
