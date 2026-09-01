<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValueChainMember extends Model
{
    protected $fillable = [
        'value_chain_id',
        'name',
        'role',
        'governorate_id',
        'directorate_id',
        'phone',
        'is_active',
        'created_by',
    ];

    public function valueChain()
    {
        return $this->belongsTo(ValueChain::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
