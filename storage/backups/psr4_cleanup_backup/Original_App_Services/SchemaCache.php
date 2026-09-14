<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class SchemaCache
{
    /**
     * Cache for Schema::hasColumn results.
     */
    protected static array $hasColumnCache = [];

    /**
     * Determine if the given table has the given column, utilizing an in-memory cache.
     */
    public static function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";

        if (! array_key_exists($key, self::$hasColumnCache)) {
            self::$hasColumnCache[$key] = Schema::hasColumn($table, $column);
        }

        return self::$hasColumnCache[$key];
    }
}
