<?php

namespace App\Traits;

use App\Services\ReferenceDataApprovalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait HasApprovalWorkflow
{
    /**
     * Apply status ordering & filtering for index queries
     */
    protected function applyStatusFilter($query, Request $request)
    {
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'pending' || $status === '0') {
                $query->where(function ($q) {
                    $q->where('status', ReferenceDataApprovalService::STATUS_PENDING)
                        ->orWhere('status', 'pending');
                });
            } elseif ($status === 'approved' || $status === '1') {
                $query->where(function ($q) {
                    $q->where('status', ReferenceDataApprovalService::STATUS_APPROVED)
                        ->orWhere('status', 'approved')
                        ->orWhereNull('status');
                });
            } elseif ($status === 'rejected' || $status === '2') {
                $query->where(function ($q) {
                    $q->where('status', ReferenceDataApprovalService::STATUS_REJECTED)
                        ->orWhere('status', 'rejected');
                });
            }
        }

        // Default order: pending items first, then by creation date
        return $query->orderByRaw("CASE WHEN status = 0 OR status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Approve a reference model record
     */
    public function approveModel(Model $record, string $redirectRoute)
    {
        ReferenceDataApprovalService::approve($record);

        return redirect()->route($redirectRoute)
            ->with('success', 'تم اعتماد العنصر وإضافته بشكل دائم وقابل للاستخدام في القوائم العامة.');
    }

    /**
     * Reject a reference model record
     */
    public function rejectModel(Model $record, string $redirectRoute)
    {
        ReferenceDataApprovalService::reject($record);

        return redirect()->route($redirectRoute)
            ->with('success', 'تم رفض العنصر وإيقاف تفعيله.');
    }
}
