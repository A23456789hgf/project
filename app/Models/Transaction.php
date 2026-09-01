<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'transaction_type',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'amount_change',
        'description',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'amount_change' => 'decimal:2',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        if ($modelId) {
            $query->where('model_id', $modelId);
        }

        return $query;
    }

    // Accessors
    public function getTransactionTypeLabelAttribute()
    {
        return match ($this->transaction_type) {
            'create' => 'إنشاء',
            'update' => 'تحديث',
            'delete' => 'حذف',
            default => $this->transaction_type
        };
    }

    public function getFormattedAmountChangeAttribute()
    {
        if (! $this->amount_change) {
            return null;
        }

        $prefix = $this->amount_change > 0 ? '+' : '';

        return $prefix.number_format($this->amount_change, 2).' ر.س';
    }
}
