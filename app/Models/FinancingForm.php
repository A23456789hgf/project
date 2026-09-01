<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancingForm extends Model
{
    use HasFactory;

    protected $table = 'financing_forms';

    protected $fillable = ['name', 'is_active'];

    public function subForms()
    {
        return $this->hasMany(SubFinancingForm::class);
    }
}
