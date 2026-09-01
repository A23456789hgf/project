/**
 * Project Wizard - Multi-step form handler
 * Handles navigation, validation, and dynamic content for project creation/editing
 */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize the wizard if it exists on the page
    const wizardForm = document.getElementById('projectWizardForm');
    if (wizardForm) {
        initProjectWizard(wizardForm);
    }

    /**
     * Initialize the project wizard
     * @param {HTMLElement} form - The wizard form element
     */
    function initProjectWizard(form) {
        // Get all steps in the form
        const steps = form.querySelectorAll('.step-content');
        const stepIndicators = form.querySelectorAll('.step-progress .step');
        const totalSteps = steps.length;
        let currentStep = 1;

        // Get navigation buttons
        const prevBtn = form.querySelector('.btn-prev');
        const nextBtn = form.querySelector('.btn-next');
        const submitBtn = form.querySelector('.btn-submit');
        const saveDraftBtn = form.querySelector('.btn-save-draft');

        // Initialize dynamic sections
        initDynamicSections();

        // Show the first step by default (already done in HTML with 'active' class)
        updateNavButtons();

        // Add event listeners to navigation buttons
        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                navigateStep(-1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                navigateStep(1);
            });
        }

        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', function () {
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
                this.disabled = true;

                saveStepData(currentStep)
                    .then(() => {
                        if (window.$toast) {
                            window.$toast.success('تم حفظ مسودة المشروع بنجاح!');
                        }
                    })
                    .catch(error => {
                        if (window.$toast) {
                            window.$toast.error('حدث خطأ أثناء حفظ المسودة: ' + error.message);
                        } else {
                            alert('حدث خطأ: ' + error.message);
                        }
                    })
                    .finally(() => {
                        this.innerHTML = originalHtml;
                        this.disabled = false;
                    });
            });
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                if (validateStep(currentStep)) {
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
                    this.disabled = true;

                    // Submit the form
                    form.submit();
                }
            });
        }

        // Change-based debounced autosave strategy (Concern #2)
        let isDirty = false;

        // Simple debounce helper
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Debounced save execution
        const debouncedAutoSave = debounce(function () {
            if (!isDirty || document.body.classList.contains('loading')) return;

            saveStepData(currentStep, true)
                .then(data => {
                    isDirty = false;
                    console.log('Change-based draft autosaved successfully.');
                })
                .catch(err => {
                    console.warn('Change-based autosave failed:', err.message);
                });
        }, 3000); // Trigger save 3 seconds after the last change/keystroke

        // Listen for user input & changes inside the form to mark dirty and schedule save
        form.addEventListener('input', function () {
            isDirty = true;
            debouncedAutoSave();
        });

        form.addEventListener('change', function () {
            isDirty = true;
            debouncedAutoSave();
        });

        /**
         * Navigate to the next or previous step
         * @param {number} direction - Direction to navigate (1 for next, -1 for previous)
         */
        function navigateStep(direction) {
            // Validate current step if moving forward
            if (direction > 0 && !validateStep(currentStep)) {
                return;
            }

            // Transition immediately to keep UI snappy
            const prevStep = currentStep;
            hideStep(currentStep);
            currentStep += direction;
            showStep(currentStep);
            updateNavButtons();

            // Avoid smooth scrolling to reduce perceived lag
            if (typeof form.scrollIntoView === 'function') {
                form.scrollIntoView({ behavior: 'auto', block: 'start' });
            }

            // Save in background (non-blocking, silent)
            saveStepData(prevStep, true).catch(error => {
                if (window.$toast) {
                    window.$toast.error('حدث خطأ أثناء حفظ البيانات: ' + error.message);
                } else {
                    console.error('Save draft failed:', error);
                }
            });
        }

        /**
         * Save step data to server as draft
         * @param {number} stepNumber - The step number to save
         * @param {boolean} isSilent - Whether to skip showing the success toast
         * @returns {Promise} - Promise that resolves when data is saved
         */
        function saveStepData(stepNumber, isSilent = false) {
            // Get the project ID if it exists (for updates)
            const projectId = form.getAttribute('data-project-id') || '';

            // Get the CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Get all form inputs in the current step
            const step = form.querySelector(`.step-content[data-step="${stepNumber}"]`);
            if (!step) return Promise.resolve();

            // Create FormData object
            const formData = new FormData();

            // Add step number
            formData.append('step', stepNumber);

            // Add status as draft
            formData.append('status', 'draft');

            // Add all inputs from the current step
            const inputs = step.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                // Skip buttons and hidden inputs except for specific ones
                if (input.type === 'button' || input.type === 'submit') return;

                // Handle checkboxes and radio buttons
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked) {
                        formData.append(input.name || input.id, input.value);
                    }
                }
                // Handle select-multiple
                else if (input.tagName === 'SELECT' && input.multiple) {
                    const selectedOptions = Array.from(input.selectedOptions);
                    selectedOptions.forEach(option => {
                        formData.append(input.name || input.id, option.value);
                    });
                }
                // Handle all other input types
                else {
                    formData.append(input.name || input.id, input.value);
                }
            });

            // Determine the endpoint based on whether we're creating or updating
            const endpoint = projectId
                ? `/api/projects/${projectId}/save-draft`
                : '/api/projects/save-draft';

            // Send data to server
            return fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'حدث خطأ أثناء حفظ البيانات');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // If this is a new project, update the form with the project ID
                    if (data.project && data.project.id && !projectId) {
                        form.setAttribute('data-project-id', data.project.id);

                        // Update form action if needed
                        const formAction = form.getAttribute('action');
                        if (formAction && formAction.includes('/store')) {
                            form.setAttribute('action', formAction.replace('/store', `/${data.project.id}/update`));
                        }
                    }

                    // Show success message only if not silent
                    if (!isSilent && window.$toast) {
                        window.$toast.success('تم حفظ البيانات كمسودة');
                    }

                    return data;
                });
        }

        /**
         * Show a specific step
         * @param {number} stepNumber - The step number to show
         */
        function showStep(stepNumber) {
            // Find the step element
            const step = form.querySelector(`.step-content[data-step="${stepNumber}"]`);
            if (step) {
                step.classList.add('active');
            }

            // Update step indicator
            if (stepIndicators.length >= stepNumber) {
                // Mark current step as active
                stepIndicators[stepNumber - 1].classList.add('active');

                // Mark previous steps as completed
                for (let i = 0; i < stepNumber - 1; i++) {
                    stepIndicators[i].classList.add('completed');
                }
            }
        }

        /**
         * Hide a specific step
         * @param {number} stepNumber - The step number to hide
         */
        function hideStep(stepNumber) {
            // Find the step element
            const step = form.querySelector(`.step-content[data-step="${stepNumber}"]`);
            if (step) {
                step.classList.remove('active');
            }
        }

        /**
         * Update navigation buttons based on current step
         */
        function updateNavButtons() {
            // Show/hide previous button
            if (prevBtn) {
                prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
            }

            // Show/hide next and submit buttons
            if (nextBtn && submitBtn) {
                if (currentStep < totalSteps) {
                    nextBtn.style.display = 'block';
                    submitBtn.style.display = 'none';
                } else {
                    nextBtn.style.display = 'none';
                    submitBtn.style.display = 'block';
                }
            }
        }

        /**
         * Validate a specific step
         * @param {number} stepNumber - The step number to validate
         * @returns {boolean} - Whether the step is valid
         */
        function validateStep(stepNumber) {
            const step = form.querySelector(`.step-content[data-step="${stepNumber}"]`);
            if (!step) return true;

            // Get all required inputs in the current step
            const requiredInputs = step.querySelectorAll('.wizard-input[required], .wizard-input[data-required="true"]');
            let isValid = true;

            // Reset all error messages first
            const errorMessages = step.querySelectorAll('.invalid-feedback');
            errorMessages.forEach(msg => {
                msg.textContent = '';
                msg.style.display = 'none';
            });

            // Check each required input
            requiredInputs.forEach(input => {
                // Reset validation state
                input.classList.remove('is-invalid');
                const fieldId = input.id || input.getAttribute('data-field');
                const errorElement = document.getElementById(`${fieldId}-error`);

                // Validate based on input type
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    if (errorElement) {
                        errorElement.textContent = 'هذا الحقل مطلوب';
                        errorElement.style.display = 'block';
                    }
                } else if (input.type === 'email' && !validateEmail(input.value)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    if (errorElement) {
                        errorElement.textContent = 'يرجى إدخال بريد إلكتروني صحيح';
                        errorElement.style.display = 'block';
                    }
                } else if (input.type === 'number' && input.min && parseFloat(input.value) < parseFloat(input.min)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    if (errorElement) {
                        errorElement.textContent = `يجب أن تكون القيمة أكبر من أو تساوي ${input.min}`;
                        errorElement.style.display = 'block';
                    }
                }
            });

            return isValid;
        }

        /**
         * Validate email format
         * @param {string} email - The email to validate
         * @returns {boolean} - Whether the email is valid
         */
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        /**
         * Initialize dynamic sections (add/remove functionality)
         */
        function initDynamicSections() {
            // Add item buttons
            const addButtons = form.querySelectorAll('.add-item-btn');
            addButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const sectionId = this.getAttribute('data-section');
                    const container = document.getElementById(`${sectionId}-container`);
                    const template = document.getElementById(`${sectionId}-template`);

                    if (container && template) {
                        // Clone the template
                        const newItem = template.cloneNode(true);
                        newItem.classList.remove('d-none');
                        newItem.removeAttribute('id');

                        // Update indices in the new item
                        const itemCount = container.querySelectorAll('.dynamic-item:not(.d-none)').length;
                        updateIndices(newItem, itemCount);

                        // Add the new item to the container
                        container.appendChild(newItem);

                        // Initialize remove button
                        const removeBtn = newItem.querySelector('.remove-item-btn');
                        if (removeBtn) {
                            initRemoveButton(removeBtn);
                        }

                        // Initialize any select2 and unit selects
                        if (window.initializeApiUnitSelects) {
                            window.initializeApiUnitSelects(newItem);
                        } else if (window.initializeSearchableSelects) {
                            window.initializeSearchableSelects(newItem);
                        }
                    }
                });
            });

            // Initialize existing remove buttons
            const removeButtons = form.querySelectorAll('.remove-item-btn');
            removeButtons.forEach(button => {
                initRemoveButton(button);
            });

            /**
             * Initialize a remove button
             * @param {HTMLElement} button - The remove button
             */
            function initRemoveButton(button) {
                button.addEventListener('click', function () {
                    const item = this.closest('.dynamic-item');
                    if (item) {
                        // Ask for confirmation
                        Swal.fire({
                            title: 'تأكيد الحذف',
                            text: 'هل أنت متأكد من حذف هذا العنصر؟',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'نعم',
                            cancelButtonText: 'لا',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                item.remove();
                            }
                        });
                    }
                });
            }

            /**
             * Update indices in a dynamic item
             * @param {HTMLElement} item - The dynamic item
             * @param {number} index - The new index
             */
            function updateIndices(item, index) {
                const inputs = item.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
                    }
                    if (input.id) {
                        const newId = input.id.replace(/\-\d+$/, `-${index}`);
                        input.id = newId;

                        // Update associated labels
                        const labels = item.querySelectorAll(`label[for="${input.id}"]`);
                        labels.forEach(label => {
                            label.setAttribute('for', newId);
                        });
                    }
                });
            }
        }
    }
});