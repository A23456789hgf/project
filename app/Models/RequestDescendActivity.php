<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestDescendActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_descend_id',
        'activity',
        'expected_output',
        'from_date',
        'to_date',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function requestDescend()
    {
        return $this->belongsTo(RequestDescend::class);
    }

    // Accessor for Duration
    public function getDurationAttribute()
    {
        if ($this->from_date && $this->to_date) {
            return Carbon::parse($this->from_date)->diffInDays(Carbon::parse($this->to_date));
        }

        return 0;
    }
}
