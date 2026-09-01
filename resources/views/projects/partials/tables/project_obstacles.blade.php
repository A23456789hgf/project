<div class="project-table-container">
    <div class="project-table-header">
        <i class="fas fa-exclamation-triangle"></i>مخاطر المشروع
    </div>

    <div class="project-table-wrapper">
        <table class="project-table" id="projectRisksTable">
            <thead>
                <tr>
                    <th>الخطر</th>
                    <th>معدل الخطر (1-10)</th>
                    <th>الحل المقترح</th>
                    <th width="100">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <tr class="project-empty-row">
                    <td colspan="4" class="text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                        لا توجد مخاطر مضافة بعد
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="addRiskBtn">
                <i class="fas fa-plus"></i>إضافة خطر جديد
            </button>
        </div>
    </div>
</div>

<script>
// Risk Management Module - Using AppUtils
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Wait for AppUtils to be available
        if (typeof AppUtils === 'undefined') {
            console.error('AppUtils not found! Make sure app-utils.js is loaded.');
            return;
        }

        const RiskManager = {
            counter: 0,

            init: function() {
                this.bindEvents();
                console.log('✅ Risk Manager Initialized');
            },

            bindEvents: function() {
                // Add risk button
                const addBtn = document.getElementById('addRiskBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => this.addRisk());
                }

                // Remove risk (delegated event)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-risk')) {
                        this.removeRisk(e.target.closest('tr'));
                    }
                });
            },

            addRisk: function() {
                // Remove empty row if exists
                const emptyRow = document.querySelector('.project-empty-row');
                if (emptyRow) {
                    emptyRow.remove();
                }

                const tbody = document.querySelector('#projectRisksTable tbody');
                const row = this.createRiskRow();
                tbody.appendChild(row);

                // Animate using AppUtils
                AppUtils.Utils.animate(row, 'fadeIn');
                this.counter++;
            },

            createRiskRow: function() {
                const row = document.createElement('tr');
                row.dataset.riskIndex = this.counter;

                row.innerHTML = `
                    <td>
                        <textarea name="risks[${this.counter}][risk]"
                            class="form-control"
                            placeholder="أدخل وصف الخطر"
                            maxlength="255"></textarea>
                    </td>
                    <td>
                        <input type="number" name="risks[${this.counter}][risk_rate]"
                               class="form-control risk-rate-input"
                               min="1" max="10"
                               placeholder="1-10">
                    </td>
                    <td>
                        <textarea name="risks[${this.counter}][proposed_solution]"
                            class="form-control"
                            placeholder="أدخل الحل المقترح"
                            maxlength="500"></textarea>
                    </td>
                    <td>
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-danger remove-risk" title="حذف الخطر">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;

                return row;
            },

            removeRisk: function(row) {
                AppUtils.Table.removeRow(row).then((removed) => {
                    if (removed) {
                        this.checkEmptyTable();
                    }
                });
            },

            checkEmptyTable: function() {
                const tbody = document.querySelector('#projectRisksTable tbody');
                AppUtils.Table.checkEmpty(tbody, `
                    <i class="fas fa-exclamation-triangle fa-2x mb-2 text-muted"></i><br>
                    لا توجد مخاطر مضافة بعد
                `);
            }
        };

        // Initialize Risk Manager
        RiskManager.init();

        // Make it globally accessible
        window.RiskManager = RiskManager;
    });
})();
</script>