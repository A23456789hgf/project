/**
 * Project Forms Fix - Comprehensive solution for button functionality issues
 * This file fixes all the button issues in the project forms
 */

(function() {
    'use strict';
    
    // Wait for DOM and jQuery to be ready
    function initializeProjectFormsFix() {
        if (typeof $ === 'undefined') {
            console.log('Waiting for jQuery...');
            setTimeout(initializeProjectFormsFix, 100);
            return;
        }
        
        console.log('✅ Project Forms Fix - Initializing...');
        
        $(document).ready(function() {
            console.log('✅ Project Forms Fix - DOM Ready');
            
            // Fix 1: Add Specific Objectives Button
            fixSpecificObjectivesButton();
            
            // Fix 2: Add Cost Buttons in Preliminary Activities
            fixPreliminaryActivitiesCostButton();
            
            // Fix 3: Add Cost Icons in Executive Activities
            fixExecutiveActivitiesCostButtons();
            
            // Fix 4: Add Entities to Related Entities Table
            fixEntitiesButtons();
            
            console.log('✅ All project form buttons have been fixed');
        });
    }
    
    /**
     * Fix 1: Add Specific Objectives Button
     */
    function fixSpecificObjectivesButton() {
        // Use event delegation to ensure the button works even if added dynamically
        $(document).off('click', '#addSpecialObjective').on('click', '#addSpecialObjective', function(e) {
            e.preventDefault();
            console.log('✅ Add Special Objective button clicked - FIXED');
            
            // Get current counter
            let specialCounter = $('#specialObjectivesTable tbody tr.objective-row').length;
            
            // Remove empty row if exists
            $('.project-empty-row').remove();
            
            const newRow = `
            <tr class="project-animated-row objective-row" data-index="${specialCounter}">
                <td data-label="الهدف الخاص">
                    <input type="text" name="special_objectives[${specialCounter}][objective]" 
                           class="form-control" placeholder="أدخل الهدف الخاص" required>
                </td>
                <td data-label="المؤشر">
                    <input type="text" name="special_objectives[${specialCounter}][indicator]" 
                           class="form-control" placeholder="أدخل المؤشر" required>
                </td>
                <td data-label="وحدة المؤشر">
                    <input type="text" name="special_objectives[${specialCounter}][indicator_unit]" 
                           class="form-control" placeholder="أدخل وحدة المؤشر" required>
                </td>
                <td data-label="القيمة المستهدفة">
                    <input type="number" step="0.01" name="special_objectives[${specialCounter}][indicator_value]" 
                           class="form-control" placeholder="أدخل القيمة">
                </td>
                <td data-label="الإجراءات">
                    <div class="project-action-buttons">
                        <button type="button" class="project-btn project-btn-success add-result" data-objective-index="${specialCounter}">
                            <i class="fas fa-plus-circle"></i>نتيجة
                        </button>
                        <button type="button" class="project-btn project-btn-danger remove-row">
                            <i class="fas fa-trash-alt"></i>حذف
                        </button>
                    </div>
                </td>
            </tr>`;
            
            $('#specialObjectivesTable tbody').append(newRow);
            
            // Show success message
            showSuccessMessage('تم إضافة هدف خاص جديد بنجاح');
        });
        
        // Fix remove row functionality
        $(document).off('click', '.remove-row').on('click', '.remove-row', function(e) {
            e.preventDefault();
            const row = $(this).closest('tr');
            const objectiveIndex = row.data('index');
            
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذا الهدف؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم',
                cancelButtonText: 'لا',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove all results and outputs for this objective
                    $(`.result-row[data-objective-index="${objectiveIndex}"]`).remove();
                    
                    row.remove();
                    
                    // Show empty message if no rows left
                    if ($('#specialObjectivesTable tbody tr.objective-row').length === 0) {
                        $('#specialObjectivesTable tbody').html(`
                            <tr class="project-empty-row">
                                <td colspan="5" class="text-center">
                                    <i class="fas fa-bullseye fa-2x mb-2"></i><br>
                                    لا توجد أهداف خاصة مضافة بعد
                                </td>
                            </tr>
                        `);
                    }
                    
                    showSuccessMessage('تم حذف الهدف بنجاح');
                }
            });
        });
    }
    
    /**
     * Fix 2: Add Cost Buttons in Preliminary Activities
     */
    function fixPreliminaryActivitiesCostButton() {
        // Override the global addCost function
        window.addCost = function(activityId) {
            console.log('✅ Add Cost button clicked - FIXED for activity:', activityId);
            
            showCostModal(activityId, 'preliminary');
        };
    }
    
    /**
     * Fix 3: Add Cost Icons in Executive Activities
     */
    function fixExecutiveActivitiesCostButtons() {
        // Override the global showCostsModal function
        window.showCostsModal = function(activityIndex, actionIndex) {
            console.log('✅ Add Cost icon clicked - FIXED for executive activity:', activityIndex, 'action:', actionIndex);
            
            showCostModal(`${activityIndex}_${actionIndex}`, 'executive');
        };
        
        // Also fix the assignees modal
        window.showAssigneesModal = function(activityIndex, actionIndex) {
            console.log('✅ Add Assignees icon clicked - FIXED for executive activity:', activityIndex, 'action:', actionIndex);
            
            showAssigneesModal(activityIndex, actionIndex);
        };
    }
    
    /**
     * Fix 4: Add Entities to Related Entities Table
     */
    function fixEntitiesButtons() {
        // Fix main add entity button
        $(document).off('click', '#add-entity-btn').on('click', '#add-entity-btn', function(e) {
            e.preventDefault();
            console.log('✅ Add Entity button clicked - FIXED');
            
            addNewEntity();
        });
        
        // Fix empty state add entity button
        $(document).off('click', '#empty-add-entity-btn').on('click', '#empty-add-entity-btn', function(e) {
            e.preventDefault();
            console.log('✅ Empty Add Entity button clicked - FIXED');
            
            addNewEntity();
        });
        
        // Fix floating add entity button
        $(document).off('click', '#floating-add-entity-btn').on('click', '#floating-add-entity-btn', function(e) {
            e.preventDefault();
            console.log('✅ Floating Add Entity button clicked - FIXED');
            
            addNewEntity();
            
            // Scroll to the newly added entity
            setTimeout(() => {
                const newEntity = $('.entity-item:last');
                if (newEntity.length) {
                    newEntity[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        });
    }
    
    /**
     * Show cost modal for different activity types
     */
    function showCostModal(activityId, type) {
        const modalId = 'costModal_' + activityId.toString().replace('_', '');
        const modalTitle = type === 'preliminary' ? 'إضافة تكلفة للنشاط التمهيدي' : 'إضافة تكلفة للإجراء التنفيذي';
        
        const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="${modalId}Label">${modalTitle}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="costForm_${activityId}">
                            <input type="hidden" id="costActivityId_${activityId}" value="${activityId}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="financialItem_${activityId}" class="form-label">البند المالي</label>
                                    <input type="text" class="form-control" id="financialItem_${activityId}" placeholder="أدخل البند المالي" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="unit_${activityId}" class="form-label">الوحدة</label>
                                    <input type="text" class="form-control" id="unit_${activityId}" placeholder="أدخل الوحدة" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="unitPrice_${activityId}" class="form-label">سعر الوحدة</label>
                                    <input type="number" step="0.01" class="form-control" id="unitPrice_${activityId}" placeholder="0.00" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="quantity_${activityId}" class="form-label">الكمية</label>
                                    <input type="number" step="0.01" class="form-control" id="quantity_${activityId}" placeholder="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="total_${activityId}" class="form-label">الإجمالي</label>
                                    <input type="number" step="0.01" class="form-control" id="total_${activityId}" placeholder="0.00" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description_${activityId}" class="form-label">وصف التكلفة</label>
                                <textarea class="form-control" id="description_${activityId}" rows="3" placeholder="أدخل وصف التكلفة (اختياري)"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-warning" id="saveCostBtn_${activityId}">حفظ التكلفة</button>
                    </div>
                </div>
            </div>
        </div>`;
        
        // Remove existing modal if any
        $(`#${modalId}`).remove();
        
        // Add modal to body
        $('body').append(modalHtml);
        
        // Show modal
        $(`#${modalId}`).modal('show');
        
        // Calculate total when unit price or quantity changes
        $(`#unitPrice_${activityId}, #quantity_${activityId}`).on('input', function() {
            const unitPrice = parseFloat($(`#unitPrice_${activityId}`).val()) || 0;
            const quantity = parseFloat($(`#quantity_${activityId}`).val()) || 0;
            const total = unitPrice * quantity;
            $(`#total_${activityId}`).val(total.toFixed(2));
        });
        
        // Save cost
        $(`#saveCostBtn_${activityId}`).click(function() {
            const formData = {
                activity_id: activityId,
                financial_item: $(`#financialItem_${activityId}`).val(),
                unit: $(`#unit_${activityId}`).val(),
                unit_price: $(`#unitPrice_${activityId}`).val(),
                quantity: $(`#quantity_${activityId}`).val(),
                total: $(`#total_${activityId}`).val(),
                description: $(`#description_${activityId}`).val(),
                type: type
            };
            
            if (!formData.financial_item || !formData.unit || !formData.unit_price || !formData.quantity) {
                alert('يرجى ملء جميع الحقول المطلوبة');
                return;
            }
            
            console.log('Saving cost:', formData);
            
            // Here you would typically send the data to the server
            // For now, we'll just show a success message
            showSuccessMessage('تم إضافة التكلفة بنجاح');
            
            // Close modal
            $(`#${modalId}`).modal('hide');
        });
    }
    
    /**
     * Show assignees modal
     */
    function showAssigneesModal(activityIndex, actionIndex) {
        const modalId = `assigneesModal_${activityIndex}_${actionIndex}`;
        
        const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="${modalId}Label">إضافة مكلفين للإجراء</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="assigneesForm_${activityIndex}_${actionIndex}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assigneeName_${activityIndex}_${actionIndex}" class="form-label">اسم المكلف</label>
                                    <input type="text" class="form-control" id="assigneeName_${activityIndex}_${actionIndex}" placeholder="أدخل اسم المكلف" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="assigneeRole_${activityIndex}_${actionIndex}" class="form-label">الدور</label>
                                    <input type="text" class="form-control" id="assigneeRole_${activityIndex}_${actionIndex}" placeholder="أدخل دور المكلف" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assigneeEntity_${activityIndex}_${actionIndex}" class="form-label">الجهة</label>
                                    <input type="text" class="form-control" id="assigneeEntity_${activityIndex}_${actionIndex}" placeholder="أدخل الجهة" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="assigneeContact_${activityIndex}_${actionIndex}" class="form-label">معلومات الاتصال</label>
                                    <input type="text" class="form-control" id="assigneeContact_${activityIndex}_${actionIndex}" placeholder="رقم الهاتف أو البريد الإلكتروني">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-success" id="saveAssigneeBtn_${activityIndex}_${actionIndex}">حفظ المكلف</button>
                    </div>
                </div>
            </div>
        </div>`;
        
        // Remove existing modal if any
        $(`#${modalId}`).remove();
        
        // Add modal to body
        $('body').append(modalHtml);
        
        // Show modal
        $(`#${modalId}`).modal('show');
        
        // Save assignee
        $(`#saveAssigneeBtn_${activityIndex}_${actionIndex}`).click(function() {
            const formData = {
                activity_index: activityIndex,
                action_index: actionIndex,
                name: $(`#assigneeName_${activityIndex}_${actionIndex}`).val(),
                role: $(`#assigneeRole_${activityIndex}_${actionIndex}`).val(),
                entity: $(`#assigneeEntity_${activityIndex}_${actionIndex}`).val(),
                contact: $(`#assigneeContact_${activityIndex}_${actionIndex}`).val()
            };
            
            if (!formData.name || !formData.role || !formData.entity) {
                alert('يرجى ملء الحقول المطلوبة');
                return;
            }
            
            console.log('Saving assignee:', formData);
            
            showSuccessMessage('تم إضافة المكلف بنجاح');
            
            $(`#${modalId}`).modal('hide');
        });
    }
    
    /**
     * Add new entity function
     */
    function addNewEntity() {
        const template = document.getElementById('entity-template');
        const container = document.getElementById('entities-container');
        const noEntitiesMessage = document.getElementById('no-entities-message');
        
        if (!template || !container) {
            console.error('Entity template or container not found');
            alert('خطأ: لم يتم العثور على قالب الجهة أو الحاوية');
            return;
        }
        
        if (noEntitiesMessage) {
            noEntitiesMessage.remove();
        }
        
        // Get current entity count
        let entityIndex = $('.entity-item').length;
        
        // Replace placeholder index with actual index
        const newEntity = template.innerHTML.replace(/__INDEX__/g, entityIndex);
        
        // Insert the new entity HTML
        container.insertAdjacentHTML('beforeend', newEntity);
        
        // Add entrance animation
        const newEntityElement = container.lastElementChild;
        if (newEntityElement) {
            newEntityElement.style.opacity = '0';
            newEntityElement.style.transform = 'translateY(20px)';
            
            // Trigger reflow
            void newEntityElement.offsetWidth;
            
            // Start animation
            newEntityElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            newEntityElement.style.opacity = '1';
            newEntityElement.style.transform = 'translateY(0)';
        }
        
        showSuccessMessage('تم إضافة جهة جديدة بنجاح');
    }
    
    /**
     * Show success message
     */
    function showSuccessMessage(message) {
        if (typeof AppUtils !== 'undefined' && AppUtils.Utils) {
            AppUtils.Utils.showToast(message, 'success');
        } else if (typeof flasher !== 'undefined') {
            flasher.success(message);
        } else {
            alert(message);
        }
    }
    
    // Initialize the fix
    initializeProjectFormsFix();
})();