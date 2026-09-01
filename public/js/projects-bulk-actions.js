document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-projects');
    const projectCheckboxes = document.querySelectorAll('.project-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    function updateBulkActionsVisibility() {
        const checkedCount = document.querySelectorAll('.project-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkDeleteBtn.classList.remove('d-none');
            // Add a counter badge if desired
            bulkDeleteBtn.setAttribute('title', `حذف ${checkedCount} من العناصر المحددة`);
        } else {
            bulkDeleteBtn.classList.add('d-none');
        }
        
        // Update "Select All" state
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = (checkedCount === projectCheckboxes.length && projectCheckboxes.length > 0);
            selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < projectCheckboxes.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            projectCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActionsVisibility();
        });
    }

    projectCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionsVisibility);
    });

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.project-checkbox:checked')).map(cb => cb.value);
            
            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'تأكيد الحذف',
                text: `هل أنت متأكد من حذف ${selectedIds.length} من المشاريع المحددة؟ لا يمكن التراجع عن هذا الإجراء.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم',
                cancelButtonText: 'لا',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const originalContent = this.innerHTML;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                    this.disabled = true;

                    fetch('/projects/bulk-destroy', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload or remove rows
                            toastr.success(data.message);
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            toastr.error(data.message || 'حدث خطأ أثناء الحذف');
                            this.innerHTML = originalContent;
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('حدث خطأ في الاتصال بالخادم');
                        this.innerHTML = originalContent;
                        this.disabled = false;
                    });
                }
            });
        });
    }
});
