@extends('layouts.app')

@section('content')
    @if(!auth()->user()->hasSignature())
        <div class="alert alert-warning mb-4 shadow-sm border-warning">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>تنبيه هام</h5>
            <p class="mb-0">
                لم تقم بإضافة توقيعك الشخصي في النظام بعد. يرجى 
                <a href="{{ route('profile.show') }}" class="alert-link text-decoration-underline fw-bold">الضغط هنا للانتقال إلى ملفك الشخصي وإضافة توقيعك</a>
                حتى تتمكن من اتخاذ إجراءات على المراسلات (مثل الرد أو الإحالة أو التوجيه).
            </p>
        </div>
    @endif

    @include('correspondence.partials.details-content')
@endsection
@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/ar.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for referral entity selector (uses dynamic ID)
    $('[id^="referred_to_entity_id"]').select2({
        placeholder: 'اختر الجهة...',
        allowClear: true,
        language: "ar"
    });

    // Initialize Select2 for referral form (standalone)
    $('.select2-dynamic').select2({
        placeholder: 'اختر الجهة...',
        allowClear: true,
        language: "ar"
    });

    // Initialize Select2 inside modals
    $('.select2-dynamic-modal').select2({
        dropdownParent: $('#forwardModal-{{ $correspondence->id ?? "" }}'),
        placeholder: 'اختر الأقسام الفرعية...',
        language: "ar"
    });

    $('.select2').select2({
        placeholder: 'اختر الأقسام الفرعية...',
        language: "ar"
    });

    $('.select2-modal').select2({
        dropdownParent: $('#forwardModal'),
        placeholder: 'اختر الأقسام الفرعية...',
        language: "ar"
    });

    // Set minimum date for deadline
    const today = new Date().toISOString().split('T')[0];
    $('[id^="deadline"]').attr('min', today);

    // File size validation
    $('[id^="attachments"], [id^="referral_attachments"]').on('change', function() {
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        const files = this.files;
        
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
                alert(`الملف ${files[i].name} يتجاوز الحد الأقصى المسموح به (10 ميجابايت)`);
                $(this).val('');
                return;
            }
        }
    });
});


function printFromPreview() {
    const iframe = document.getElementById('previewIframe');
    if (iframe) {
        iframe.contentWindow.print();
    }
}
// تم استبدال تأكيد العلم اليدوي بالتأكيد التلقائي عند الاطلاع
// Manual acknowledgement replaced by automatic acknowledgement on view
</script>
@endpush