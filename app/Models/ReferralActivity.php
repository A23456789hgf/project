<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralActivity extends Model
{
    protected $fillable = [
        'topic_id',
        'referral_number',
        'from_user_id',
        'from_department_id',
        'to_department_id',
        'referral_text',
        'attachments',
        'referral_date',
        'response_text',
        'response_date',
        'response_attachments',
        'responded_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'response_attachments' => 'array',
        'referral_date' => 'datetime',
        'response_date' => 'datetime',
    ];

    public function topic()
    {
        return $this->belongsTo(ReferralTopic::class, 'topic_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function fromDepartment()
    {
        return $this->belongsTo(InternalEntity::class, 'from_department_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(InternalEntity::class, 'to_department_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public static function generateActivityNumber($topicNumber)
    {
        $lastActivity = self::where('topic_id', function ($query) use ($topicNumber) {
            $query->select('id')->from('referral_topics')->where('topic_number', $topicNumber);
        })->count();

        return $topicNumber.'-'.($lastActivity + 1);
    }
}
