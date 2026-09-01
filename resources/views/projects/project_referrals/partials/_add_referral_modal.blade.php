<div class="modal fade" id="addReferralModal" tabindex="-1" aria-labelledby="addReferralModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addReferralModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>إضافة إحالة جديدة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addReferralForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="modal_project_id" name="project_id">
                <input type="hidden" id="modal_drop" name="drop">
                <input type="hidden" id="modal_stage_id" name="stage_id">
                <input type="hidden" id="modal_entity_id" name="entity_id" value="{{ auth()->user()->entity_id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="modal_referred_entity_ids" class="form-label fw-bold">الجهة المحال إليها </label>
                            <select id="modal_referred_entity_ids" name="referred_entity_ids[]" class="form-select" multiple style="min-height: 150px;">
                                <option value="">جاري تحميل الجهات...</option>
                            </select>
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>يمكنك اختيار أكثر من جهة (استخدم Ctrl للاختيار المتعدد)</small>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="modal_referral_text" class="form-label fw-bold">نص الإحالة </label>
                            <textarea id="modal_referral_text" name="referral_text" class="form-control" rows="4" placeholder="اكتب تفاصيل الإحالة هنا..."></textarea>
                            <small class="text-muted">الحد الأدنى 10 أحرف</small>
                        </div>

                        <div class="col-md-12">
                            <label for="modal_referral_attachments" class="form-label fw-bold">المرفقات</label>
                            <input type="file" id="modal_referral_attachments" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">يمكنك اختيار أكثر من ملف (الحد الأقصى لكل ملف 20MB)</small>
                            <div id="modal_file_list" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="submit" id="submitAddReferral" class="btn btn-primary px-4">
                        <i class="fas fa-paper-plane me-1"></i>إرسال الإحالة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
