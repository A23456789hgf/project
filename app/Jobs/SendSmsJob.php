<?php

namespace App\Jobs;

use App\Services\SmppSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;

    public $mobileNo;

    public $message;

    public $eventName;

    public $sentByUserId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $mobileNo, $message, $eventName = null, $sentByUserId = null)
    {
        $this->userId = $userId;
        $this->mobileNo = $mobileNo;
        $this->message = $message;
        $this->eventName = $eventName;
        $this->sentByUserId = $sentByUserId;
    }

    /**
     * Execute the job.
     */
    public function handle(SmppSmsService $smsService): void
    {
        $smsService->sendSmsSync($this->userId, $this->mobileNo, $this->message, $this->eventName, $this->sentByUserId);
    }
}
