@if(isset($approvalStages) && count($approvalStages) > 0)
    <div class="approval-tracker-container">
        <div class="approval-tracker">
            @php
                $currentOrder = $project->current_stage_order ?? 1;
                $isDraft = in_array($project->status, ['draft', 'completed_draft']);
                $isInProgress = in_array($project->status, ['in_progress', 'in_execution']);
            @endphp

            @foreach($approvalStages as $index => $stage)
                @php
                    $isCompleted = !$isDraft && ($stage['drop_order'] < $currentOrder || $isInProgress);
                    $isActive = !$isDraft && !$isInProgress && ($stage['is_current_stage'] ?? false);
                @endphp
                <div class="tracker-item {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }}">
                    <div class="tracker-line"></div>
                    <div class="tracker-content">
                        <div class="tracker-icon">
                            @if($isCompleted)
                                <i class="fas fa-check"></i>
                            @elseif($isActive)
                                <i class="fas fa-circle-notch fa-spin"></i>
                            @else
                                <span>{{ $stage['drop_order'] }}</span>
                            @endif
                        </div>
                        <div class="tracker-text">
                            <div class="tracker-title">{{ $stage['stage_name'] }}</div>
                            <div class="tracker-status">
                                @if($isCompleted)
                                    تم الاعتماد
                                @elseif($isActive)
                                    قيد المراجعة
                                @else
                                    بانتظار وصول الدور
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Final Stage: Implementation --}}
            <div class="tracker-item {{ $isInProgress ? 'completed' : '' }}">
                <div class="tracker-line"></div>
                <div class="tracker-content">
                    <div class="tracker-icon {{ $isInProgress ? 'bg-success border-success text-white' : '' }}">
                        @if($isInProgress)
                            <i class="fas fa-rocket"></i>
                        @else
                            <i class="fas fa-flag-checkered text-muted"></i>
                        @endif
                    </div>
                    <div class="tracker-text">
                        <div class="tracker-title">التنفيذ</div>
                        <div class="tracker-status">
                            @if($isInProgress)
                                قيد التنفيذ
                            @else
                                المرحلة النهائية
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="text-center py-4 text-muted">
        <i class="fas fa-info-circle me-2"></i>لا توجد مراحل اعتماد محددة لهذا المشروع حالياً.
    </div>
@endif
