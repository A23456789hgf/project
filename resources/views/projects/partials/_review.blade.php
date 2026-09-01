<div class="card">
    <div class="card-header">
        <h5 class="mb-0">مراجعة البيانات المدخلة</h5>
        <small class="text-muted">تم حفظ البيانات كمسودة. يرجى مراجعة جميع البيانات قبل الحفظ النهائي</small>
    </div>
    <div class="card-body">
        <!-- Step 1: Basic Info Review -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th colspan="4" class="text-primary"><i class="fas fa-info-circle me-1"></i> الخطوة 1: بيانات المشروع والموقع</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th width="25%">اسم المشروع</th>
                        <td width="25%" id="review-project-name">-</td>
                        <th width="25%">البرنامج</th>
                        <td width="25%" id="review-program">-</td>
                    </tr>
                    <tr>
                        <th>الأولوية</th>
                        <td colspan="3" id="review-priority">-</td>
                    </tr>
                    <tr>
                        <th>المجال</th>
                        <td id="review-domain">-</td>
                        <th>المجال الفرعي</th>
                        <td id="review-subdomain">-</td>
                    </tr>
                    <tr>
                        <th>نوع التدخل</th>
                        <td id="review-intervention">-</td>
                        <th>نوع المشروع</th>
                        <td id="review-project-type">-</td>
                    </tr>
                    <tr>
                        <th>تاريخ البداية (ميلادي)</th>
                        <td id="review-start-date">-</td>
                        <th>تاريخ البداية (هجري)</th>
                        <td id="review-start-date-hijri">-</td>
                    </tr>
                    <tr>
                        <th>تاريخ النهاية (ميلادي)</th>
                        <td id="review-end-date">-</td>
                        <th>تاريخ النهاية (هجري)</th>
                        <td id="review-end-date-hijri">-</td>
                    </tr>
                    <tr>
                        <th>فئات المستفيدين</th>
                        <td id="review-beneficiary-categories">-</td>
                        <th>مجموعات المستفيدين</th>
                        <td id="review-beneficiary-groups">-</td>
                    </tr>
                    <tr>
                        <th>عدد المستفيدين</th>
                        <td id="review-number-of-beneficiaries">-</td>
                        <th>عدد الأسر المستفيدة</th>
                        <td id="review-number-of-beneficiary-families">-</td>
                    </tr>
                    <tr>
                        <th>مواقع المشروع</th>
                        <td colspan="3" id="review-project-locations">-</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Step 2: Project Details Review -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th colspan="4" class="text-primary"><i class="fas fa-bullseye me-1"></i> الخطوة 2: تفاصيل المشروع والأهداف</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th width="25%">هل المشروع جزء من الخطة</th>
                        <td width="25%" id="review-is-part-of-plan">-</td>
                        <th width="25%">مقدمة المشروع</th>
                        <td width="25%" id="review-project_introduction">-</td>
                    </tr>
                    <tr>
                        <th>ملخص المشروع</th>
                        <td id="review-project-summary">-</td>
                        <th>المشكلة والمبررات</th>
                        <td id="review-problem-justification">-</td>
                    </tr>
                    <tr>
                        <th>الهدف الرئيسي</th>
                        <td colspan="3" id="review-main-objective">-</td>
                    </tr>
                    <tr>
                        <th>مكونات المشروع</th>
                        <td id="review-project-components">-</td>
                        <th>الأثر المتوقع</th>
                        <td id="review-expected-impact">-</td>
                    </tr>
                    <tr>
                        <th colspan="4" class="bg-light">الأهداف الخاصة والنتائج والمخرجات</th>
                    </tr>
                    <tr>
                        <td colspan="4" id="review-specific-objectives" class="p-3"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Step 3: Stakeholders and Risks Review -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th colspan="4" class="text-primary"><i class="fas fa-users me-1"></i> الخطوة 3: أصحاب المصلحة والمخاطر</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th width="25%">المخاطر والعوائق</th>
                        <td width="25%" id="review-obstacles">-</td>
                        <th width="25%">تحليل تأثير المخاطر</th>
                        <td width="25%" id="review-risk-impact">-</td>
                    </tr>
                    <tr>
                        <th>الأدوار والمسؤوليات</th>
                        <td id="review-roles">-</td>
                        <th>خطة التخفيف</th>
                        <td id="review-mitigation-plan">-</td>
                    </tr>
                    <tr>
                        <th>الجهات الإشرافية</th>
                        <td id="review-supervising-entities">-</td>
                        <th>الجهات المنفذة</th>
                        <td id="review-implementing-entities">-</td>
                    </tr>
                    <tr>
                        <th>الجهات المشاركة</th>
                        <td colspan="3" id="review-participating-entities">-</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Step 4: Activities Review -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th colspan="4" class="text-primary"><i class="fas fa-tasks me-1"></i> الخطوة 4: الأنشطة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th width="25%">الأنشطة التمهيدية</th>
                        <td width="25%" id="review-preliminary-activities" class="p-2">-</td>
                        <th width="25%">الأنشطة التنفيذية</th>
                        <td width="25%" id="review-executive-activities" class="p-2">-</td>
                    </tr>
                    <tr>
                        <th>ملخص التمويل التمهيدي</th>
                        <td id="review-preliminary-financial-summary">-</td>
                        <th>ملخص التمويل التنفيذي</th>
                        <td id="review-executive-financial-summary">-</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Step 5: Cost and Financing Review -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th colspan="6" class="text-primary"><i class="fas fa-money-bill-wave me-1"></i> الخطوة 5: التكلفة والتمويل</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th width="16.6%">إجمالي التكلفة</th>
                        <td width="16.6%" id="review-total-cost">-</td>
                        <th width="16.6%">إجمالي التمويل</th>
                        <td width="16.6%" id="review-total-financing">-</td>
                        <th width="16.6%">الفجوة التمويلية</th>
                        <td width="16.6%" id="review-financing-gap">-</td>
                    </tr>
                    <tr>
                        <th colspan="2">مصادر التمويل</th>
                        <td colspan="4" id="review-financing">-</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Step 6: Record Information -->
        <div class="table-responsive mb-0">
            <table class="table table-bordered table-striped table-sm">
                <thead class="table-light">
                    <tr>
                        <th colspan="4" class="text-primary"><i class="fas fa-file-alt me-1"></i> بيانات تسجيل ومراجعة المشروع</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th width="25%">منشئ المشروع</th>
                        <td width="25%">{{ auth()->user()->name }}</td>
                        <th width="25%">الجهة (منشئ)</th>
                        <td width="25%">{{ auth()->user()->department ?? '-' }}</td>
                    </tr>
                    @if(isset($project) && $project->updated_by_user_id)
                    <tr>
                        <th>آخر تعديل بواسطة</th>
                        <td>{{ $project->updatedBy->name ?? '-' }}</td>
                        <th>جهة التعديل</th>
                        <td>{{ $project->updated_by_entity ?? '-' }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mt-4">
            <button type="button" class="btn btn-warning me-2" onclick="showReviewEditSteps()">
                <i class="fas fa-edit"></i> تعديل البيانات
            </button>
            <button type="button" class="btn btn-info" onclick="refreshReview()">
                <i class="fas fa-refresh"></i> تحديث المراجعة
            </button>
        </div>
    </div>
</div>

<script>
    function populateReviewData() {
        const setText = (selector, value) => {
            const el = document.querySelector(selector);
            if (el) {
                el.textContent = value?.toString().trim() ? value : '-';
            }
        };

        const setHtml = (selector, html) => {
            const el = document.querySelector(selector);
            if (el) {
                el.innerHTML = html?.toString().trim() ? html : '-';
            }
        };

        const getSelectedText = (selectName) => {
            return document.querySelector(`[name="${selectName}"] option:checked`)?.textContent?.trim() || '';
        };

        const getInputValue = (name) => {
            const el = document.querySelector(`[name="${name}"]`);
            return el ? (el.value || '').trim() : '';
        };

        // Step 1: Basic Info
        setText('#review-project-name', getInputValue('project_name'));
        setText('#review-program', getSelectedText('program_id'));
        setText('#review-priority', getSelectedText('priority_id'));
        setText('#review-domain', getSelectedText('domain_id'));
        setText('#review-subdomain', getSelectedText('subdomain_id'));
        setText('#review-intervention', getSelectedText('intervention_id'));
        setText('#review-project-type', getSelectedText('project_type_id'));
        setText('#review-start-date', getInputValue('start_date_gregorian'));
        setText('#review-end-date', getInputValue('end_date_gregorian'));
        setText('#review-start-date-hijri', getInputValue('start_date_hijri'));
        setText('#review-end-date-hijri', getInputValue('end_date_hijri'));
        setText('#review-beneficiary-categories', getInputValue('beneficiary_categories'));

        // Beneficiary Groups (multi-select)
        const beneficiaryGroups = [];
        document.querySelectorAll('[name="beneficiary_groups[]"] option:checked').forEach(opt => {
            if (opt.value) beneficiaryGroups.push(opt.textContent.trim());
        });
        setHtml('#review-beneficiary-groups', beneficiaryGroups.length ? beneficiaryGroups.map(g => `<span class="badge bg-info me-1">${g}</span>`).join('') : '-');

        setText('#review-number-of-beneficiaries', getInputValue('number_of_beneficiaries'));
        setText('#review-number-of-beneficiary-families', getInputValue('number_of_beneficiary_families'));

        // Project locations summary
        const locationRows = document.querySelectorAll('#projectLocationsTable tbody tr');
        let locationsHtml = '';
        locationRows.forEach(row => {
            if (!row.classList.contains('project-empty-row')) {
                const selects = row.querySelectorAll('select');
                const texts = Array.from(selects).map(select => select.options[select.selectedIndex]?.text?.trim()).filter(Boolean);
                if (texts.length) {
                    locationsHtml += `<div class="badge bg-secondary me-2 mb-2">${texts.join(' / ')}</div>`;
                }
            }
        });
        setHtml('#review-project-locations', locationsHtml);

        // Step 2: Project Details
        const isPlanValue = document.querySelector('[name="is_part_of_plan"]:checked')?.value;
        setText('#review-is-part-of-plan', isPlanValue === '1' ? 'نعم' : isPlanValue === '0' ? 'لا' : '-');
        setText('#review-project_introduction', getInputValue('project_introduction'));
        setText('#review-project-summary', getInputValue('project_summary'));
        setText('#review-problem-justification', getInputValue('problem_and_justification'));
        setText('#review-main-objective', getInputValue('main_objective'));
        setText('#review-project-components', getInputValue('project_components'));
        setText('#review-expected-impact', getInputValue('expected_impact'));

        // Specific objectives summary
        const specificRows = document.querySelectorAll('#specificObjectivesTable tbody tr.objective-row');
        let specificHtml = '';
        specificRows.forEach(row => {
            const objective = row.querySelector('[name$="[objective]"]')?.value?.trim();
            const indicator = row.querySelector('[name$="[target_value]"]')?.value?.trim();
            const unit = row.querySelector('[name$="[measurement_unit]"]')?.value?.trim();
            const weight = row.querySelector('[name$="[objective_weight]"]')?.value?.trim();

            let resultsHtml = '';
            row.parentElement.querySelectorAll(`tr.result-row[data-objective-index="${row.dataset.index}"]`).forEach(resultRow => {
                const resultName = resultRow.querySelector('[name$="[result_name]"]')?.value?.trim();
                const resultTarget = resultRow.querySelector('[name$="[target_value]"]')?.value?.trim();
                const resultIndicatorType = resultRow.querySelector('[name$="[indicator_type]"]')?.value?.trim();
                const resultIndicatorUnit = resultRow.querySelector('[name$="[indicator_unit]"]')?.value?.trim();

                let outputsHtml = '';
                resultRow.querySelectorAll('.project-output-item').forEach(outputItem => {
                    const outputName = outputItem.querySelector('[name$="[output]"]')?.value?.trim();
                    const outputTarget = outputItem.querySelector('[name$="[target_value]"]')?.value?.trim();
                    const outputIndicatorType = outputItem.querySelector('[name$="[indicator_type]"]')?.value?.trim();
                    const outputIndicatorUnit = outputItem.querySelector('[name$="[indicator_unit]"]')?.value?.trim();

                    if (outputName || outputTarget || outputIndicatorType || outputIndicatorUnit) {
                        outputsHtml += `
                        <div class="sub-badge">
                            <strong>المخرج:</strong> ${outputName || '-'} |
                            <strong>المستهدف:</strong> ${outputTarget || '-'} |
                            <strong>نوع المؤشر:</strong> ${outputIndicatorType || '-'} |
                            <strong>وحدة المؤشر:</strong> ${outputIndicatorUnit || '-'}
                        </div>`;
                    }
                });

                if (resultName || resultTarget || resultIndicatorType || resultIndicatorUnit || outputsHtml) {
                    resultsHtml += `
                    <div class="review-card">
                        <div><strong>النتيجة:</strong> ${resultName || '-'}</div>
                        <div><strong>القيمة المستهدفة:</strong> ${resultTarget || '-'}</div>
                        <div><strong>نوع المؤشر:</strong> ${resultIndicatorType || '-'}</div>
                        <div><strong>وحدة المؤشر:</strong> ${resultIndicatorUnit || '-'}</div>
                        <div><strong>المخرجات:</strong> ${outputsHtml || '-'}</div>
                    </div>`;
                }
            });

            if (objective || indicator || unit || resultsHtml) {
                specificHtml += `
                <div class="review-card">
                    <div><strong>الهدف الخاص:</strong> ${objective || '-'} ${weight ? `(${weight}%)` : ''}</div>
                    <div><strong>القيمة المستهدفة:</strong> ${indicator || '-'}</div>
                    <div><strong>وحدة القياس:</strong> ${unit || '-'}</div>
                    ${resultsHtml || '<div class="text-muted">لا توجد نتائج مسجلة</div>'}
                </div>`;
            }
        });
        setHtml('#review-specific-objectives', specificHtml);

        // Step 3: Risks and entities
        const renderBadges = (selector, badgeClass = 'bg-secondary') => {
            let html = '';
            document.querySelectorAll(selector).forEach(input => {
                const value = input.value?.trim();
                if (value) {
                    html += `<div class="badge ${badgeClass} me-2 mb-2">${value}</div>`;
                }
            });
            return html;
        };

        setHtml('#review-obstacles', renderBadges('[name^="risks"][name$="[risk]"]', 'bg-secondary'));
        setHtml('#review-risk-impact', renderBadges('[name^="risks"][name$="[risk_rate]"]', 'bg-danger'));
        setHtml('#review-mitigation-plan', renderBadges('[name^="risks"][name$="[proposed_solution]"]', 'bg-success'));

        const renderEntities = (rowSelector, nameSelector) => {
            let html = '';
            document.querySelectorAll(rowSelector).forEach(row => {
                const nameEl = row.querySelector(nameSelector);
                const name = nameEl?.textContent?.trim();
                if (!name || name === 'اختر الجهة') return;
                const type = row.querySelector('[name$="[authority_type]"] option:checked')?.textContent?.trim() ||
                    row.querySelector('[name$="[type]"] option:checked')?.textContent?.trim();
                const parent = row.querySelector('[name$="[parent_name]"]')?.value?.trim();
                const role = row.querySelector('[name$="[role]"]')?.value?.trim();
                const extraParts = [type, parent, role].filter(Boolean).join(' - ');
                html += `<div class="badge bg-primary me-2 mb-2">${name}${extraParts ? ` (${extraParts})` : ''}</div>`;
            });
            return html;
        };

        setHtml('#review-supervising-entities', renderEntities('#supervisingAuthoritiesTable tbody tr', '.authority-select option:checked'));
        setHtml('#review-implementing-entities', renderEntities('#implementingEntitiesTable tbody tr', '.authority-select option:checked'));
        setHtml('#review-participating-entities', renderEntities('#participatingEntitiesTable tbody tr', '.authority-select option:checked'));

        // Step 4: Activities
        const renderActivities = (containerSelector, type) => {
            let html = '';
            const isExecutive = type === 'executive';
            const rowClass = isExecutive ? '.executive-activity-row' : '.activity-row';
            const rows = document.querySelectorAll(rowClass);

            rows.forEach(activityRow => {
                const name = activityRow.querySelector('[name*="[name]"]')?.value?.trim();
                const weight = activityRow.querySelector('[name*="[weight]"]')?.value?.trim();
                let subItemsHtml = '';

                const detailsRow = activityRow.nextElementSibling;
                if (detailsRow && (detailsRow.classList.contains('activity-details-row') || detailsRow.classList.contains('activity-details-row'))) {
                    const subItemRows = isExecutive
                        ? detailsRow.querySelectorAll('.executive-activity-action-row')
                        : detailsRow.querySelectorAll('.procedure-row');

                    subItemRows.forEach(subRow => {
                        const subName = isExecutive
                            ? subRow.querySelector('[name*="[action]"]')?.value?.trim()
                            : subRow.querySelector('[name*="[procedure_name]"]')?.value?.trim() || subRow.querySelector('[name*="[name]"]')?.value?.trim();
                        const subWeight = subRow.querySelector('[name*="[weight]"]')?.value?.trim();

                        let thirdLevelHtml = '';
                        const subDetailsRow = subRow.nextElementSibling;
                        if (subDetailsRow && (subDetailsRow.classList.contains('action-details-row') || subDetailsRow.classList.contains('procedure-details-row'))) {
                            const lowLevelRows = isExecutive
                                ? subDetailsRow.querySelectorAll('.executive-cost-row')
                                : subDetailsRow.querySelectorAll('.cost-row');

                            lowLevelRows.forEach(lowRow => {
                                const itemName = isExecutive
                                    ? (lowRow.querySelector('select[name*="[financial_item_id]"] option:checked')?.textContent?.trim() || lowRow.querySelector('.financial-item-display')?.textContent?.trim() || 'بند مالي')
                                    : (lowRow.querySelector('[name*="[name]"]')?.value?.trim() || lowRow.querySelector('select[name*="[financial_item_id]"] option:checked')?.textContent?.trim());
                                const qty = lowRow.querySelector('[name*="[quantity]"]')?.value?.trim();
                                const amount = isExecutive
                                    ? lowRow.querySelector('[name*="[amount]"]')?.value?.trim()
                                    : lowRow.querySelector('[name*="[amount]"]')?.value?.trim();
                                const total = lowRow.querySelector('[name*="[total]"]')?.value?.trim() || (parseFloat(qty || 0) * parseFloat(amount || 0)).toFixed(2);

                                if (itemName || qty || amount) {
                                    thirdLevelHtml += `<div class="sub-badge">${itemName || '-'} | ${qty || '1'} × ${amount || '0'} = ${total || '-'} ريال</div>`;
                                }
                            });
                        }

                        if (subName || subWeight || thirdLevelHtml) {
                            subItemsHtml += `
                            <div class="review-card">
                                <div><strong>${isExecutive ? 'الإجراء' : 'الإجراء'}:</strong> ${subName || 'إجراء بدون اسم'} ${subWeight ? `(${subWeight}%)` : ''}</div>
                                <div><strong>التكاليف:</strong> ${thirdLevelHtml || '-'}</div>
                            </div>`;
                        }
                    });
                }

                if (name || weight || subItemsHtml) {
                    html += `
                    <div class="review-block">
                        <h6 class="review-block-title">${name || 'نشاط بدون اسم'}${weight ? ` <span class="badge bg-dark">${weight}%</span>` : ''}</h6>
                        ${subItemsHtml || '<div class="text-muted">لا توجد إجراءات مسجلة</div>'}
                    </div>`;
                }
            });
            setHtml(containerSelector, html || '<div class="text-muted p-2">لا توجد بيانات</div>');
        };

        renderActivities('#review-preliminary-activities', 'preliminary');
        renderActivities('#review-executive-activities', 'executive');

        // Financial summaries
        const sumInputs = (selector) => Array.from(document.querySelectorAll(selector)).reduce((total, input) => total + (parseFloat(input.value) || 0), 0);
        const preliminaryTotal = sumInputs('[name^="preliminary_financial_summary"][name$="[total]"]');
        const executiveTotal = sumInputs('[name^="executive_financial_summary"][name$="[total]"]');

        setText('#review-preliminary-financial-summary', preliminaryTotal ? preliminaryTotal.toFixed(2) : '0.00');
        setText('#review-executive-financial-summary', executiveTotal ? executiveTotal.toFixed(2) : '0.00');

        // Step 5: Financing and costs
        setText('#review-total-cost', getInputValue('total_cost'));
        setText('#review-total-financing', getInputValue('total_financing'));
        setText('#review-financing-gap', getInputValue('financing_gap'));

        let financingHtml = '';
        document.querySelectorAll('#financingTable tbody tr').forEach(row => {
            if (!row.classList.contains('project-empty-row')) {
                const source = row.querySelector('[name$="[source]"]')?.value?.trim();
                const type = row.querySelector('[name$="[type]"] option:checked')?.textContent?.trim();
                const amount = row.querySelector('[name$="[amount]"]')?.value?.trim();
                const schedule = row.querySelector('[name$="[schedule]"]')?.value?.trim();
                if (source || type || amount || schedule) {
                    financingHtml += `<div class="badge bg-primary me-2 mb-2">${source || '-'}${type ? ` (${type})` : ''} - ${amount || '-'}${schedule ? ` | ${schedule}` : ''}</div>`;
                }
            }
        });
        setHtml('#review-financing', financingHtml);
    }

    function showReviewEditSteps() {
        // Show a modal or dropdown to select which step to edit
        const stepOptions = [
            'الخطوة 1: بيانات المشروع والموقع',
            'الخطوة 2: تفاصيل المشروع والأهداف',
            'الخطوة 3: أصحاب المصلحة والمخاطر',
            'الخطوة 4: الأنشطة',
            'الخطوة 5: التكلفة والتمويل'
        ];

        let optionsHtml = '';
        stepOptions.forEach((option, index) => {
            const stepNum = index + 1;
            const goAction = typeof FormManager !== 'undefined' ? `FormManager.goToStep(${stepNum})` : `console.warn('FormManager not found')`;
            optionsHtml += `<button class="btn btn-outline-primary btn-sm me-2 mb-2" onclick="${goAction}">${option}</button>`;
        });

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'editStepModal';
        modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اختر الخطوة للتعديل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${optionsHtml}
                </div>
            </div>
        </div>
    `;

        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }

    function refreshReview() {
        if (typeof populateReviewData === 'function') {
            populateReviewData();

            // Show refresh confirmation
            const refreshBtn = document.querySelector('[onclick="refreshReview()"]');
            if (refreshBtn) {
                const originalHtml = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<i class="fas fa-check text-success"></i> تم التحديث';
                refreshBtn.disabled = true;

                setTimeout(() => {
                    refreshBtn.innerHTML = originalHtml;
                    refreshBtn.disabled = false;
                }, 2000);
            }
        }
    }
</script>