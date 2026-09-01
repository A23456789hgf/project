<?php

namespace App\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the trait and register model observers.
     */
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::logEvent($model, 'Created');
        });

        static::updated(function (Model $model) {
            self::logEvent($model, 'Updated');
        });

        static::deleted(function (Model $model) {
            self::logEvent($model, 'Deleted');
        });
    }

    /**
     * Log the model event.
     */
    protected static function logEvent(Model $model, string $action)
    {
        $oldValues = $action === 'Updated' ? array_intersect_key($model->getOriginal(), $model->getChanges()) : null;
        $newValues = $action === 'Updated' ? $model->getChanges() : ($action === 'Created' ? $model->toArray() : null);

        // Don't log sensitive timestamps if not needed, but keep for now
        unset($oldValues['updated_at'], $newValues['updated_at']);

        $description = "$action ".class_basename($model)." (ID: {$model->id})";

        AuditLogService::log(
            action: strtolower($action),
            description: $description,
            modelType: get_class($model),
            modelId: $model->id,
            oldValues: $oldValues,
            newValues: $newValues
        );
    }
}
