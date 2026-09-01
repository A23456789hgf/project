<div class="modal fade" id="bulkEditModal" tabindex="-1" aria-labelledby="bulkEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25 py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="bulkEditModalLabel">
                    <x-icon name="edit" class="text-warning me-2" size="20" />
                    تعديل جماعي للجهات (<span id="bulk-edit-selected-count" class="text-primary mx-1 fw-bolder">0</span> جهة محددة)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 small mb-4 d-flex align-items-center border-0 bg-info bg-opacity-10 text-info rounded-3">
                    <x-icon name="info-circle" class="me-2 flex-shrink-0" size="18" />
                    <span>الحقول المتروكة على خيار <b>"-- بدون تغيير --"</b> لن يتم تعديلها للجهات المحددة.</span>
                </div>

                <form id="bulkEditForm">
                    <div class="mb-3">
                        <label for="bulk_is_active" class="form-label fw-semibold small text-muted">الحالة</label>
                        <select id="bulk_is_active" class="form-select shadow-sm">
                            <option value="">-- بدون تغيير --</option>
                            <option value="1">نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_parent_id" class="form-label fw-semibold small text-muted">الجهة الرئيسية (الأم)</label>
                        <select id="bulk_parent_id" class="form-select shadow-sm">
                            <option value="">-- بدون تغيير --</option>
                            <option value="null">بدون جهة رئيسية (جهة أم)</option>
                            @isset($allAuthorities)
                                @foreach($allAuthorities as $parentAuth)
                                    <option value="{{ $parentAuth->id }}">{{ $parentAuth->agency_name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label for="bulk_governorate_id" class="form-label fw-semibold small text-muted">المحافظة</label>
                            <select id="bulk_governorate_id" class="form-select shadow-sm">
                                <option value="">-- بدون تغيير --</option>
                                <option value="null">بدون محافظة (جهة مركزية)</option>
                                @isset($governorates)
                                    @foreach($governorates as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bulk_directorate_id" class="form-label fw-semibold small text-muted">المديرية</label>
                            <select id="bulk_directorate_id" class="form-select shadow-sm">
                                <option value="">-- بدون تغيير --</option>
                                <option value="null">بدون مديرية</option>
                            </select>
                        </div>
                    </div>

                    {{-- نوع الجهة - استخدام الكائنات --}}
                    <div class="mb-3">
                        <label for="bulk_type_entity_id" class="form-label fw-semibold small text-muted">نوع الجهة</label>
                        <select id="bulk_type_entity_id" class="form-select shadow-sm">
                            <option value="">-- بدون تغيير --</option>
                            <option value="null">بدون تحديد</option>
                            @if(isset($typeEntities) && $typeEntities->isNotEmpty())
                                @foreach($typeEntities as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>لا توجد أنواع جهات متاحة</option>
                            @endif
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light py-3 px-4 border-top">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="save-bulk-edit-btn" class="btn btn-warning px-4 fw-semibold shadow-sm">
                    <x-icon name="check" size="16" class="me-1" /> حفظ التعديلات
                </button>
            </div>
        </div>
    </div>
</div>