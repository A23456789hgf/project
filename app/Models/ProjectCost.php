<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCost extends Model
{
    use HasFactory;

    protected $table = 'project_costs';

    protected $fillable = [
        'project_id',
        'total_cost',
        'spent_amount',
        'remaining_amount',
        'hijri_year',
        'year_type',
        'approval_date_hijri',
        'approval_year_gregorian',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
