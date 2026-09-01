<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_user_id',
        'phone_number',
        'message',
        'status',
        'api_response',
        'error_message',
        'sent_by_user_id',
        'event_name',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }
}
