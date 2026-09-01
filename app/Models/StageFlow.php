<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_stage_id',
        'to_stage_id',
        'trigger_status',
        'conditions',
        'auto_create_next',
        'is_active',
        'order',
    ];

    protected $casts = [
        'auto_create_next' => 'boolean',
        'is_active' => 'boolean',
        'conditions' => 'json',
    ];

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStage($query, Stage $stage)
    {
        return $query->where('from_stage_id', $stage->id);
    }

    public function scopeByTrigger($query, string $status)
    {
        return $query->where('trigger_status', $status);
    }

    public function scopeWithAutoCreate($query)
    {
        return $query->where('auto_create_next', true);
    }

    public function shouldAutoCreateNext(): bool
    {
        return $this->auto_create_next === true;
    }

    public function conditionsMet(?array $context = null): bool
    {
        if (! $this->conditions || empty($this->conditions)) {
            return true;
        }

        if (! $context) {
            return true;
        }

        foreach ($this->conditions as $key => $value) {
            if (! isset($context[$key]) || $context[$key] !== $value) {
                return false;
            }
        }

        return true;
    }
}
