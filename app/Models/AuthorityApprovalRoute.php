<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorityApprovalRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'authority_id',
        'destination_type',
        'destination_authority_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function authority()
    {
        return $this->belongsTo(Authority::class, 'authority_id');
    }

    public function destinationAuthority()
    {
        return $this->belongsTo(Authority::class, 'destination_authority_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
