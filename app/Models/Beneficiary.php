<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    // الحقول المسموح بإدخالها بشكل جماعي
    protected $fillable = [
        'name',
        'is_active',
        // أضف المزيد من الحقول هنا مثل:
        // 'gender',
        // 'birthdate',
        // 'address',
    ];
}
