<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    const TYPE_SIDEBAR = 'sidebar';

    const TYPE_PAGE = 'page';

    const TYPE_ACTION = 'action';

    const TYPE_BUTTON = 'button';

    const TYPE_ICON = 'icon';

    const TYPE_SCOPE = 'scope';

    const TYPE_SETTING = 'setting';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'type',
        'auto_registered',
        'controller_class',
        'controller_method',
    ];

    /**
     * Scope for a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the roles that have this permission.
     */
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'permission_id');
    }

    public function permissionScopes()
    {
        return $this->hasMany(RolePermissionScope::class, 'permission_id');
    }
}
