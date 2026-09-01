<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreliminaryFinancialSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'activity_id',
        'procedure_id',
        'cost_id',
        'financial_item_id',
        'aggregated_total',
    ];

    /**
     * المشروع المرتبط.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * النشاط المرتبط.
     */
    public function activity()
    {
        return $this->belongsTo(PreliminaryActivity::class);
    }

    /**
     * الإجراء المرتبط.
     */
    public function procedure()
    {
        return $this->belongsTo(PreliminaryProcedure::class);
    }

    /**
     * التكلفة المبدئية المرتبطة.
     */
    public function cost()
    {
        return $this->belongsTo(PreliminaryCost::class, 'cost_id');
    }

    /**
     * البند المالي المرتبط.
     */
    public function financialItem()
    {
        return $this->belongsTo(FinancialItem::class, 'financial_item_id');
    }
}
