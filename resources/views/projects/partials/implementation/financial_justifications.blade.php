@extends('layouts.app')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%);
        color: white;
        border-radius: 12px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="page-header flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="mb-2">
                        <i class="fas fa-file-invoice-dollar me-3"></i>التبريرات المالية
                    </h1>
                    <p class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>عرض جميع التبريرات المالية للأنشطة الأولية والتنفيذية
                    </p>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>عودة
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="p-3">رقم المشروع</th>
                            <th class="p-3">اسم المشروع</th>
                            <th class="p-3">نوع النشاط</th>
                            <th class="p-3">النشاط</th>
                            <th class="p-3">الإجراء</th>
                            <th class="p-3">المبلغ المخطط</th>
                            <th class="p-3">المبلغ المنفذ</th>
                            <th class="p-3">المبلغ الزائد </th>
                            <th class="p-3">التبرير</th>
                            <th class="p-3">المرفقات</th>
                            <th class="p-3">حالة الاعتماد</th>
                            <th class="p-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasFinJustifications = false; @endphp
                        
                        @foreach($projects as $project)
                            {{-- Loop through Preliminary Procedures --}}
                            @if($project->preliminaryActivities)
                                @foreach($project->preliminaryActivities as $activity)
                                    @if($activity->procedures)
                                        @foreach($activity->procedures as $procedure)
                                            @if($procedure->procedureBudgetJustification)
                                                @php 
                                                    $justification = $procedure->procedureBudgetJustification;
                                                    $hasFinJustifications = true; 
                                                @endphp
                                                <tr>
                                                    <td class="p-3 fw-bold">{{ $project->form_number }}</td>
                                                    <td class="p-3 fw-bold">{{ $project->project_name }}</td>
                                                    <td class="p-3"><span class="badge bg-info bg-opacity-10 text-info border border-info">أولي</span></td>
                                                    <td class="p-3">{{ $activity->name }}</td>
                                                    <td class="p-3">{{ $procedure->procedure_name }}</td>
                                                    <td class="p-3 fw-bold">{{ number_format($justification->planned_total, 2) }} ﷼</td>
                                                    <td class="p-3 fw-bold">{{ number_format($justification->actual_total, 2) }} ﷼</td>
                                                    <td class="p-3 fw-bold text-danger">{{ number_format($justification->actual_total - $justification->planned_total, 2) }} ﷼</td>
                                                    <td class="p-3">{{ $justification->justification }}</td>
                                                    <td class="p-3">
                                                        @if($justification->attachments)
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach($justification->attachments ?? [] as $attachment)
                                                                    @php
                                                                        $extension = pathinfo($attachment, PATHINFO_EXTENSION);
                                                                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                                                                        $isPdf = strtolower($extension) === 'pdf';
                                                                        $url = asset('storage/' . str_replace('\\', '/', $attachment));
                                                                    @endphp
                                                                    
                                                                    <div class="btn-group btn-group-sm" role="group">
                                                                        @if($isImage || $isPdf)
                                                                            <button type="button" class="btn btn-outline-primary" onclick="viewAttachment('{{ $url }}', '{{ $isImage ? 'image' : 'pdf' }}')" title="معاينة">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        @endif
                                                                        <a href="{{ $url }}" class="btn btn-outline-secondary" title="تحميل" download>
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="p-3">
                                                        @if($justification->approval_status == 'approved')
                                                            <span class="badge bg-success">معتمد</span>
                                                        @elseif($justification->approval_status == 'rejected')
                                                            <span class="badge bg-danger">مرفوض</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                                        @endif
                                                    </td>
                                                    <td class="p-3">
                                                        @if($justification->approval_status == 'pending')
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-success" onclick="approveJustification('{{ $justification->id }}', 'preliminary')">
                                                                    <i class="fas fa-check"></i> اعتماد
                                                                </button>
                                                                <button type="button" class="btn btn-danger" onclick="rejectJustification('{{ $justification->id }}', 'preliminary')">
                                                                    <i class="fas fa-times"></i> رفض
                                                                </button>
                                                            </div>
                                                        @else
                                                            @if($justification->approval_status == 'rejected')
                                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewRejectionDetails('{{ $justification->reviewer_notes }}', '{{ $justification->rejection_attachment ? asset('storage/' . $justification->rejection_attachment) : '' }}')">
                                                                    <i class="fas fa-info-circle"></i> سبب الرفض
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif

                            {{-- Loop through Executive Actions --}}
                            @if($project->executiveActivities)
                                @foreach($project->executiveActivities as $activity)
                                    @if($activity->actions)
                                        @foreach($activity->actions as $action)
                                            @if($action->budgetJustification)
                                                @php 
                                                    $justification = $action->budgetJustification;
                                                    $hasFinJustifications = true; 
                                                @endphp
                                                <tr>
                                                    <td class="p-3 fw-bold">{{ $project->form_number }}</td>
                                                    <td class="p-3 fw-bold">{{ $project->project_name }}</td>
                                                    <td class="p-3"><span class="badge bg-success bg-opacity-10 text-success border border-success">تنفيذي</span></td>
                                                    <td class="p-3">{{ $activity->name }}</td>
                                                    <td class="p-3">{{ $action->action }}</td>
                                                    <td class="p-3 fw-bold">{{ number_format($justification->planned_amount, 2) }} ﷼</td>
                                                    <td class="p-3 fw-bold">{{ number_format($justification->actual_amount, 2) }} ﷼</td>
                                                    <td class="p-3 fw-bold text-danger">{{ number_format($justification->actual_amount - $justification->planned_amount, 2) }} ﷼</td>
                                                    <td class="p-3">{{ $justification->justification }}</td>
                                                    <td class="p-3">
                                                        @if($justification->attachments)
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach($justification->attachments ?? [] as $attachment)
                                                                    @php
                                                                        $extension = pathinfo($attachment, PATHINFO_EXTENSION);
                                                                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                                                                        $isPdf = strtolower($extension) === 'pdf';
                                                                        $url = asset('storage/' . str_replace('\\', '/', $attachment));
                                                                    @endphp
                                                                    
                                                                    <div class="btn-group btn-group-sm" role="group">
                                                                        @if($isImage || $isPdf)
                                                                            <button type="button" class="btn btn-outline-primary" onclick="viewAttachment('{{ $url }}', '{{ $isImage ? 'image' : 'pdf' }}')" title="معاينة">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        @endif
                                                                        <a href="{{ $url }}" class="btn btn-outline-secondary" title="تحميل" download>
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="p-3">
                                                        @if($justification->approval_status == 'approved')
                                                            <span class="badge bg-success">معتمد</span>
                                                        @elseif($justification->approval_status == 'rejected')
                                                            <span class="badge bg-danger">مرفوض</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                                        @endif
                                                    </td>
                                                    <td class="p-3">
                                                        @if($justification->approval_status == 'pending')
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-success" onclick="approveJustification('{{ $justification->id }}', 'executive')">
                                                                    <i class="fas fa-check"></i> اعتماد
                                                                </button>
                                                                <button type="button" class="btn btn-danger" onclick="rejectJustification('{{ $justification->id }}', 'executive')">
                                                                    <i class="fas fa-times"></i> رفض
                                                                </button>
                                                            </div>
                                                        @else
                                                            @if($justification->approval_status == 'rejected')
                                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewRejectionDetails('{{ $justification->reviewer_notes }}', '{{ $justification->rejection_attachment ? asset('storage/' . $justification->rejection_attachment) : '' }}')">
                                                                    <i class="fas fa-info-circle"></i> سبب الرفض
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        @if(!$hasFinJustifications)
                            <tr>
                                <td colspan="12" class="text-center p-5 text-muted">
                                    <i class="fas fa-file-invoice-dollar fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">لا توجد تبريرات مالية مسجلة حتى الآن</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Attachment Preview Modal -->
<div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">معاينة المرفق</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0" id="attachmentModalBody" style="min-height: 200px; background: #f8f9fa;">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">رفض التبرير المالي</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectionForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="reject_id" name="id">
                    <input type="hidden" id="reject_type" name="type">
                    <input type="hidden" name="status" value="rejected">
                    
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">سبب الرفض </label>
                        <textarea class="form-control" id="rejection_reason" name="reviewer_notes" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejection_attachment" class="form-label">مرفق (اختياري)</label>
                        <input class="form-control" type="file" id="rejection_attachment" name="rejection_attachment">
                        <div class="form-text">يمكنك إرفاق ملف يوضح سبب الرفض (PDF, Images)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rejection Details Modal -->
<div class="modal fade" id="rejectionDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الرفض</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold mb-2">سبب الرفض:</label>
                    <p id="view_rejection_reason" class="p-3 bg-light rounded border"></p>
                </div>
                <div id="view_rejection_attachment_container" class="d-none">
                    <label class="fw-bold mb-2">المرفق:</label>
                    <div>
                        <a id="view_rejection_attachment_link" href="#" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-download me-1"></i> تحميل المرفق
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Confirmation Form (Hidden) -->
<form id="approvalForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="approve_id">
    <input type="hidden" name="type" id="approve_type">
    <input type="hidden" name="status" value="approved">
</form>

@endsection

@section('scripts')
<script>
    function viewAttachment(url, type) {
        const modalBody = document.getElementById('attachmentModalBody');
        modalBody.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100 p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        const modal = new bootstrap.Modal(document.getElementById('attachmentModal'));
        modal.show();

        setTimeout(() => {
            if (type === 'image') {
                modalBody.innerHTML = `<img src="${url}" class="img-fluid" style="max-height: 80vh;">`;
            } else if (type === 'pdf') {
                modalBody.innerHTML = `<iframe src="${url}" style="width: 100%; height: 80vh; border: none;"></iframe>`;
            }
        }, 300);
    }

    function approveJustification(id, type) {
        Swal.fire({
            title: 'تأكيد العملية',
            text: 'هل أنت متأكد من اعتماد هذا التبرير المالي؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم',
            cancelButtonText: 'لا',
            reverseButtons: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('approvalForm');
                document.getElementById('approve_id').value = id;
                document.getElementById('approve_type').value = type;
                
                const formData = new FormData(form);
                
                fetch('{{ route("financial-justifications.update-status") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        flasher.success('تم اعتماد التبرير بنجاح');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        flasher.error('حدث خطأ أثناء الاعتماد');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    flasher.error('حدث خطأ غير متوقع');
                });
            }
        });
    }

    function rejectJustification(id, type) {
        document.getElementById('reject_id').value = id;
        document.getElementById('reject_type').value = type;
        document.getElementById('rejectionForm').reset();
        
        const modal = new bootstrap.Modal(document.getElementById('rejectionModal'));
        modal.show();
    }

    document.getElementById('rejectionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("financial-justifications.update-status") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                flasher.success('تم رفض التبرير بنجاح');
                bootstrap.Modal.getInstance(document.getElementById('rejectionModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                flasher.error('حدث خطأ أثناء الرفض');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            flasher.error('حدث خطأ غير متوقع');
        });
    });

    function viewRejectionDetails(notes, attachmentUrl) {
        document.getElementById('view_rejection_reason').textContent = notes;
        
        const attachmentContainer = document.getElementById('view_rejection_attachment_container');
        const attachmentLink = document.getElementById('view_rejection_attachment_link');
        
        if (attachmentUrl) {
            attachmentLink.href = attachmentUrl;
            attachmentContainer.classList.remove('d-none');
        } else {
            attachmentContainer.classList.add('d-none');
        }
        
        const modal = new bootstrap.Modal(document.getElementById('rejectionDetailsModal'));
        modal.show();
    }
</script>
@endsection
