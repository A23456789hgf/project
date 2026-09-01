<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermissionScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'permission_id',
        'geographic_scope_id',
        'administrative_scope_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get the role associated with this scope.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the permission associated with this scope.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get the geographic scope (Governorate).
     */
    public function geographicScope(): BelongsTo
    {
        return $this->belongsTo(Governorate::class, 'geographic_scope_id');
    }

    /**
     * Get the administrative scope (Internal Entity).
     */
    public function administrativeScope(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'administrative_scope_id');
    }
}
