document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    console.log('🚀 Multi-Step Form Initialized');
    
    const FormManager = {
        currentStep: 1,
        totalSteps: 7,
        
        debug: function(message, data = null) {
            console.log(`🔧 FormManager: ${message}`, data || '');
        },
        
        showToast: function(message, type = 'info', duration = 5000) {
            let toastContainer = document.getElementById('toastContainer');
            
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                document.body.appendChild(toastContainer);
            }
            
            const toast = document.createElement('div');
            const bgClass = `alert alert-${type}`;
            toast.className = `${bgClass} alert-dismissible fade show`;
            toast.style.cssText = 'margin-bottom: 10px; animation: slideIn 0.3s ease-out;';
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const icon = icons[type] || 'fa-info-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            toastContainer.appendChild(toast);
            
            if (duration > 0) {
                setTimeout(() => {
                    toast.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        },
        
        validateRequired: function(element) {
            const requiredFields = element.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value || field.value.trim() === '') {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        },
        
        init: function() {
            this.debug('Initializing FormManager...');
            this.bindEvents();
            this.updateUI();
            this.initializeTables();
            this.updateProgressBar();
            this.debug('✅ Form Manager Initialized');
            this.setupDynamicRowObserver();
        },

        setupDynamicRowObserver: function() {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.addedNodes.length && this.currentStep === 5) {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === 1 && (node.classList.contains('executive-activity-action-row') || node.querySelector('.objective-result-select'))) {
                                this.populateExecutiveActivityDropdowns();
                            }
                        });
                    }
                });
            });

            const container = document.getElementById('executive-activities-container');
            if (container) {
                observer.observe(container, { childList: true, subtree: true });
            }
        },
        
        bindEvents: function() {
            this.debug('Binding events...');
            
            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const saveBtn = document.getElementById('saveBtn');
            
            this.debug('Navigation buttons found:', {
                nextBtn: !!nextBtn,
                prevBtn: !!prevBtn,
                saveBtn: !!saveBtn
            });
            
            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.debug('Next button clicked');
                    this.nextStep();
                });
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.debug('Previous button clicked');
                    this.prevStep();
                });
            }
            
            if (saveBtn) {
                saveBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.debug('Save draft button clicked');
                    this.saveDraft();
                });
            }
            
            document.querySelectorAll('.step-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const step = parseInt(e.currentTarget.dataset.step);
                    this.debug(`Step indicator clicked: ${step}`);
                    this.goToStep(step);
                });
            });
            
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        this.nextStep();
                    } else if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        this.prevStep();
                    }
                }
            });
            
            this.debug('✅ Events bound successfully');
        },
        
        nextStep: function() {
            if (this.validateCurrentStep()) {
                if (this.currentStep < this.totalSteps) {
                    this.autoDraftSave(() => {
                        this.currentStep++;
                        this.updateUI();
                        this.updateProgressBar();
                        this.scrollToTop();
                        this.animateStepTransition();
                    });
                } else {
                    this.submitForm();
                }
            }
        },
        
        prevStep: function() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.updateUI();
                this.updateProgressBar();
                this.scrollToTop();
                this.animateStepTransition();
            }
        },
        
        goToStep: function(step) {
            if (step >= 1 && step <= this.totalSteps) {
                this.autoDraftSave(() => {
                    this.currentStep = step;
                    this.updateUI();
                    this.updateProgressBar();
                    this.scrollToTop();
                    this.animateStepTransition();
                });
            }
        },
        
        updateUI: function() {
            document.querySelectorAll('.step').forEach((step, index) => {
                if (index + 1 === this.currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });
            
            document.querySelectorAll('.step-item').forEach((item, index) => {
                const stepNum = index + 1;
                item.classList.remove('active', 'completed');
                
                if (stepNum === this.currentStep) {
                    item.classList.add('active');
                } else if (stepNum < this.currentStep) {
                    item.classList.add('completed');
                }
            });
            
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const currentStepDisplay = document.getElementById('currentStepDisplay');
            
            if (prevBtn) {
                prevBtn.disabled = this.currentStep === 1;
            }
            
            if (nextBtn) {
                if (this.currentStep === this.totalSteps) {
                    nextBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>إرسال المشروع';
                    nextBtn.className = 'btn btn-success btn-navigation';
                } else {
                    nextBtn.innerHTML = 'التالي<i class="fas fa-arrow-left ms-2"></i>';
                    nextBtn.className = 'btn btn-primary btn-navigation';
                }
            }
            
            if (currentStepDisplay) {
                currentStepDisplay.textContent = this.currentStep;
            }
            
            console.log(`📍 Current Step: ${this.currentStep}`);
        },
        
        updateProgressBar: function() {
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                const progress = (this.currentStep / this.totalSteps) * 100;
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
            }
        },
        
        scrollToTop: function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        
        animateStepTransition: function() {
            const currentStep = document.querySelector(`.step[data-step="${this.currentStep}"]`);
            if (currentStep) {
                currentStep.style.animation = 'none';
                currentStep.offsetHeight;
                currentStep.style.animation = 'fadeIn 0.3s ease-in-out';
            }

            if (this.currentStep === 5) {
                this.populateExecutiveActivityDropdowns();
            }
        },

        populateExecutiveActivityDropdowns: function() {
            this.debug('Populating Executive Activity Dropdowns...');
            
            const results = [];
            document.querySelectorAll('input[name^="objective_results"][name$="[result_name]"]').forEach(input => {
                if (input.value.trim() !== '') {
                    results.push(input.value.trim());
                }
            });

            this.debug('Found Objective Results:', results);

            const dropdowns = document.querySelectorAll('.objective-result-select');
            dropdowns.forEach(select => {
                const currentValue = select.getAttribute('data-selected-value') || select.value;
                
                while (select.options.length > 1) {
                    select.remove(1);
                }

                results.forEach(result => {
                    const option = document.createElement('option');
                    option.value = result;
                    option.textContent = result;
                    if (currentValue === result) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            });
        },
        
        validateCurrentStep: function() {
            if (this.currentStep === 1) {
                const currentStepElement = document.querySelector(`.step[data-step="${this.currentStep}"]`);
                if (!currentStepElement) return true;
                
                const isValid = this.validateRequired(currentStepElement);
                
                if (!isValid) {
                    this.showToast('يرجى ملء الحقول المطلوبة: اسم المشروع والقوائم المنسدلة', 'error');
                    this.highlightInvalidFields(currentStepElement);
                }
                
                return isValid;
            }
            
            if (this.currentStep === 2) {
                return this.validateStep2();
            }
            
            if (this.currentStep === 3) {
                return this.validateStep3();
            }
            
            if (this.currentStep === 4) {
                return this.validatePreliminaryActivitiesWeights();
            }
            
            if (this.currentStep === 5) {
                return this.validateExecutiveActivitiesWeights();
            }
            
            return true;
        },
        
        validateStep2: function() {
            const locationsTable = document.querySelector('#projectLocationsTable tbody');
            if (!locationsTable) {
                this.showToast('خطأ: لم يتم العثور على جدول المواقع', 'error');
                return false;
            }
            
            const locationRows = locationsTable.querySelectorAll('tr:not(.project-empty-row)');
            if (locationRows.length === 0) {
                this.showToast('يرجى إضافة موقع واحد على الأقل للمشروع', 'error');
                return false;
            }
            
            return true;
        },
        
        validateStep3: function() {
            let errors = [];
            
            const mainObjectivesContainer = document.getElementById('main-objectives-container');
            if (mainObjectivesContainer) {
                const mainObjectiveRows = mainObjectivesContainer.querySelectorAll('.objective-row');
                if (mainObjectiveRows.length === 0) {
                    errors.push('يجب إضافة هدف رئيسي واحد على الأقل');
                }
            }
            
            const specialObjectivesContainer = document.getElementById('special-objectives-container');
            if (specialObjectivesContainer) {
                const specialObjectiveRows = specialObjectivesContainer.querySelectorAll('.special-objective-row');
                if (specialObjectiveRows.length === 0) {
                    errors.push('يجب إضافة هدف خاص واحد على الأقل');
                } else {
                    let hasObjectiveWithoutResults = false;
                    specialObjectiveRows.forEach((row, index) => {
                        const resultsContainer = row.querySelector('.results-container');
                        if (resultsContainer) {
                            const resultRows = resultsContainer.querySelectorAll('.result-row');
                            if (resultRows.length === 0) {
                                hasObjectiveWithoutResults = true;
                            }
                        }
                    });
                    if (hasObjectiveWithoutResults) {
                        errors.push('يجب أن يحتوي كل هدف خاص على نتيجة واحدة على الأقل');
                    }
                }
            }
            
            const risksTable = document.querySelector('#risks-table tbody');
            if (risksTable) {
                const riskRows = risksTable.querySelectorAll('tr:not(.empty-row)');
                if (riskRows.length === 0) {
                    errors.push('يجب إضافة مخاطرة واحدة على الأقل');
                }
            }
            
            const supervisingTable = document.querySelector('#supervisingEntitiesTable tbody');
            if (supervisingTable) {
                const supervisingRows = supervisingTable.querySelectorAll('tr:not(.project-empty-row)');
                if (supervisingRows.length === 0) {
                    errors.push('يجب إضافة جهة مشرفة واحدة على الأقل');
                }
            }
            
            const implementingTable = document.querySelector('#implementingEntitiesTable tbody');
            if (implementingTable) {
                const implementingRows = implementingTable.querySelectorAll('tr:not(.project-empty-row)');
                if (implementingRows.length === 0) {
                    errors.push('يجب إضافة جهة منفذة واحدة على الأقل');
                }
            }
            
            const financingTable = document.querySelector('#financing-table tbody');
            if (financingTable) {
                const financingRows = financingTable.querySelectorAll('tr:not(.empty-row)');
                if (financingRows.length === 0) {
                    errors.push('يجب إضافة مصدر تمويل واحد على الأقل');
                }
            }
            
            if (errors.length > 0) {
                this.showToast(errors.join(' | '), 'error');
                return false;
            }
            
            return true;
        },
        
        validatePreliminaryActivitiesWeights: function() {
            const weights = [];
            document.querySelectorAll('input[name^="preliminary_activities"][name$="[weight]"]').forEach(input => {
                const weight = parseFloat(input.value) || 0;
                weights.push(weight);
            });
            
            if (weights.length === 0) {
                this.showToast('يرجى إضافة أنشطة تمهيدية', 'error');
                return false;
            }
            
            const totalWeight = weights.reduce((a, b) => a + b, 0);
            if (Math.abs(totalWeight - 100) > 0.01) {
                this.showToast(`مجموع الأوزان يجب أن يساوي 100% (الحالي: ${totalWeight.toFixed(2)}%)`, 'error');
                return false;
            }
            
            return true;
        },
        
        validateExecutiveActivitiesWeights: function() {
            const weights = [];
            document.querySelectorAll('input[name^="executive_activities"][name$="[weight]"]').forEach(input => {
                const weight = parseFloat(input.value) || 0;
                weights.push(weight);
            });
            
            if (weights.length === 0) {
                this.showToast('يرجى إضافة أنشطة تنفيذية', 'error');
                return false;
            }
            
            const totalWeight = weights.reduce((a, b) => a + b, 0);
            if (Math.abs(totalWeight - 100) > 0.01) {
                this.showToast(`مجموع الأوزان يجب أن يساوي 100% (الحالي: ${totalWeight.toFixed(2)}%)`, 'error');
                return false;
            }
            
            return true;
        },
        
        highlightInvalidFields: function(element) {
            element.querySelectorAll('[required]').forEach(field => {
                if (!field.value || field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    field.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    });
                }
            });
        },
        
        autoDraftSave: function(callback) {
            this.debug('Auto-saving draft...');
            callback();
        },
        
        saveDraft: function() {
            this.debug('Saving draft...');
            const form = document.getElementById('projectForm');
            if (!form) return;
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showToast('تم حفظ المسودة بنجاح', 'success');
                } else {
                    this.showToast(data.message || 'فشل حفظ المسودة', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving draft:', error);
                this.showToast('خطأ في حفظ المسودة', 'error');
            });
        },
        
        submitForm: function() {
            this.debug('Submitting form...');
            const form = document.getElementById('projectForm');
            if (form) {
                form.submit();
            }
        },
        
        initializeTables: function() {
            this.debug('Initializing tables...');
        }
    };

    FormManager.init();
    window.FormManager = FormManager;
    
    if (document.readyState === 'loading' || !document.head.querySelector('style[data-toast-animations]')) {
        const style = document.createElement('style');
        style.setAttribute('data-toast-animations', 'true');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        if (document.head) {
            document.head.appendChild(style);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                document.head.appendChild(style);
            });
        }
    }
});
