<!-- Project Movement Log -->
<div class="content-section" style="margin-top: 2rem;">
    <div class="section-header">
        <i class="fas fa-route"></i>
        سجل حركة المشروع الكامل
    </div>

    <!-- Latest Status Summary -->
    <div id="movementStatusSummary" style="display: none; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-left: 4px solid #007bff; border-radius: 6px;">
        <h6 style="margin-top: 0; margin-bottom: 0.5rem; color: #333;">
            <i class="fas fa-info-circle"></i> آخر إجراء
        </h6>
        <p id="movementLatestStatus" style="margin: 0; font-size: 0.95rem; color: #666;"></p>
    </div>

    <div id="projectMovementLog">
        <!-- Logs will be loaded here via JavaScript -->
        
        <!-- Approvals Section -->
        <div style="margin-bottom: 2rem;">
            <h5 style="color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <i class="fas fa-check-circle"></i> الموافقات (Approvals)
            </h5>
            <div id="approvalsLog">
                <div style="text-align: center; color: #6c757d; padding: 1rem;">
                    <i class="fas fa-spinner fa-spin"></i> جاري التحميل...
                </div>
            </div>
        </div>

        <!-- Rejections Section -->
        <div style="margin-bottom: 2rem;">
            <h5 style="color: #dc3545; border-bottom: 2px solid #dc3545; padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <i class="fas fa-times-circle"></i> الرفض (Rejections)
            </h5>
            <div id="rejectionsLog">
                <div style="text-align: center; color: #6c757d; padding: 1rem;">لا توجد حالات رفض</div>
            </div>
        </div>

        <!-- Action Section -->
        <div style="margin-bottom: 2rem;">
            <h5 style="color: #ffc107; border-bottom: 2px solid #ffc107; padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <i class="fas fa-exclamation-circle"></i> إجراء مطلوب (Required Action)
            </h5>
            <div id="requiredActionsLog">
                <div style="text-align: center; color: #6c757d; padding: 1rem;">لا توجد إجراءات مطلوبة</div>
            </div>
        </div>

    </div>
</div>
