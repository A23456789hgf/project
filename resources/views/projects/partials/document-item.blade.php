<div class="col">
    <div class="file-item d-flex justify-content-between align-items-center p-2 border rounded">
        <div class="d-flex align-items-center flex-grow-1">
            <i class="fas fa-file me-2 text-muted"></i>
            <div class="flex-grow-1">
                <small class="text-truncate d-block">{{ basename($doc) }}</small>
                <small class="text-muted">{{ Storage::disk('public')->size($doc) ? round(Storage::disk('public')->size($doc) / 1024, 2) . ' KB' : 'N/A' }}</small>
            </div>
        </div>
        <div class="file-actions d-flex gap-1">
            <a href="{{ route('projects.execution.download', [$procedure->project_id, $procedure->execution->id]) }}?path={{ urlencode($doc) }}&action=view" 
               class="btn btn-sm btn-outline-primary" 
               title="عرض" 
               target="_blank">
                <i class="fas fa-eye"></i>
            </a>
            <a href="{{ route('projects.execution.download', [$procedure->project_id, $procedure->execution->id]) }}?path={{ urlencode($doc) }}" 
               class="btn btn-sm btn-outline-success" 
               title="تحميل">
                <i class="fas fa-download"></i>
            </a>
            <button type="button" 
                    class="btn btn-sm btn-outline-danger delete-doc-btn" 
                    title="حذف"
                    data-doc-path="{{ $doc }}"
                    data-type="{{ $type }}"
                    data-delete-url="{{ $type === 'technical' ? route('projects.execution.delete-preliminary-technical', [$procedure->project_id, $procedure->execution->id]) : route('projects.execution.delete-preliminary-financial', [$procedure->project_id, $procedure->execution->id]) }}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-doc-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'تأكيد العملية',
                text: 'هل أنت متأكد من حذف هذا المستند؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم',
                cancelButtonText: 'لا',
                reverseButtons: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (!result.isConfirmed) return;
                
                const docPath = this.dataset.docPath;
                const deleteUrl = this.dataset.deleteUrl;
                
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ document_path: docPath })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.col').remove();
                        if (typeof toastr !== 'undefined') {
                            flasher.success('تم حذف المستند بنجاح');
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            flasher.error('فشل حذف المستند');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof toastr !== 'undefined') {
                        flasher.error('حدث خطأ في الحذف');
                    }
                });
            });
        });
    });
});
</script>
