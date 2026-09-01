<script>
/**
 * Real-Time Weight Calculation System
 * Automatically calculates and validates weights for:
 * - Preliminary Activities & Procedures
 * - Executive Activities, Actions & Costs
 */

window.WeightCalculator = window.WeightCalculator || (function() {
    'use strict';

    // =============== PRELIMINARY ACTIVITIES WEIGHTS ===============
    
    /**
     * Update total weight for all preliminary activities
     */
    function updatePreliminaryActivitiesWeight() {
        let totalWeight = 0;
        const weights = document.querySelectorAll('[name^="preliminary_activities"][name$="[weight]"]');
        
        weights.forEach(input => {
            // Only count activity-level weights, not procedure weights
            if (input.name.match(/preliminary_activities\[\d+\]\[weight\]/)) {
                totalWeight += parseFloat(input.value) || 0;
            }
        });

        const display = document.getElementById('weight-total');
        const badge = document.getElementById('total-weight-display');
        
        if (display) {
            display.textContent = totalWeight.toFixed(2);
            
            if (badge) {
                if (Math.abs(totalWeight - 100) < 0.01) {
                    badge.className = 'badge bg-success fs-6';
                } else if (totalWeight > 100) {
                    badge.className = 'badge bg-danger fs-6';
                } else {
                    badge.className = 'badge bg-warning fs-6';
                }
            }
        }
    }

    /**
     * Update total weight for all procedures in a specific activity
     */
    function updatePreliminaryProceduresWeight(activityIndex) {
        let totalWeight = 0;
        const procedureWeights = document.querySelectorAll(
            `[name*="preliminary_activities[${activityIndex}][procedures"][name*="[weight]"]`
        );
        
        procedureWeights.forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });

        const display = document.querySelector(
            `.activity-procedures-total-weight[data-activity-index="${activityIndex}"], 
             .activity-procedures-weight-total[data-activity-index="${activityIndex}"]`
        );
        
        if (display) {
            display.textContent = totalWeight.toFixed(2);
            
            // Update color based on weight
            if (Math.abs(totalWeight - 100) < 0.01) {
                display.classList.remove('text-danger');
                display.classList.add('text-success');
            } else {
                display.classList.remove('text-success');
                display.classList.add('text-danger');
            }
        }

        const statusElement = document.querySelector(
            `.activity-procedures-weight-status[data-activity-index="${activityIndex}"]`
        );
        
        if (statusElement) {
            if (Math.abs(totalWeight - 100) < 0.01) {
                statusElement.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle"></i> 100%</span>';
            } else if (totalWeight > 100) {
                statusElement.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> تجاوز</span>';
            } else {
                statusElement.innerHTML = '<span class="badge bg-warning"><i class="fas fa-exclamation-circle"></i> ' + totalWeight.toFixed(2) + '%</span>';
            }
        }
    }

    // =============== EXECUTIVE ACTIVITIES WEIGHTS ===============
    
    /**
     * Update total weight for all executive activities
     */
    function updateExecutiveActivitiesWeight() {
        let totalWeight = 0;
        const weights = document.querySelectorAll('.executive-activity-weight');
        
        weights.forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });

        const display = document.getElementById('executive-weight-total');
        const badge = document.getElementById('executive-total-weight-display');
        
        if (display) {
            display.textContent = totalWeight.toFixed(2);
        }

        if (badge) {
            if (Math.abs(totalWeight - 100) < 0.01) {
                badge.className = 'badge bg-light text-success fs-6';
            } else if (totalWeight > 100) {
                badge.className = 'badge bg-light text-danger fs-6';
            } else {
                badge.className = 'badge bg-light text-primary fs-6';
            }
        }
    }

    /**
     * Update total weight for all actions in a specific executive activity
     */
    function updateExecutiveActionWeight(activityIndex) {
        let totalWeight = 0;
        const actionWeights = document.querySelectorAll(
            `[name*="executive_activities[${activityIndex}][actions"][name*="[weight]"]`
        );
        
        actionWeights.forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });

        const display = document.querySelector(
            `.executive-action-total-weight[data-activity-index="${activityIndex}"]`
        );
        
        if (display) {
            display.textContent = totalWeight.toFixed(2);
        }

        // Update status badge
        const statusElement = document.querySelector(
            `.executive-action-weight-status[data-activity-index="${activityIndex}"]`
        );
        
        if (statusElement) {
            if (Math.abs(totalWeight - 100) < 0.01) {
                statusElement.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle"></i> 100%</span>';
            } else if (totalWeight > 100.00) {
                statusElement.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> تجاوز</span>';
            } else {
                statusElement.innerHTML = '<span class="badge bg-warning"><i class="fas fa-exclamation-circle"></i> ' + totalWeight.toFixed(2) + '%</span>';
            }
        }
    }

    // =============== EVENT LISTENERS ===============
    
    /**
     * Initialize real-time weight calculation listeners
     */
    function initializeListeners() {
        // Shared dynamic listener for ALL weights and costs
        document.addEventListener('input', function(e) {
            // 1. Preliminary Activity Weights
            if (e.target.matches('.activity-weight-input, [name^="preliminary_activities"][name$="[weight]"]')) {
                updatePreliminaryActivitiesWeight();
                if (typeof window.calculateTotals === 'function') window.calculateTotals();
            }

            // 2. Preliminary Procedure Weights
            if (e.target.matches('.procedure-weight, [name*="[procedures]"][name*="[weight]"]')) {
                const match = e.target.name.match(/preliminary_activities\[(\d+)\]/);
                if (match) {
                    updatePreliminaryProceduresWeight(match[1]);
                }
                if (typeof window.calculateTotals === 'function') window.calculateTotals();
            }

            // 3. Executive Activity Weights
            if (e.target.matches('.executive-activity-weight')) {
                updateExecutiveActivitiesWeight();
                if (typeof window.calculateExecutiveTotals === 'function') window.calculateExecutiveTotals();
            }

            // 4. Executive Action Weights
            if (e.target.matches('.action-weight-input') && e.target.name.includes('executive_activities')) {
                const match = e.target.name.match(/executive_activities\[(\d+)\]/);
                if (match) {
                    updateExecutiveActionWeight(match[1]);
                    updateExecutiveActivitiesWeight();
                }
                if (typeof window.calculateExecutiveTotals === 'function') window.calculateExecutiveTotals();
            }
        });
    }

    // =============== PUBLIC API ===============
    
    return {
        init: function() {
            initializeListeners();
            // Initial calculations
            updatePreliminaryActivitiesWeight();
            updateExecutiveActivitiesWeight();
            
            // Calculate all procedure and action weights
            document.querySelectorAll('[data-activity-index]').forEach(el => {
                const index = el.dataset.activityIndex;
                updatePreliminaryProceduresWeight(index);
                updateExecutiveActionWeight(index);
            });
            
            console.log('✅ Real-Time Weight Calculator Initialized');
        },
        
        updateActivityWeight: updatePreliminaryActivitiesWeight,
        updateProcedureWeight: updatePreliminaryProceduresWeight,
        updateExecutiveActivityWeight: updateExecutiveActivitiesWeight,
        updateActionWeight: updateExecutiveActionWeight
    };
})();

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    if (window.WeightCalculator) {
        window.WeightCalculator.init();
    }
});
</script>
