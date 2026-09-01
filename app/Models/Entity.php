<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasActiveScope, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_active',
        'entity_type_id',
        'entity_father_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the father entity.
     */
    public function father()
    {
        return $this->belongsTo(Entity::class, 'entity_father_id');
    }

    /**
     * Get the child entities.
     */
    public function children()
    {
        return $this->hasMany(Entity::class, 'entity_father_id');
    }

    /**
     * Scope a query to only include active entities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
