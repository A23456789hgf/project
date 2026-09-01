<div class="project-table-container risks-table-container">
    <div class="project-table-header">
        <i class="fas fa-exclamation-triangle me-2"></i>إدارة مخاطر المشروع
    </div>

    <div class="project-table-wrapper">
        <div class="table-responsive">
            <table class="project-table" id="risksTable">
                <thead>
                    <tr>
                        <th>وصف الخطر</th>
                        <th width="120">مستوى الخطر (0-10)</th>
                        <th>الحل المقترح</th>
                        <th width="100">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="risks-table-body">
                    @php 
                        $risks = old('risks', (isset($project) && $project->risks->count() > 0) ? $project->risks : [['risk' => '', 'risk_rate' => 5, 'proposed_solution' => '']]); 
                    @endphp
                    @foreach($risks as $index => $riskData)
                        @php 
                            $risk = is_array($riskData) ? (object)$riskData : $riskData;
                        @endphp
                    <tr class="risk-row" data-index="{{ $index }}">
                        <td>
                            @if(isset($risk->id))
                                <input type="hidden" name="risks[{{ $index }}][id]" value="{{ $risk->id }}">
                            @endif
                            <input type="text"
                                   name="risks[{{ $index }}][risk]"
                                   class="form-control risk-name-input"
                                   placeholder="وصف الخطر المحتمل"
                                   value="{{ $risk->risk ?? '' }}">
                        </td>
                        <td>
                            <input type="number"
                                   name="risks[{{ $index }}][risk_rate]"
                                   class="form-control text-center risk-rate-input"
                                   min="0" max="10"
                                   value="{{ $risk->risk_rate ?? 5 }}">
                        </td>
                        <td>
                            <input type="text"
                                   name="risks[{{ $index }}][proposed_solution]"
                                   class="form-control"
                                   placeholder="الحل المقترح لإدارة الخطر"
                                   value="{{ $risk->proposed_solution ?? '' }}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="project-btn project-btn-danger remove-risk" title="حذف الخطر">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-3 text-end">
            <button type="button" class="project-btn project-btn-primary" id="add-risk">
                <i class="fas fa-plus me-1"></i>إضافة خطر جديد
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const risksTableBody = document.getElementById('risks-table-body');
    const addRiskBtn = document.getElementById('add-risk');

    if (!risksTableBody || !addRiskBtn) return;

    // إضافة صف جديد
    addRiskBtn.addEventListener('click', function() {
        const index = risksTableBody.querySelectorAll('.risk-row').length;
        const row = document.createElement('tr');
        row.className = 'risk-row';
        row.dataset.index = index;
        row.innerHTML = `
            <td>
                <input type="text"
                       name="risks[${index}][risk]"
                       class="form-control risk-name-input"
                       placeholder="وصف الخطر المحتمل">
            </td>
            <td>
                <input type="number"
                       name="risks[${index}][risk_rate]"
                       class="form-control text-center risk-rate-input"
                       min="0" max="10"
                       value="5">
            </td>
            <td>
                <input type="text"
                       name="risks[${index}][proposed_solution]"
                       class="form-control"
                       placeholder="الحل المقترح لإدارة الخطر">
            </td>
            <td class="text-center">
                <button type="button" class="project-btn project-btn-danger remove-risk" title="حذف الخطر">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        risksTableBody.appendChild(row);
        if (window.updateRiskDropdowns) window.updateRiskDropdowns();
    });

    // حذف صف
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-risk')) {
            const row = e.target.closest('.risk-row');
            const rows = risksTableBody.querySelectorAll('.risk-row');
            if (rows.length > 1) {
                row.remove();
                if (window.updateRiskDropdowns) window.updateRiskDropdowns();
            } else {
                alert('يجب أن يكون هناك خطر واحد على الأقل');
            }
        }
    });

    // التحقق من الحد الأدنى والأقصى عند الإدخال
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('risk-rate-input')) {
            let value = parseInt(e.target.value);
            if (isNaN(value) || value < 0) e.target.value = 0;
            if (value > 10) e.target.value = 10;
        }
        
        // Update dropdowns when risk text or rate changes
        if (e.target.closest('.risk-row')) {
            if (window.updateRiskDropdowns) {
                window.updateRiskDropdowns();
            }
        }
    });

    /**
     * Update all risk dropdowns in the form
     */
    window.updateRiskDropdowns = function() {
        const risks = [];
        document.querySelectorAll('.risk-row').forEach(row => {
            const nameInput = row.querySelector('.risk-name-input');
            const rateInput = row.querySelector('.risk-rate-input');
            const idInput = row.querySelector('input[name*="[id]"]');
            
            if (nameInput) {
                const nameValue = nameInput.value.trim();
                const rateValue = rateInput ? rateInput.value : '';
                const idValue = idInput ? idInput.value : nameValue;
                
                if (nameValue) {
                    risks.push({
                        text: `${nameValue} (${rateValue})`,
                        value: idValue
                    });
                }
            }
        });

        document.querySelectorAll('.project-risk-select').forEach(select => {
            const currentValue = select.value;
            const options = Array.from(select.querySelectorAll('option'));
            const firstOption = options.find(opt => opt.value === "");
            
            select.innerHTML = '';
            if (firstOption) {
                select.appendChild(firstOption);
            } else {
                const defOption = document.createElement('option');
                defOption.value = "";
                defOption.textContent = "اختر المخاطرة";
                select.appendChild(defOption);
            }

            risks.forEach(risk => {
                const option = document.createElement('option');
                option.value = risk.value;
                option.textContent = risk.text;
                select.appendChild(option);
            });
            
            // Restore selection
            if (currentValue) {
                select.value = currentValue;
            }
        });
    };

    // Initial update on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(window.updateRiskDropdowns, 500));
    } else {
        setTimeout(window.updateRiskDropdowns, 500);
    }

    // Trigger update when risk is added
    const originalAddRisk = document.getElementById('add-risk');
    if (originalAddRisk) {
        originalAddRisk.addEventListener('click', () => {
            setTimeout(window.updateRiskDropdowns, 100);
        });
    }

    // Trigger update when risk is removed
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-risk')) {
            setTimeout(window.updateRiskDropdowns, 100);
        }
    });

})();
</script>
