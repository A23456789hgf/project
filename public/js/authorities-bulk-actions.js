document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-authorities');
    const bulkEditBtn = document.getElementById('bulk-edit-btn');
    const selectedCountSpan = document.getElementById('bulk-selected-count');
    const modalSelectedCountSpan = document.getElementById('bulk-edit-selected-count');
    const saveBulkEditBtn = document.getElementById('save-bulk-edit-btn');
    const bulkGovSelect = document.getElementById('bulk_governorate_id');
    const bulkDirSelect = document.getElementById('bulk_directorate_id');

    function getSelectedCheckboxes() {
        return document.querySelectorAll('.authority-checkbox:checked');
    }

    function updateBulkActionsVisibility() {
        const checkedCheckboxes = getSelectedCheckboxes();
        const checkedCount = checkedCheckboxes.length;
        
        if (bulkEditBtn) {
            bulkEditBtn.classList.remove('d-none');
        }
        if (selectedCountSpan) {
            selectedCountSpan.textContent = checkedCount > 0 ? checkedCount : 'الكل';
        }

        const allCheckboxes = document.querySelectorAll('.authority-checkbox');
        if (selectAllCheckbox && allCheckboxes.length > 0) {
            selectAllCheckbox.checked = (checkedCount === allCheckboxes.length);
            selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < allCheckboxes.length);
        }
    }

    // Handle Select All Checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.authority-checkbox');
            allCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkActionsVisibility();
        });
    }

    // Handle individual checkboxes using event delegation
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('authority-checkbox')) {
            updateBulkActionsVisibility();
        }
    });

    // Handle navigation to bulk edit page
    if (bulkEditBtn) {
        bulkEditBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedIds = Array.from(getSelectedCheckboxes()).map(cb => cb.value);
            if (selectedIds.length === 0) {
                window.location.href = '/authorities/bulk-edit-page';
            } else {
                window.location.href = '/authorities/bulk-edit-page?ids=' + selectedIds.join(',');
            }
        });
    }

    // Handle cascading dropdown for governorates -> directorates in modal
    if (bulkGovSelect && bulkDirSelect) {
        bulkGovSelect.addEventListener('change', function() {
            const govId = this.value;
            bulkDirSelect.innerHTML = '<option value="">-- بدون تغيير --</option><option value="null">بدون مديرية</option>';
            
            if (govId && govId !== 'null') {
                fetch('/authorities/get-directorates/' + govId)
                    .then(response => response.json())
                    .then(response => {
                        if (response.success && response.data) {
                            response.data.forEach(item => {
                                const opt = document.createElement('option');
                                opt.value = item.id;
                                opt.textContent = item.name;
                                bulkDirSelect.appendChild(opt);
                            });
                        }
                    })
                    .catch(err => console.error('Error fetching directorates:', err));
            }
        });
    }

    // Helper to generate tree child HTML
    function createChildNodeHtml(child, canBulkEdit, csrfToken) {
        const activeBadgeClass = child.is_active ? 'bg-success' : 'bg-danger';
        const activeBadgeText = child.is_active ? 'نشط' : 'غير نشط';
        const childrenBadge = child.has_children ? `<span class="badge bg-info">${child.children_count} جهة تابعة</span>` : '';
        const toggleBtn = child.has_children 
            ? `<button class="tree-toggle"><i class="fas fa-chevron-right"></i></button>`
            : `<button class="tree-toggle" style="visibility: hidden;"><i class="fas fa-chevron-right"></i></button>`;
        const checkboxHtml = canBulkEdit 
            ? `<input type="checkbox" class="form-check-input authority-checkbox shadow-sm" value="${child.id}" onclick="event.stopPropagation();">`
            : '';

        return `
        <li data-id="${child.id}" data-level="${child.level}" class="${child.has_children ? '' : 'no-children'}">
            <div class="tree-node">
                ${toggleBtn}
                <div class="node-content" style="display: flex; align-items: center; gap: 8px; flex: 1;">
                    ${checkboxHtml}
                    <span class="folder-icon"><i class="fas fa-folder"></i></span>
                    <span class="node-name" style="flex: 1;">${child.agency_name}</span>
                    <span class="node-meta" style="display: flex; gap: 8px; align-items: center;">
                        <span class="badge ${activeBadgeClass}">${activeBadgeText}</span>
                        ${childrenBadge}
                    </span>
                    <div class="node-actions" style="display: flex; gap: 4px; align-items: center;">
                        <a href="${child.url}" class="btn btn-sm" title="عرض التفاصيل"><i class="fas fa-eye"></i></a>
                        <a href="${child.edit_url}" class="btn btn-sm" title="تعديل"><i class="fas fa-edit"></i></a>
                        <a href="/authorities/create?parent_id=${child.id}" class="btn btn-sm" title="إضافة جهة تابعة"><i class="fas fa-plus"></i></a>
                        <form action="${child.delete_url}" method="POST" class="d-inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm" title="حذف" onclick="return confirmAction(this, 'هل أنت متأكد من حذف الجهة ${child.agency_name}؟')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            ${child.has_children ? '<ul style="display: none;"></ul>' : ''}
        </li>`;
    }

    // Tree Toggle and AJAX Loading for Tree View
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.tree-toggle');
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            const li = toggleBtn.closest('li');
            if (!li) return;
            const ul = li.querySelector('ul');
            if (!ul) return;

            const icon = toggleBtn.querySelector('i');

            if (ul.style.display === 'none') {
                toggleBtn.classList.add('expanded');
                if (icon) { icon.classList.remove('fa-chevron-right'); icon.classList.add('fa-chevron-down'); }
                
                if (ul.children.length === 0) {
                    const parentId = li.dataset.id;
                    const level = parseInt(li.dataset.level || 0) + 1;
                    const canBulkEdit = document.getElementById('bulk-edit-btn') !== null || document.getElementById('select-all-authorities') !== null || document.querySelector('.authority-checkbox') !== null;
                    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

                    toggleBtn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:12px;height:12px;"></span>';

                    fetch(`/authorities/get-children?parent_id=${parentId}&level=${level}`)
                        .then(res => res.json())
                        .then(data => {
                            toggleBtn.innerHTML = '<i class="fas fa-chevron-down"></i>';
                            if (data.success && data.children) {
                                let html = '';
                                data.children.forEach(child => {
                                    html += createChildNodeHtml(child, canBulkEdit, csrfToken);
                                });
                                ul.innerHTML = html;
                                ul.style.display = 'block';
                                updateBulkActionsVisibility();
                            }
                        })
                        .catch(err => {
                            console.error('Error loading children:', err);
                            toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                            toggleBtn.classList.remove('expanded');
                        });
                } else {
                    ul.style.display = 'block';
                }
            } else {
                ul.style.display = 'none';
                toggleBtn.classList.remove('expanded');
                if (icon) { icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-right'); }
            }
        }
    });

    // Handle save bulk edit
    if (saveBulkEditBtn) {
        saveBulkEditBtn.addEventListener('click', function() {
            const selectedIds = Array.from(getSelectedCheckboxes()).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            const isActiveVal = document.getElementById('bulk_is_active').value;
            const parentIdVal = document.getElementById('bulk_parent_id').value;
            const govIdVal = document.getElementById('bulk_governorate_id').value;
            const dirIdVal = document.getElementById('bulk_directorate_id').value;
            const scopeVal = document.getElementById('bulk_entity_scope') ? document.getElementById('bulk_entity_scope').value : '';
            const formIdVal = document.getElementById('bulk_financing_form_id') ? document.getElementById('bulk_financing_form_id').value : '';

            if (isActiveVal === '' && parentIdVal === '' && govIdVal === '' && dirIdVal === '' && scopeVal === '' && formIdVal === '') {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('الرجاء تحديد تعديل واحد على الأقل لتطبيقه على الجهات المحددة.');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire('تنبيه', 'الرجاء تحديد تعديل واحد على الأقل لتطبيقه على الجهات المحددة.', 'warning');
                } else {
                    alert('الرجاء تحديد تعديل واحد على الأقل.');
                }
                return;
            }

            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> جاري الحفظ...';
            this.disabled = true;

            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

            fetch('/authorities/bulk-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    is_active: isActiveVal,
                    parent_id: parentIdVal,
                    governorate_id: govIdVal,
                    directorate_id: dirIdVal,
                    entity_scope: scopeVal,
                    financing_form_id: formIdVal
                })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(({ status, body }) => {
                this.innerHTML = originalText;
                this.disabled = false;

                if (status === 200 && body.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(body.message);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('نجاح', body.message, 'success');
                    }
                    const modalEl = document.getElementById('bulkEditModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modalInstance.hide();
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    const errorMsg = body.message || 'حدث خطأ أثناء حفظ التعديلات.';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('خطأ', errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error in bulk update:', error);
                this.innerHTML = originalText;
                this.disabled = false;
                if (typeof toastr !== 'undefined') {
                    toastr.error('حدث خطأ في الاتصال بالخادم.');
                } else {
                    alert('حدث خطأ في الاتصال بالخادم.');
                }
            });
        });
    }
});
