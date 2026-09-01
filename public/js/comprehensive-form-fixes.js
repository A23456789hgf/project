/**
 * Comprehensive Form Fixes for Project Management System
 * Handles:
 * - Hijri date conversion for procedures and actions
 * - Financial cost calculations and totals
 * - Activity/procedure/action numbering and weights
 * - Dynamic row initialization
 */

(function () {
    'use strict';

    // ===================== UNIFIED DATE CONVERSION =====================

    /**
     * Convert Gregorian date to Hijri date
     * Uses Intl API for accuracy
     */
    window.gregorianToHijri = function (gregorianDate) {
        if (!gregorianDate) return '';

        // Use HijriConverter if available
        if (typeof HijriConverter !== 'undefined') {
            return HijriConverter.gregorianToHijri(gregorianDate);
        }

        try {
            const date = new Date(gregorianDate);
            const hijriDate = new Intl.DateTimeFormat('ar-SA', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                calendar: 'islamic-civil'
            }).format(date);

            // Convert to object format to maintain compatibility with other scripts
            const parts = hijriDate.replace(/\u200E/g, '').replace(/هـ/g, '').trim()
                .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d)).split('/');

            // Intl ar-SA usually returns dd/mm/yyyy
            return {
                day: parseInt(parts[0]),
                month: parseInt(parts[1]),
                year: parseInt(parts[2])
            };
        } catch (error) {
            console.error("خطأ في تحويل التاريخ:", error);
            return '';
        }
    };

    /**
     * Calculate duration in days between two dates
     */
    window.calculateDurationDays = function (startDate, endDate) {
        if (!startDate || !endDate) return '';

        try {
            const start = new Date(startDate + 'T00:00:00Z');
            const end = new Date(endDate + 'T00:00:00Z');
            const diffTime = end.getTime() - start.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

            return diffDays >= 1 ? diffDays : '';
        } catch (error) {
            console.error("خطأ في حساب المدة:", error);
            return '';
        }
    };

    // ===================== PROCEDURE ROW INITIALIZATION =====================

    /**
     * Initialize date listeners and calculations for a procedure row
     */
    window.initializeProcedureRowDates = function (row) {
        if (!row || row.dataset.datesInitialized) return;

        const startDateInput = row.querySelector('.start-date');
        const startHijriInput = row.querySelector('.start-date-hijri');
        const endDateInput = row.querySelector('.end-date');
        const endHijriInput = row.querySelector('.end-date-hijri');
        const durationInput = row.querySelector('.duration-days');

        function updateDates() {
            if (startDateInput && startHijriInput) {
                startHijriInput.value = window.gregorianToHijri(startDateInput.value);
            }
            if (endDateInput && endHijriInput) {
                endHijriInput.value = window.gregorianToHijri(endDateInput.value);
            }
            if (startDateInput && endDateInput && durationInput) {
                durationInput.value = window.calculateDurationDays(startDateInput.value, endDateInput.value);
            }
        }

        if (startDateInput) {
            startDateInput.addEventListener('input', updateDates);
            startDateInput.addEventListener('change', updateDates);
        }
        if (endDateInput) {
            endDateInput.addEventListener('input', updateDates);
            endDateInput.addEventListener('change', updateDates);
        }

        // Initial calculation
        updateDates();
        row.dataset.datesInitialized = 'true';
    };

    // ===================== ACTION ROW INITIALIZATION =====================

    /**
     * Initialize date listeners and calculations for an executive action row
     */
    window.initializeActionRowDates = function (row) {
        if (!row || row.dataset.datesInitialized) return;

        const startDateInput = row.querySelector('.executive-start-date');
        const startHijriInput = row.querySelector('.executive-start-date-hijri');
        const endDateInput = row.querySelector('.executive-end-date');
        const endHijriInput = row.querySelector('.executive-end-date-hijri');
        const durationInput = row.querySelector('.executive-duration');

        function updateDates() {
            if (startDateInput && startHijriInput) {
                startHijriInput.value = window.gregorianToHijri(startDateInput.value);
            }
            if (endDateInput && endHijriInput) {
                endHijriInput.value = window.gregorianToHijri(endDateInput.value);
            }
            if (startDateInput && endDateInput && durationInput) {
                durationInput.value = window.calculateDurationDays(startDateInput.value, endDateInput.value);
            }
        }

        if (startDateInput) {
            startDateInput.addEventListener('input', updateDates);
            startDateInput.addEventListener('change', updateDates);
        }
        if (endDateInput) {
            endDateInput.addEventListener('input', updateDates);
            endDateInput.addEventListener('change', updateDates);
        }

        // Initial calculation
        updateDates();
        row.dataset.datesInitialized = 'true';
    };

    // ===================== COST ROW INITIALIZATION =====================

    /**
     * Initialize cost calculation for a cost row (preliminary activities)
     */
    window.initializeCostRowCalculation = function (costRow, callbacks) {
        if (!costRow || costRow.dataset.costInitialized) return;

        const amountInput = costRow.querySelector('.amount-input');
        const quantityInput = costRow.querySelector('.quantity-input');
        const totalInput = costRow.querySelector('.total-input');

        if (!amountInput || !quantityInput || !totalInput) return;

        function calculateTotal() {
            const amount = parseFloat(amountInput.value) || 0;
            const quantity = parseFloat(quantityInput.value) || 0;
            const total = amount * quantity;
            totalInput.value = total.toFixed(2);
            totalInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Call callback if provided
            if (callbacks && typeof callbacks.onTotalChange === 'function') {
                callbacks.onTotalChange(totalInput);
            }
        }

        amountInput.addEventListener('input', calculateTotal);
        quantityInput.addEventListener('input', calculateTotal);

        calculateTotal();
        costRow.dataset.costInitialized = 'true';
    };

    /**
     * Initialize cost calculation for executive action cost rows
     */
    window.initializeExecutiveCostRowCalculation = function (costRow, callbacks) {
        if (!costRow || costRow.dataset.costInitialized) return;

        const amountInput = costRow.querySelector('.executive-cost-amount');
        const quantityInput = costRow.querySelector('.executive-cost-quantity');
        const totalInput = costRow.querySelector('.executive-cost-total');

        if (!amountInput || !quantityInput || !totalInput) return;

        function calculateTotal() {
            const amount = parseFloat(amountInput.value) || 0;
            const quantity = parseFloat(quantityInput.value) || 0;
            const total = amount * quantity;
            totalInput.value = total.toFixed(2);
            totalInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Call callback if provided
            if (callbacks && typeof callbacks.onTotalChange === 'function') {
                callbacks.onTotalChange(totalInput);
            }
        }

        amountInput.addEventListener('input', calculateTotal);
        quantityInput.addEventListener('input', calculateTotal);

        calculateTotal();
        costRow.dataset.costInitialized = 'true';
    };

    // ===================== TOTAL COST CALCULATION =====================

    /**
     * Calculate total cost for a procedure (preliminary activities)
     */
    window.calculateProcedureTotalCost = function (procedureRow) {
        let total = 0;
        procedureRow.querySelectorAll('.cost-row .total-input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        return total.toFixed(2);
    };

    /**
     * Calculate total cost for an action (executive activities)
     */
    window.calculateActionTotalCost = function (actionRow) {
        let total = 0;
        actionRow.querySelectorAll('[data-executive-cost-row] .executive-cost-total').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        return total.toFixed(2);
    };

    /**
     * Update total cost display for a procedure
     */
    window.updateProcedureTotalDisplay = function (procedureRow) {
        const total = window.calculateProcedureTotalCost(procedureRow);
        const displayElement = procedureRow.querySelector('.procedure-total-cost');

        if (displayElement) {
            displayElement.textContent = parseFloat(total).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    };

    /**
     * Update total cost display for an action
     */
    window.updateActionTotalDisplay = function (actionRow) {
        const total = window.calculateActionTotalCost(actionRow);
        const displayElement = actionRow.querySelector('.executive-action-total-cost');

        if (displayElement) {
            displayElement.textContent = parseFloat(total).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    };

    // ===================== ACTIVITY WEIGHT CALCULATIONS =====================

    /**
     * Calculate total weight for an activity's procedures
     */
    window.calculateActivityProceduresWeight = function (activityIndex) {
        let total = 0;
        const activityRow = document.querySelector(`[id="activity-${activityIndex}"]`);

        if (activityRow) {
            activityRow.querySelectorAll('.procedure-row input[name*="[weight]"]').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
        }

        return total.toFixed(2);
    };

    /**
     * Calculate total weight for an activity's actions
     */
    window.calculateActivityActionsWeight = function (activityIndex) {
        let total = 0;
        const activityRow = document.querySelector(`[id="executive-activity-${activityIndex}"]`);

        if (activityRow) {
            activityRow.querySelectorAll('.executive-activity-action-row input[name*="[weight]"]').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
        }

        return total.toFixed(2);
    };

    // ===================== PAGE INITIALIZATION =====================

    /**
     * Initialize all date fields on page load
     */
    window.initializeAllDateFields = function () {
        // Initialize procedure rows
        document.querySelectorAll('.procedure-row').forEach(row => {
            window.initializeProcedureRowDates(row);
        });

        // Initialize action rows
        document.querySelectorAll('.executive-activity-action-row').forEach(row => {
            window.initializeActionRowDates(row);
        });
    };

    /**
     * Initialize all cost calculations on page load
     */
    window.initializeAllCostCalculations = function () {
        // Initialize preliminary cost rows
        document.querySelectorAll('[data-cost-row]').forEach(row => {
            window.initializeCostRowCalculation(row, {
                onTotalChange: function (totalInput) {
                    const procedureRow = totalInput.closest('.procedure-row');
                    if (procedureRow) {
                        window.updateProcedureTotalDisplay(procedureRow);
                    }
                }
            });
        });

        // Initialize executive cost rows
        document.querySelectorAll('[data-executive-cost-row]').forEach(row => {
            window.initializeExecutiveCostRowCalculation(row, {
                onTotalChange: function (totalInput) {
                    const actionRow = totalInput.closest('.executive-activity-action-row');
                    if (actionRow) {
                        window.updateActionTotalDisplay(actionRow);
                    }
                }
            });
        });
    };

    /**
     * Main initialization function
     */
    window.initializeProjectForm = function () {
        window.initializeAllDateFields();
        window.initializeAllCostCalculations();
    };

    // ===================== DATA-HIJRI-FIELD HANDLER =====================

    /**
     * Initialize generic handler for data-hijri-field attributes
     * This handles automatic Hijri date conversion for any date input with data-hijri-field
     */
    window.initializeHijriFieldHandlers = function () {
        // Use event delegation on document to handle dynamically added forms
        const handleEvent = function (e) {
            const target = e.target;

            // Check if the modified element has a data-hijri-field or data-hijri-target attribute
            if (target.type === 'date' && (target.hasAttribute('data-hijri-field') || target.hasAttribute('data-hijri-target'))) {
                const targetSelector = target.getAttribute('data-hijri-field') || target.getAttribute('data-hijri-target');

                // If it starts with # it's an ID, otherwise we try to use it as an ID if it's just a string
                const hijriField = targetSelector.startsWith('#')
                    ? document.querySelector(targetSelector)
                    : document.getElementById(targetSelector);

                if (hijriField) {
                    // Convert Gregorian to Hijri
                    const hijriDate = window.gregorianToHijri(target.value);

                    // Format if object
                    if (typeof hijriDate === 'object' && hijriDate !== null) {
                        if (typeof HijriConverter !== 'undefined') {
                            hijriField.value = HijriConverter.formatHijri(hijriDate);
                        } else {
                            const d = String(hijriDate.day).padStart(2, '0');
                            const m = String(hijriDate.month).padStart(2, '0');
                            hijriField.value = `${d}/${m}/${hijriDate.year}`;
                        }
                    } else {
                        hijriField.value = hijriDate || '';
                    }
                }
            }
        };

        document.addEventListener('input', handleEvent);
        document.addEventListener('change', handleEvent);
    };

    // ===================== AUTO-INITIALIZATION =====================

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            window.initializeProjectForm();
            window.initializeHijriFieldHandlers();
        });
    } else {
        window.initializeProjectForm();
        window.initializeHijriFieldHandlers();
    }

})();
