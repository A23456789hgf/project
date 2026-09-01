<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use Auditable, HasCreatorTracking, HasFactory;
    use HasCreatorTracking;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'full_access',
        'module_scopes',
        'module_geo_scopes',
        'entity_display_scope',
        'entity_add_scope',
        'creator_username',
        'creator_entity_id',
    ];

    protected $casts = [
        'module_scopes' => 'array',
        'module_geo_scopes' => 'array',
        'entity_display_scope' => 'array',
        'entity_add_scope' => 'array',
        'is_active' => 'boolean',
        'full_access' => 'boolean',
    ];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    public function permissionScopes()
    {
        return $this->hasMany(RolePermissionScope::class, 'role_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
