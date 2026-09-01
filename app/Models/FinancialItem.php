<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialItem extends Model
{
    use HasActiveScope, HasFactory;

    protected $fillable = [
        'status',
        'code',
        'name',
        'is_active',
    ];

    /**
     * العلاقة مع التكاليف المبدئية
     * لكل بند مالي عدة تكاليف.
     */
    public function preliminaryCosts()
    {
        return $this->hasMany(PreliminaryCost::class, 'financial_item_id');
    }

    /**
     * العلاقة مع الملخصات المالية المبدئية
     */
    public function preliminaryFinancialSummaries()
    {
        return $this->hasMany(PreliminaryFinancialSummary::class, 'financial_item_id');
    }
}
