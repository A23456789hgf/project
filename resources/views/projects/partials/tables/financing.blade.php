<div class="project-table-container">
    <div class="project-table-header">
        <i class="fas fa-money-bill-wave"></i>تمويل المشروع
    </div>
    
    <div class="project-table-wrapper">
        <table class="project-table" id="projectFinancingTable">
            <thead>
                <tr>
                    <th>مصدر التمويل</th>
                    <th>جهة التمويل</th>
                    <th>نوع التمويل</th>
                    <th>شكل التمويل</th>
                    <th>الشكل الفرعي</th>
                    <th>مبلغ التمويل</th>
                    <th>النسبة المئوية</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($project) && $project->financings && $project->financings->count() > 0)
                    @foreach($project->financings as $index => $financing)
                    <tr class="project-animated-row financing-row" data-index="{{ $index }}">
                        <td data-label="مصدر التمويل">
                            <select name="financings[{{ $index }}][funding_source_id]" class="form-select select2">
                                <option value="">اختر مصدر التمويل</option>
                                @foreach($fundingSources ?? [] as $src)
                                    <option value="{{ $src->id }}" {{ $financing->funding_source_id == $src->id ? 'selected' : '' }}>{{ $src->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td data-label="جهة التمويل">
                            <select name="financings[{{ $index }}][entity_id]" class="form-select select2">
                                <option value="">اختر الجهة</option>
                                @foreach($entities ?? [] as $ent)
                                    <option value="{{ $ent->id }}" {{ $financing->entity_id == $ent->id ? 'selected' : '' }}>{{ $ent->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td data-label="نوع التمويل">
                            <select name="financings[{{ $index }}][financing_type_id]" class="form-select select2">
                                <option value="">اختر النوع</option>
                                @foreach($financingTypes ?? [] as $type)
                                    <option value="{{ $type->id }}" {{ $financing->financing_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td data-label="شكل التمويل">
                            <select name="financings[{{ $index }}][financing_form_id]" class="form-select select2">
                                <option value="">اختر الشكل</option>
                                @foreach($financingForms ?? [] as $form)
                                    <option value="{{ $form->id }}" {{ $financing->financing_form_id == $form->id ? 'selected' : '' }}>{{ $form->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td data-label="الشكل الفرعي">
                            <select name="financings[{{ $index }}][sub_financing_form_id]" class="form-select select2">
                                <option value="">اختر الشكل الفرعي</option>
                                @foreach($subFinancingForms ?? [] as $sform)
                                    <option value="{{ $sform->id }}" {{ $financing->sub_financing_form_id == $sform->id ? 'selected' : '' }}>{{ $sform->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td data-label="مبلغ التمويل">
                            <input type="number" step="0.01" name="financings[{{ $index }}][financing_amount]" 
                                   class="form-control funding-amount" value="{{ $financing->financing_amount }}" 
                                   min="0" placeholder="0.00">
                        </td>
                        <td data-label="النسبة المئوية">
                            <input type="number" step="0.01" name="financings[{{ $index }}][financing_percentage]" 
                                   class="form-control funding-percentage" value="{{ $financing->financing_percentage }}" 
                                   min="0" max="100" placeholder="0.00" readonly>
                        </td>
                        <td data-label="الإجراءات">
                            <div class="project-action-buttons">
                                <button type="button" class="project-btn project-btn-danger remove-financing">
                                    <i class="fas fa-trash"></i>حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr class="project-empty-row">
                        <td colspan="8" class="text-center">
                            <i class="fas fa-money-bill-wave fa-2x mb-2"></i><br>
                            لا توجد مصادر تمويل مضافة بعد
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="addFinancingBtn">
                <i class="fas fa-plus"></i>إضافة مصدر تمويل جديد
            </button>
        </div>
    </div>
</div>


<script>
// Financing Management Module مع دعم كامل للبحث
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const FinancingManager = {
            counter: {{ isset($project) && $project->financings ? $project->financings->count() : 0 }},
            
            init: function() {
                this.bindEvents();
                this.calculatePercentages();
                
                // Initial call on page load
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2('#projectFinancingTable');
                }
                
                console.log('✅ Financing Manager Initialized with Select2');
            },
            
            bindEvents: function() {
                // Add financing button
                const addBtn = document.getElementById('addFinancingBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => this.addFinancing());
                }
                
                // Remove financing (delegated event)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-financing')) {
                        const row = e.target.closest('tr');
                        Swal.fire({
                            title: 'تأكيد العملية',
                            text: 'هل أنت متأكد من حذف هذا السجل؟',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'نعم',
                            cancelButtonText: 'لا',
                            reverseButtons: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.removeFinancing(row);
                            }
                        });
                    }
                });
                
                // Calculate percentages when amounts change
                document.addEventListener('input', (e) => {
                    if (e.target.classList.contains('funding-amount')) {
                        this.calculatePercentages();
                    }
                });
            },
            
            
            addFinancing: function() {
                // Remove empty row if exists
                const emptyRow = document.querySelector('.project-empty-row');
                if (emptyRow) {
                    emptyRow.remove();
                }
                
                const tbody = document.querySelector('#projectFinancingTable tbody');
                const row = this.createFinancingRow();
                tbody.appendChild(row);
                
                // Add animation class
                row.classList.add('project-animated-row');
                
                // Initialize Select2 for the new row
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2(row);
                }
                
                this.counter++;
                this.calculatePercentages();
            },
            
            createFinancingRow: function() {
                const row = document.createElement('tr');
                row.classList.add('financing-row');
                row.dataset.index = this.counter;
                
                // ملاحظة: تمت إضافة class="searchable-dropdown" لكل عناصر select
                row.innerHTML = `
                    <td data-label="مصدر التمويل">
                        <select name="financings[${this.counter}][funding_source_id]" class="form-select select2">
                            <option value="">اختر مصدر التمويل</option>
                            @foreach($fundingSources ?? [] as $src)
                                <option value="{{ $src->id }}">{{ $src->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td data-label="جهة التمويل">
                        <select name="financings[${this.counter}][entity_id]" class="form-select select2">
                            <option value="">اختر الجهة</option>
                            @foreach($entities ?? [] as $ent)
                                <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td data-label="نوع التمويل">
                        <select name="financings[${this.counter}][financing_type_id]" class="form-select select2">
                            <option value="">اختر النوع</option>
                            @foreach($financingTypes ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td data-label="شكل التمويل">
                        <select name="financings[${this.counter}][financing_form_id]" class="form-select select2">
                            <option value="">اختر الشكل</option>
                            @foreach($financingForms ?? [] as $form)
                                <option value="{{ $form->id }}">{{ $form->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td data-label="الشكل الفرعي">
                        <select name="financings[${this.counter}][sub_financing_form_id]" class="form-select select2">
                            <option value="">اختر الشكل الفرعي</option>
                            @foreach($subFinancingForms ?? [] as $sform)
                                <option value="{{ $sform->id }}">{{ $sform->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td data-label="مبلغ التمويل">
                        <input type="number" step="0.01" name="financings[${this.counter}][financing_amount]" 
                               class="form-control funding-amount" min="0" placeholder="0.00">
                    </td>
                    <td data-label="النسبة المئوية">
                        <input type="number" step="0.01" name="financings[${this.counter}][financing_percentage]" 
                               class="form-control funding-percentage" min="0" max="100" placeholder="0.00" readonly>
                    </td>
                    <td data-label="الإجراءات">
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-danger remove-financing">
                                <i class="fas fa-trash"></i>حذف
                            </button>
                        </div>
                    </td>
                `;
                
                return row;
            },
            
            removeFinancing: function(row) {
                row.remove();
                this.calculatePercentages();
                
                const tbody = document.querySelector('#projectFinancingTable tbody');
                if (tbody.children.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'project-empty-row';
                    emptyRow.innerHTML = `
                        <td colspan="8" class="text-center">
                            <i class="fas fa-money-bill-wave fa-2x mb-2"></i><br>
                            لا توجد مصادر تمويل مضافة بعد
                        </td>
                    `;
                    tbody.appendChild(emptyRow);
                }
            },
            
            calculatePercentages: function() {
                const amountInputs = document.querySelectorAll('.funding-amount');
                const percentageInputs = document.querySelectorAll('.funding-percentage');
                
                let totalAmount = 0;
                amountInputs.forEach(input => {
                    const value = parseFloat(input.value) || 0;
                    totalAmount += value;
                });
                
                amountInputs.forEach((input, index) => {
                    const amount = parseFloat(input.value) || 0;
                    const percentage = totalAmount > 0 ? (amount / totalAmount * 100).toFixed(2) : 0;
                    if (percentageInputs[index]) {
                        percentageInputs[index].value = percentage;
                    }
                });
            }
        };
        
        FinancingManager.init();
        
        window.FinancingManager = FinancingManager;
    });
})();
</script>