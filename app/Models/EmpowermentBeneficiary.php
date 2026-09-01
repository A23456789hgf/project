<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpowermentBeneficiary extends Model
{
    use HasDomainScope, HasFactory;

    protected $table = 'empowerment_beneficiaries';

    protected $fillable = [
        'geographic_scope_id',
        'administrative_scope_id',
        'empowerment_project_id',
        'first_name',
        'middle_name',
        'last_name',
        'id_number',
        'governorate_id',
        'directorate_id',
        'sub_area_id',
        'village_id',
        'loan_amount',
        'repayment_method',
        'installments_count',
    ];

    /** اسم كامل محسوب من الأجزاء الثلاثة */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    /** العلاقة بمشروع التمكين */
    public function empowermentProject(): BelongsTo
    {
        return $this->belongsTo(EmpowermentProject::class);
    }

    /** العلاقة بالمحافظة */
    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    /** العلاقة بالمديرية */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    /** العلاقة بالعزلة */
    public function subArea(): BelongsTo
    {
        return $this->belongsTo(SubArea::class);
    }

    /** العلاقة بالقرية */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
