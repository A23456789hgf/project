<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    // اسم الجدول غير مطلوب تحديده هنا لأن Laravel يفرض الجمع التلقائي 'participants'

    protected $fillable = [
        'name',
        'is_active',
        'mother_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // علاقة الأم
    public function mother()
    {
        return $this->belongsTo(Mother::class);
    }
}
