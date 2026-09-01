{{-- resources/views/correspondence/partials/actions-table.blade.php --}}
@php
    $actions = collect();

    // 1. إضافة الإحالات
    foreach($correspondence->referrals as $referral) {
        $actions->push((object)[
            'date' => $referral->referred_at,
            'type' => 'referral',
            'type_label' => 'إحالة',
            'type_color' => 'info',
            'icon' => 'share',
            'from' => $referral->referredByUser->name ?? '---',
            'to' => $referral->referredToEntity->name ?? '---',
            'details' => $referral->referral_text,
            'status' => $referral->status_label,
            'status_color' => $referral->deadline_status_color ?? 'secondary'
        ]);
    }

    // 2. إضافة الردود (Inline Replies)
    foreach($correspondence->replies as $reply) {
        $actions->push((object)[
            'date' => $reply->replied_at,
            'type' => 'reply',
            'type_label' => 'رد داخلي',
            'type_color' => 'success',
            'icon' => 'reply',
            'from' => $reply->repliedByUser->name ?? '---',
            'to' => 'الجهة المرسلة',
            'details' => $reply->reply_text,
            'status' => $reply->status_label,
            'status_color' => 'success'
        ]);
    }

    // 3. إضافة المراسلات التابعة (Formal Replies/Returns)
    foreach($correspondence->children as $child) {
        $isReturn = $child->correspondence_type === 'return';
        $actions->push((object)[
            'date' => $child->sent_at,
            'type' => $isReturn ? 'rejection' : 'formal_reply',
            'type_label' => $isReturn ? 'إرجاع / رفض' : 'رد رسمي',
            'type_color' => $isReturn ? 'danger' : 'primary',
            'icon' => $isReturn ? 'undo' : 'paper-plane',
            'from' => $child->senderUser->name ?? '---',
            'to' => $child->recipientEntity->name ?? '---',
            'details' => $child->subject . ($child->message_body ? ' - ' . Str::limit(strip_tags($child->message_body), 100) : ''),
            'status' => $child->status_label,
            'status_color' => $child->status_color
        ]);
    }

    // ترتيب الإجراءات حسب التاريخ التنازلي (الأحدث أولاً)
    $actions = $actions->sortByDesc('date');
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle shadow-sm border">
        <thead class="bg-light">
            <tr>
                <th style="width: 15%">التاريخ والوقت</th>
                <th style="width: 15%">نوع الإجراء</th>
                <th style="width: 15%">بواسطة</th>
                <th style="width: 15%">إلى</th>
                <th style="width: 30%">التفاصيل</th>
                <th style="width: 10%">الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($actions as $action)
                <tr>
                    <td>
                        <div class="fw-bold text-nowrap">{{ $action->date->format('Y-m-d') }}</div>
                        <small class="text-muted">{{ $action->date->format('H:i') }}</small>
                    </td>
                    <td>
                        <span class="badge bg-{{ $action->type_color }} d-block py-2">
                            <x-icon :name="$action->icon" class="me-1" size="12" />
                            {{ $action->type_label }}
                        </span>
                    </td>
                    <td>
                        <div class="small fw-semibold">{{ $action->from }}</div>
                    </td>
                    <td>
                        <div class="small">{{ $action->to }}</div>
                    </td>
                    <td>
                        <div class="small text-wrap" style="max-width: 350px;">
                            {{ $action->details }}
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-{{ $action->status_color }} bg-opacity-10 text-{{ $action->status_color }} border border-{{ $action->status_color }}">
                            {{ $action->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <x-icon name="info-circle" class="me-1" />
                        لا توجد إحالات أو ردود مسجلة لهذه المراسلة بعد.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
