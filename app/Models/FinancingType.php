<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancingType extends Model
{
    use HasFactory;

    // تحديد اسم الجدول (اختياري إذا كان الاسم يتبع القاعدة)
    protected $table = 'financing_types';

    // تحديد الحقول التي يُسمح بتعبئتها جماعياً
    protected $fillable = [
        'status', 'name', 'is_active'];
}
