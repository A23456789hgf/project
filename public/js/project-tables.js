/**
 * نظام إدارة الجداول الديناميكية المتقدم
 * Advanced Dynamic Tables Management System
 * 
 * المميزات:
 * - تفويض الأحداث (Event Delegation) للأداء العالي
 * - إعادة هيكلة مرنة (Configuration-Driven)
 * - حركات سلسة عند الإضافة والحذف
 * - إعادة ترقيم ذكية للحقول والـ Labels
 * - متوافق مع التصميم الاحترافي الجديد
 */

class ProjectTablesManager {
    constructor() {
        this.tables = {};
        this.config = this._defineConfigurations();
        this._initAllTables();
    }

    // ==========================================
    // 1. إعدادات الجداول (Configuration)
    // ==========================================
    _defineConfigurations() {
        return {
            risks: {
                containerId: 'risks-container',
                addBtnId: 'addRiskBtn',
                rowSelector: '.risk-row',
                prefix: 'risks',
                minRows: 1,
                template: (index) => this._getRiskTemplate(index)
            }
            // يمكن إضافة باقي الجداول هنا بنفس الهيكل عند الحاجة
        };
    }

    // ==========================================
    // 2. التهيئة الأولية (Initialization)
    // ==========================================
    _initAllTables() {
        Object.keys(this.config).forEach(tableKey => {
            const config = this.config[tableKey];
            const container = document.getElementById(config.containerId);

            if (!container) return;

            this.tables[tableKey] = { container, config };
            this._bindGlobalEvents(tableKey);
            this._bindExistingRows(tableKey);
        });
    }

    // ==========================================
    // 3. تفويض الأحداث (Event Delegation)
    // ==========================================
    _bindGlobalEvents(tableKey) {
        const { container, config } = this.tables[tableKey];
        const addBtn = document.getElementById(config.addBtnId);

        // زر الإضافة الرئيسي
        addBtn?.addEventListener('click', () => this._addRow(tableKey));

        // تفويض أحداث الأزرار الداخلية (الحذف، الإجراءات الفرعية)
        container.addEventListener('click', (e) => {
            const actionBtn = e.target.closest('[data-action]');
            if (!actionBtn) return;

            const action = actionBtn.dataset.action;
            const row = actionBtn.closest(config.rowSelector);
            if (!row) return;

            switch (action) {
                case 'remove-row':
                    this._removeRow(tableKey, row);
                    break;
                case 'add-sub-action':
                    this._handleSubAction(tableKey, row);
                    break;
            }
        });
    }

    _bindExistingRows(tableKey) {
        const { container, config } = this.tables[tableKey];
        container.querySelectorAll(config.rowSelector).forEach(row => {
            this._applyAnimations(row);
        });
    }

    // ==========================================
    // 4. عمليات الإضافة والحذف (CRUD Operations)
    // ==========================================
    _addRow(tableKey) {
        const { container, config } = this.tables[tableKey];
        const newIndex = container.querySelectorAll(config.rowSelector).length;

        const html = config.template(newIndex);
        container.insertAdjacentHTML('beforeend', html);

        const newRow = container.lastElementChild;
        this._applyAnimations(newRow);

        // إعادة ترقيم الصفوف لضمان تسلسل الأسماء
        this._renumberRows(tableKey);

        // تمرير سلس للصف الجديد
        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    _removeRow(tableKey, rowElement) {
        const { container, config } = this.tables[tableKey];
        const currentRows = container.querySelectorAll(config.rowSelector);

        // منع حذف آخر صف متبقي
        if (currentRows.length <= config.minRows) {
            this._showToast('لا يمكن حذف آخر عنصر متبقي.', 'warning');
            return;
        }

        // حركة اختفاء أنيقة قبل الحذف
        rowElement.style.transition = 'all 0.3s ease';
        rowElement.style.opacity = '0';
        rowElement.style.transform = 'translateX(20px)';

        setTimeout(() => {
            rowElement.remove();
            this._renumberRows(tableKey);
        }, 300);
    }

    // ==========================================
    // 5. إعادة الترقيم الذكي (Smart Renumbering)
    // ==========================================
    _renumberRows(tableKey) {
        const { container, config } = this.tables[tableKey];
        const rows = container.querySelectorAll(config.rowSelector);

        rows.forEach((row, newIndex) => {
            row.dataset.index = newIndex;

            // تحديث الحقول (Inputs, Selects, Textareas)
            row.querySelectorAll('[name], [id]').forEach(field => {
                if (field.name) {
                    field.name = field.name.replace(
                        new RegExp(`${config.prefix}\\[\\d+\\]`),
                        `${config.prefix}[${newIndex}]`
                    );
                }
                if (field.id) {
                    field.id = field.id.replace(
                        new RegExp(`${config.prefix}_\\d+`),
                        `${config.prefix}_${newIndex}`
                    );
                }
            });

            // تحديث الـ Labels المرتبطة بـ for
            row.querySelectorAll('label[for]').forEach(label => {
                label.htmlFor = label.htmlFor.replace(
                    new RegExp(`${config.prefix}_\\d+`),
                    `${config.prefix}_${newIndex}`
                );
            });
        });
    }

    // ==========================================
    // 6. القوالب (Templates)
    // ==========================================
    _getRiskTemplate(index) {
        return `
            <div class="risk-row project-animated-row" data-index="${index}">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="risk_type_${index}">نوع المخاطرة</label>
                        <select name="risks[${index}][risk_type]" id="risk_type_${index}" class="form-select" required>
                            <option value="">-- اختر نوع المخاطرة --</option>
                            <option value="financing">مخاطر تمويلية</option>
                            <option value="technical">مخاطر فنية</option>
                            <option value="administrative">مخاطر إدارية</option>
                            <option value="environmental">مخاطر بيئية</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="risk_desc_${index}">وصف المخاطرة</label>
                        <input type="text" name="risks[${index}][description]" id="risk_desc_${index}" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="risk_prob_${index}">الاحتمالية</label>
                        <select name="risks[${index}][probability]" id="risk_prob_${index}" class="form-select" required>
                            <option value="">-- اختر --</option>
                            <option value="low">منخفض</option>
                            <option value="medium">متوسط</option>
                            <option value="high">مرتفع</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="risk_impact_${index}">التأثير</label>
                        <select name="risks[${index}][impact]" id="risk_impact_${index}" class="form-select" required>
                            <option value="">-- اختر --</option>
                            <option value="low">منخفض</option>
                            <option value="medium">متوسط</option>
                            <option value="high">مرتفع</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label" for="risk_mit_${index}">إجراءات التخفيف</label>
                        <textarea name="risks[${index}][mitigation]" id="risk_mit_${index}" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <div class="project-action-buttons justify-content-end">
                            <button type="button" class="project-btn project-btn-info" data-action="add-sub-action">
                                <i class="fas fa-plus-circle"></i> إجراء
                            </button>
                            <button type="button" class="project-btn project-btn-danger" data-action="remove-row">
                                <i class="fas fa-trash-alt"></i> حذف
                            </button>
                        </div>
                    </div>
                </div>
                <hr class="my-3 border-0" style="height: 1px; background: rgba(0,0,0,0.05);">
            </div>
        `;
    }

    // ==========================================
    // 7. الدوال المساعدة (Helpers)
    // ==========================================
    _applyAnimations(element) {
        element.classList.add('project-animated-row');
    }

    _handleSubAction(tableKey, row) {
        // يمكن توسيع هذه الدالة لاحقاً لإضافة إجراءات فرعية
        console.log('Sub-action triggered for row:', row.dataset.index);
    }

    _showToast(message, type = 'info') {
        if (typeof AppUtils !== 'undefined' && AppUtils.Utils) {
            AppUtils.Utils.showToast(message, type);
        } else if (typeof flasher !== 'undefined') {
            const method = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
            flasher[method](message);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
            alert(message);
        }
    }
}

// ==========================================
// التهيئة عند تحميل الصفحة
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    window.projectTables = new ProjectTablesManager();
});