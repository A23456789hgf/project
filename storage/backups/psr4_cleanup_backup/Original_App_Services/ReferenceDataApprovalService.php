<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ReferenceDataApprovalService
{
    const STATUS_APPROVED = 1;

    const STATUS_PENDING = 0;

    const STATUS_REJECTED = 2;

    /**
     * Scope query to only return approved items
     */
    public static function scopeApproved(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_APPROVED)
                ->orWhere('status', 'approved')
                ->orWhereNull('status');
        });
    }

    /**
     * Get or create a record safely without creating duplicate pending entries
     */
    public static function getOrCreatePendingRecord(string $modelClass, array $searchAttributes, array $additionalData = [], string $nameField = 'name'): Model
    {
        $nameValue = trim($searchAttributes[$nameField] ?? '');

        // Query for existing record (without global scopes to check entire table)
        $query = $modelClass::withoutGlobalScopes();
        foreach ($searchAttributes as $key => $val) {
            if ($key === $nameField) {
                $query->whereRaw("LOWER(TRIM({$nameField})) = ?", [mb_strtolower($nameValue)]);
            } else {
                if (is_null($val)) {
                    $query->whereNull($key);
                } else {
                    $query->where($key, $val);
                }
            }
        }

        $existing = $query->first();
        if ($existing) {
            return $existing;
        }

        // Create new pending record
        $user = auth()->user();
        $createData = array_merge($searchAttributes, $additionalData, [
            $nameField => $nameValue,
            'status' => self::STATUS_PENDING,
            'is_active' => false,
            'created_by_user_id' => $user?->id,
            'created_by_entity' => $user?->entity?->name ?? $user?->created_by_entity,
            'creator_username' => $user?->username,
            'creator_entity_id' => $user?->entity_id,
        ]);

        // Filter out fields that are not in the model fillable or table schema
        $modelInstance = new $modelClass;
        if (method_exists($modelInstance, 'getFillable') && ! empty($modelInstance->getFillable())) {
            $fillable = array_merge($modelInstance->getFillable(), ['status', 'is_active', 'created_by_user_id']);
            $createData = array_intersect_key($createData, array_flip($fillable));
        }

        return $modelClass::create($createData);
    }

    /**
     * Approve a reference record
     */
    public static function approve(Model $record): bool
    {
        return $record->update([
            'status' => self::STATUS_APPROVED,
            'is_active' => true,
        ]);
    }

    /**
     * Reject a reference record
     */
    public static function reject(Model $record): bool
    {
        return $record->update([
            'status' => self::STATUS_REJECTED,
            'is_active' => false,
        ]);
    }

    /**
     * Programmatically append the 'غير ذلك' option to a collection/array of items for dropdowns
     */
    public static function appendOtherOption($items, string $idKey = 'id', string $nameKey = 'name')
    {
        if ($items instanceof Collection) {
            // Remove any existing physical 'غير ذلك' if present
            $filtered = $items->reject(function ($item) use ($nameKey) {
                $val = is_array($item) ? ($item[$nameKey] ?? '') : ($item->{$nameKey} ?? '');

                return trim($val) === 'غير ذلك';
            });

            // Create virtual item
            $otherObj = (object) [
                $idKey => 'other',
                $nameKey => 'غير ذلك',
            ];

            return $filtered->push($otherObj);
        }

        if (is_array($items)) {
            $items[] = [
                $idKey => 'other',
                $nameKey => 'غير ذلك',
            ];
        }

        return $items;
    }

    /**
     * Render status HTML badge
     */
    public static function renderStatusBadge($status): string
    {
        if ($status == self::STATUS_PENDING || $status === 'pending') {
            return '<span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fas fa-clock me-1"></i> بانتظار المراجعة</span>';
        }
        if ($status == self::STATUS_REJECTED || $status === 'rejected') {
            return '<span class="badge bg-secondary text-white fw-bold px-2 py-1"><i class="fas fa-ban me-1"></i> مرفوض</span>';
        }

        return '<span class="badge bg-success text-white fw-bold px-2 py-1"><i class="fas fa-check-circle me-1"></i> معتمد ونشط</span>';
    }
}
