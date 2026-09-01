/**
 * Multi-Step Form Handler
 * Handles navigation and validation for multi-step forms
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get all multi-step forms on the page
    const multistepForms = document.querySelectorAll('.multi-step-form');
    
    // Initialize each form
    multistepForms.forEach(form => {
        initMultiStepForm(form);
    });
    
    /**
     * Initialize a multi-step form
     * @param {HTMLElement} form - The form element
     */
    function initMultiStepForm(form) {
        // Get all steps in the form and cache them
        const steps = Array.from(form.querySelectorAll('.step-content'));
        const stepIndicators = Array.from(form.querySelectorAll('.step-progress .step'));
        const totalSteps = steps.length;
        let currentStep = 1;
        
        // Map steps by their step number for ultra-fast access
        const stepsMap = {};
        steps.forEach(step => {
            const stepNum = step.getAttribute('data-step');
            if (stepNum) {
                stepsMap[stepNum.trim()] = step;
            }
        });
        
        // Get navigation buttons
        const prevBtn = form.querySelector('.btn-step[data-action="prev"]');
        const nextBtn = form.querySelector('.btn-step[data-action="next"]');
        const submitBtn = form.querySelector('.btn-step[data-action="submit"]');
        
        // Initialize dynamic item functionality
        initDynamicItems(form);
        
        // Show the first step by default
        showStep(currentStep);
        
        // Update navigation buttons for the initial step
        updateNavButtons();
        
        // Add event listeners to navigation buttons
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                navigateStep(-1);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                navigateStep(1);
            });
        }
        
        if (submitBtn) {
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (validateStep(currentStep)) {
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';
                    this.disabled = true;
                    
                    // Submit the form
                    form.submit();
                }
            });
        }
        
        /**
         * Navigate to the next or previous step
         * @param {number} direction - Direction to navigate (1 for next, -1 for previous)
         */
        function navigateStep(direction) {
            // Validate current step if moving forward
            if (direction > 0 && !validateStep(currentStep)) {
                return;
            }
            
            // Hide current step
            hideStep(currentStep);
            
            // Update current step
            currentStep += direction;
            
            // Show new step
            showStep(currentStep);
            
            // Update navigation buttons
            updateNavButtons();
            
            // Scroll to top of form for better UX on long steps
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Show the specific step and hide others
        function showStep(stepNumber) {
            // Hide all steps first
            steps.forEach(step => {
                step.classList.remove('active');
            });
            
            // Show the current step
            const step = stepsMap[stepNumber];
            if (step) {
                step.classList.add('active');
            }
            
            // Update step indicators
            stepIndicators.forEach((indicator, index) => {
                const indicatorStep = index + 1;
                indicator.classList.remove('active', 'completed');
                
                if (indicatorStep === stepNumber) {
                    indicator.classList.add('active');
                } else if (indicatorStep < stepNumber) {
                    indicator.classList.add('completed');
                }
            });
        }
        
        /**
         * Navigation buttons already update visibility, so hideStep is just for content
         * but showStep now handles everything. We'll keep navigateStep logic clean.
         */
        function hideStep(stepNumber) {
            const step = stepsMap[stepNumber];
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
            if (nextBtn) {
                // Show next button if not the last step
                nextBtn.style.display = currentStep < totalSteps ? 'block' : 'none';
            }

            // Show submit button only on the last step
            if (submitBtn) {
                submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';
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
            const requiredInputs = step.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            // Check each required input
            requiredInputs.forEach(input => {
                // Reset validation state
                input.classList.remove('is-invalid');
                const feedbackElement = input.nextElementSibling?.classList.contains('invalid-feedback') 
                    ? input.nextElementSibling 
                    : input.parentElement.querySelector('.invalid-feedback');
                
                // Validate based on input type
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    if (feedbackElement) {
                        feedbackElement.textContent = 'هذا الحقل مطلوب';
                    }
                } else if (input.type === 'email' && !validateEmail(input.value)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    if (feedbackElement) {
                        feedbackElement.textContent = 'يرجى إدخال بريد إلكتروني صحيح';
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
    }
    
    /**
     * Initialize dynamic items functionality (add/remove items)
     * @param {HTMLElement} form - The form element
     */
    function initDynamicItems(form) {
        // Add item buttons
        const addButtons = form.querySelectorAll('.add-item');
        addButtons.forEach(button => {
            button.addEventListener('click', function() {
                const section = this.closest('.dynamic-section');
                const container = section.querySelector('.dynamic-container');
                const items = container.querySelectorAll('.dynamic-item');
                const lastItem = items[items.length - 1];
                
                // Clone the last item
                const newItem = lastItem.cloneNode(true);
                
                // Update input names and clear values
                const inputs = newItem.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    // Update index in name attribute
                    if (input.name) {
                        input.name = input.name.replace(/\[\d+\]/, `[${items.length}]`);
                    }
                    
                    // Clear value
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                    
                    // Remove validation classes
                    input.classList.remove('is-invalid', 'is-valid');
                });
                
                // Show remove button
                const removeBtn = newItem.querySelector('.remove-item');
                if (removeBtn) {
                    removeBtn.style.display = 'block';
                }
                
                // Add the new item to the container
                container.appendChild(newItem);
                
                // Initialize remove button
                initRemoveButton(removeBtn);
            });
        });
        
        // Initialize existing remove buttons
        const removeButtons = form.querySelectorAll('.remove-item');
        removeButtons.forEach(button => {
            initRemoveButton(button);
        });
        
        /**
         * Initialize a remove button
         * @param {HTMLElement} button - The remove button
         */
        function initRemoveButton(button) {
            if (!button) return;
            
            button.addEventListener('click', function() {
                const item = this.closest('.dynamic-item');
                const container = item.parentElement;
                
                // Don't remove if it's the only item
                if (container.querySelectorAll('.dynamic-item').length > 1) {
                    item.remove();
                }
            });
        }
    }
});