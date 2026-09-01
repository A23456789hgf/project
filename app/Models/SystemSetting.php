<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'category',
        'description',
    ];

    /**
     * Get a setting value by key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string|null  $category
     * @return self
     */
    public static function set($key, $value, $category = 'general')
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'category' => $category]
        );
    }
}
