<?php

namespace App\Jobs;

use App\Services\SmppSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;

    public $mobileNo;

    public $message;

    public $eventName;

    public $authUserId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $mobileNo, $message, $eventName = null, $authUserId = null)
    {
        $this->userId = $userId;
        $this->mobileNo = $mobileNo;
        $this->message = $message;
        $this->eventName = $eventName;
        $this->authUserId = $authUserId;
    }

    /**
     * Execute the job.
     */
    public function handle(SmppSmsService $smsService): void
    {
        if ($this->authUserId) {
            Auth::loginUsingId($this->authUserId);
        }
        $smsService->sendSmsSync($this->userId, $this->mobileNo, $this->message, $this->eventName);
    }
}
