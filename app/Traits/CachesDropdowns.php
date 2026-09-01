<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

trait CachesDropdowns
{
    /**
     * Get all records from cache or database.
     *
     * @return Collection
     */
    public static function getCachedAll()
    {
        return Cache::rememberForever(static::class.'_all', function () {
            return static::all();
        });
    }

    /**
     * Boot the trait to clear cache on save/delete.
     */
    public static function bootCachesDropdowns()
    {
        static::saved(function ($model) {
            Cache::forget(static::class.'_all');
        });
        static::deleted(function ($model) {
            Cache::forget(static::class.'_all');
        });
    }
}
