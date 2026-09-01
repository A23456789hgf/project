<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralTopic extends Model
{
    protected $fillable = [
        'topic_number',
        'subject',
        'master_titles',
        'created_by',
        'entity_id',
        'status',
    ];

    protected $casts = [
        'master_titles' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entity()
    {
        return $this->belongsTo(InternalEntity::class, 'entity_id');
    }

    public function activities()
    {
        return $this->hasMany(ReferralActivity::class, 'topic_id');
    }

    public function latestActivity()
    {
        return $this->hasOne(ReferralActivity::class, 'topic_id')->latestOfMany();
    }

    public static function generateTopicNumber()
    {
        $year = date('Y');
        $prefix = 'REF'.$year;

        $lastTopic = self::where('topic_number', 'LIKE', $prefix.'%')
            ->orderBy('topic_number', 'desc')
            ->first();

        if ($lastTopic) {
            $lastNum = (int) substr($lastTopic->topic_number, 7);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix.str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }
}
