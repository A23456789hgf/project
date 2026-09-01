<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestDescendMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_descend_id',
        'name',
        'entity_id',
        'work',
        'daily_amount',
        'duration',
        'total',
    ];

    public function requestDescend()
    {
        return $this->belongsTo(RequestDescend::class, 'request_descend_id');
    }

    public function entity()
    {
        return $this->belongsTo(InternalEntity::class, 'entity_id');
    }
}
