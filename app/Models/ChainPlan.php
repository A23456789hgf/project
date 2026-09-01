<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChainPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'governorate_id',
        'directorate_id',
        'value_chain_id',
        'domain_id',

        'project_name',
        'activity_name',

        'indicator',
        'number',

        'value_chain_financing_type_id',

        'funding_source_id',

        'authority_id',

        'implementing_entity_id',
    ];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }

    public function valueChain()
    {
        return $this->belongsTo(ValueChain::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function financingType()
    {
        return $this->belongsTo(
            ValueChainFinancingType::class,
            'value_chain_financing_type_id'
        );
    }

    /**
     * مصدر التمويل
     */
    public function fundingEntity()
    {
        return $this->belongsTo(
            Authority::class,
            'funding_entity_id'
        );
    }

    /**
     * الجهة المنفذة
     */
    public function authority()
    {
        return $this->belongsTo(
            Authority::class,
            'authority_id'
        );
    }

    /**
     * الجهة المشرفة
     */
    public function implementingEntity()
    {
        return $this->belongsTo(
            Authority::class,
            'implementing_entity_id'
        );
    }

    // العلاقة بجهة التمويل (مصدر التمويل)
    public function fundingSource()
    {
        return $this->belongsTo(Authority::class, 'funding_source_id');
    }
}
