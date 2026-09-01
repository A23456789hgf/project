{{-- resources/views/correspondence/partials/movement-table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover table-striped mb-0">
        <thead class="table-light">
            <tr>
                <th style="width: 15%">التاريخ والوقت</th>
                <th style="width: 15%">نوع الإجراء</th>
                <th style="width: 30%">الوصف</th>
                <th style="width: 15%">بواسطة</th>
                <th style="width: 25%">الجهات المعنية</th>
            </tr>
        </thead>
        <tbody>
            @php
                $movements = $correspondence->getFullThreadTimeline();
            @endphp
            @forelse($movements as $movement)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $movement->action_date->format('Y-m-d') }}</div>
                        <small class="text-muted">{{ $movement->action_date->format('H:i:s') }}</small>
                    </td>
                    <td>
                        <span class="badge bg-{{ 
                            match($movement->action_type) {
                                'create' => 'primary',
                                'reply', 'replied' => 'success',
                                'referral', 'referred' => 'info',
                                'forward', 'forwarded' => 'secondary',
                                'return', 'returned' => 'danger',
                                'close', 'closed' => 'dark',
                                default => 'light text-dark'
                            } 
                        }} w-100">
                            {{ $movement->action_type_label }}
                        </span>
                    </td>
                    <td>
                        <div class="text-wrap" style="max-width: 300px;">
                            {{ $movement->action_description }}
                        </div>
                        @if(!empty($movement->action_details))
                            <div class="mt-1">
                                <button class="btn btn-sm btn-link p-0 text-decoration-none small" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $movement->id }}">
                                    عرض التفاصيل
                                </button>
                                <div class="collapse mt-1" id="details-{{ $movement->id }}">
                                    <div class="card card-body p-2 bg-light small">
                                        @foreach($movement->action_details as $key => $value)
                                            @if($value)
                                                <div><strong>{{ ucfirst($key) }}:</strong> {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($movement->user)
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle me-1 text-muted"></i>
                                <span>{{ $movement->user->name }}</span>
                            </div>
                        @else
                            <span class="text-muted small">النظام</span>
                        @endif
                    </td>
                    <td>
                        @if($movement->from_entity || $movement->to_entity)
                            <div class="small">
                                @if($movement->from_entity)
                                    <div><i class="fas fa-sign-out-alt text-danger me-1"></i>من: {{ $movement->from_entity }}</div>
                                @endif
                                @if($movement->to_entity)
                                    <div><i class="fas fa-sign-in-alt text-success me-1"></i>إلى: {{ $movement->to_entity }}</div>
                                @endif
                            </div>
                        @else
                            <span class="text-muted small">---</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">لا يوجد سجل حركات لهذه المراسلة</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
