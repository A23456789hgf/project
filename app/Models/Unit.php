<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'unit_name',
    ];

    /**
     * Get the unit name (alias for unit_name)
     */
    public function getNameAttribute()
    {
        return $this->unit_name;
    }

    protected $dates = ['deleted_at'];
}
