<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestDescend extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'is_linked_to_project',
        'project_id',
        'needs',
        'reason_for_drop',
        'objective_of_drop',
        'priority',
        'status',
        'notes',
        'financial_status',
    ];

    protected $casts = [
        'is_linked_to_project' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function activities()
    {
        return $this->hasMany(RequestDescendActivity::class);
    }

    public function members()
    {
        return $this->hasMany(RequestDescendMember::class, 'request_descend_id');
    }
}
