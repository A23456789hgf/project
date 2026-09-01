<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubFinancingForm extends Model
{
    use HasFactory;

    protected $table = 'sub_financing_forms';

    protected $fillable = [
        'financing_form_id',
        'name',
        'is_active',
    ];

    // علاقة الانتماء إلى نموذج التمويل الأساسي
    public function financingForm()
    {
        return $this->belongsTo(FinancingForm::class);
    }
}
