<?php

namespace App\Traits;

use App\Models\InternalEntity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Trait HasCreatorTracking
 *
 * Automatically tracks the username (user_id) and entity_id of the user who created the record.
 */
trait HasCreatorTracking
{
    /**
     * Boot the trait and register model observers.
     */
    protected static function bootHasCreatorTracking()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                $table = $model->getTable();
                $columns = Schema::getColumnListing($table);

                // Track username (user_id) as it appears on the user page
                if (in_array('creator_username', $columns) && empty($model->creator_username)) {
                    $model->creator_username = $user->user_id ?? $user->username;
                }

                // Track entity_id — use creator_entity_id as fallback for admin/legacy users
                $effectiveEntityId = $user->entity_id ?? $user->creator_entity_id;
                if (in_array('creator_entity_id', $columns) && empty($model->creator_entity_id)) {
                    $model->creator_entity_id = $effectiveEntityId;
                }

                // Also track the creator user ID for convenient lookups
                if (in_array('created_by_user_id', $columns) && empty($model->created_by_user_id)) {
                    $model->created_by_user_id = $user->id;
                }

                // Track entity name (string) for legacy resolution
                if (in_array('created_by_entity', $columns) && empty($model->created_by_entity) && $effectiveEntityId) {
                    $entityName = InternalEntity::withoutGlobalScopes()
                        ->where('id', $effectiveEntityId)
                        ->value('name');
                    if ($entityName) {
                        $model->created_by_entity = $entityName;
                    }
                }
            }
        });
    }

    /**
     * Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_username', 'user_id');
    }

    /**
     * Get the entity associated with the creator at the time of creation.
     */
    public function creatorEntity()
    {
        return $this->belongsTo(InternalEntity::class, 'creator_entity_id');
    }
}
