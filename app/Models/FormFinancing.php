<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormFinancing extends Model
{
    // اسم الجدول في قاعدة البيانات
    protected $table = 'form_financings';

    // الحقول القابلة للتعبئة الجماعية
    protected $fillable = ['name', 'is_active'];

    // إذا لم تكن تستخدم timestamps (created_at, updated_at)
    // يمكنك إلغاء تفعيلها:
    // public $timestamps = false;

}
