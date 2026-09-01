<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use ArPHP\I18N\Arabic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Memoir extends Model
{
    use Auditable, HasCreatorTracking, HasDomainScope, HasFactory, SoftDeletes;

    protected $fillable = [
        'memoir_number',
        'to',
        'subject',
        'body',
        'gregorian_date',
        'hijri_date',
        'creator_username',
        'creator_entity_id',
        'created_by',
        'entity_id',
        'geographic_scope_id',
        'administrative_scope_id',
        'project_id',
    ];

    protected $casts = [
        'gregorian_date' => 'date',
    ];

    /**
     * Boot method to generate memoir number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($memoir) {
            // Auto-generate number if empty
            if (empty($memoir->memoir_number)) {
                $memoir->memoir_number = $memoir->generateAutoNumber();
            }
        });
    }

    /**
     * Generate sequential memoir number.
     * Format: MEM-YYYY-#### (using Hijri year)
     */
    public function generateAutoNumber()
    {
        try {
            $arPHP = new Arabic;
            $hijriYear = $arPHP->date('Y', time(), 1);

            $prefix = 'MEM-'.$hijriYear.'-';

            $lastMemoir = self::withoutGlobalScopes()->withTrashed()
                ->where('memoir_number', 'like', $prefix.'%')
                ->orderBy('memoir_number', 'desc')
                ->first();

            if ($lastMemoir) {
                $lastSequence = (int) substr($lastMemoir->memoir_number, -4);
                $nextSequence = $lastSequence + 1;
            } else {
                $nextSequence = 1;
            }

            return $prefix.str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            Log::error('Failed to generate memoir number: '.$e->getMessage());

            return 'MEM-TEMP-'.date('Ymd-His');
        }
    }

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_username', 'user_id');
    }

    public function entity()
    {
        return $this->belongsTo(InternalEntity::class, 'creator_entity_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
