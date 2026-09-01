<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Association extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'governorate_id',
        'district_id',
        'is_active',
    ];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class, 'district_id');
    }
}
