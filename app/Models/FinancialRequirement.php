<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'requirement_name',
        'description',
        'total_amount',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
