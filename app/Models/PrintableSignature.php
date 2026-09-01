<?php

namespace App\Models;

use App\Traits\HasCreatorTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintableSignature extends Model
{
    use HasCreatorTracking, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'job_title',
        'signature_path',
        'is_active',
        'display_order',
    ];

    /**
     * Scope a query to only include active signatures.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the full signature image URL.
     */
    public function getSignatureUrlAttribute()
    {
        return $this->signature_path ? asset('storage/'.$this->signature_path) : null;
    }
}
