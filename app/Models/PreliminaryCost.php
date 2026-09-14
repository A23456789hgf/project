<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreliminaryCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'activity_id',
        'procedure_id',
        'financial_item_id',
        'unit_id',
        'amount',
        'quantity',
        'total',
    ];

    /**
     * المشروع المرتبط بهذا البند.
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
     * البند المالي المرتبط.
     */
    public function financialItem()
    {
        return $this->belongsTo(FinancialItem::class, 'financial_item_id');
    }

    /**
     * العلاقة مع الملخصات المالية.
     */
    public function financialSummaries()
    {
        return $this->hasMany(PreliminaryFinancialSummary::class, 'cost_id');
    }

    /**
     * العلاقة مع الوحدة.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id')->withTrashed();
    }

    public function getUnitPriceAttribute()
    {
        return $this->attributes['amount'] ?? null;
    }

    public function setUnitPriceAttribute($value): void
    {
        $this->attributes['amount'] = $value;
    }

    public function getTotalCostAttribute()
    {
        return $this->attributes['total'] ?? null;
    }

    public function setTotalCostAttribute($value): void
    {
        $this->attributes['total'] = $value;
    }
}
