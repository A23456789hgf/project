<?php

// app/Models/MainGuide.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainGuide extends Model
{
    use HasFactory;

    protected $fillable = ['main_guide', 'is_active'];
}
