<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\SmsLog;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SmppSmsService
{
    /**
     * Build an HTTP client that bypasses global proxy env vars for internal SMS host.
     */
    protected function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'timeout' => 15,
            'connect_timeout' => 5,
            'proxy' => [
                'http' => null,
                'https' => null,
                'no' => ['10.172.172.2', 'localhost', '127.0.0.1'],
            ],
            'curl' => [
                CURLOPT_PROXY => '',
                CURLOPT_NOPROXY => '*',
            ],
        ]);
    }

    public function login()
    {
        $apiURL = rtrim(config('sms.api_url'), '/').'/login';
        $client = $this->buildClient();

        try {
            $response = $client->request('POST', $apiURL, [
                'json' => [
                    'email' => config('sms.email'),
                    'username' => config('sms.email'),
                    'password' => config('sms.password'),
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['token'] ?? null;

        } catch (\Exception $e) {
            Log::error('SMPP Login Failed: '.$e->getMessage());

            return null;
        }
    }

    public function sendSMS($userId, $mobileNo, $message, $eventName = null)
    {
        $sentByUserId = Auth::id();
        SendSmsJob::dispatch($userId, $mobileNo, $message, $eventName, $sentByUserId);

        return ['status' => 'queued', 'phone' => $mobileNo, 'error' => null, 'response' => null];
    }

    public function sendSmsSync($userId, $mobileNo, $message, $eventName = null, $sentByUserId = null)
    {
        // Sanitize the phone number to remove any hidden characters or spaces
        $mobileNo = preg_replace('/[^0-9+]/', '', $mobileNo);

        if (empty($mobileNo)) {
            return $this->logSms($userId, $mobileNo, $message, 'failed', null, 'Phone number is empty', $eventName, $sentByUserId);
        }

        $token = $this->login();

        if (! $token) {
            return $this->logSms($userId, $mobileNo, $message, 'failed', null, 'Token not found', $eventName, $sentByUserId);
        }

        try {
            $client = $this->buildClient();
            $apiURL = rtrim(config('sms.api_url'), '/').'/send-sms';

            // مزود الخدمة الحالي يقتطع الرسالة إذا زادت عن 70 حرفاً، لذا يجب تقسيمها يدوياً.
            $chunks = [];
            $messageLength = mb_strlen($message, 'UTF-8');

            if ($messageLength > 70) {
                $limit = 64; // نترك 6 أحرف للترقيم مثل " (1/3) "
                for ($i = 0; $i < $messageLength; $i += $limit) {
                    $chunks[] = mb_substr($message, $i, $limit, 'UTF-8');
                }
            } else {
                $chunks[] = $message;
            }

            $lastResponse = null;
            $totalChunks = count($chunks);

            foreach ($chunks as $index => $chunk) {
                // إضافة ترقيم إذا كانت أكثر من جزء
                $chunkText = ($totalChunks > 1) ? '('.($index + 1).'/'.$totalChunks.') '.$chunk : $chunk;

                try {
                    $response = $client->post($apiURL, [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer '.$token,
                            'Connection' => 'close', // Force closing connection to prevent keep-alive errors
                        ],
                        'json' => [
                            'message' => $chunkText,
                            'phone' => $mobileNo,
                        ],
                        'timeout' => 15, // Set explicit timeout
                    ]);

                    $responseData = json_decode($response->getBody()->getContents(), true);
                    $lastResponse = $this->logSms($userId, $mobileNo, $chunkText, 'sent', $responseData, null, $eventName, $sentByUserId);
                } catch (\Exception $e) {
                    $errorData = ($e instanceof RequestException && $e->hasResponse())
                        ? json_decode($e->getResponse()->getBody()->getContents(), true)
                        : $e->getMessage();

                    $this->logSms($userId, $mobileNo, $chunkText, 'failed', $errorData, $e->getMessage(), $eventName, $sentByUserId);
                }

                // تأخير لتجنب الحظر من البوابة عند إرسال أجزاء متتالية لنفس الرقم
                if ($totalChunks > 1 && $index < $totalChunks - 1) {
                    sleep(4);
                }
            }

            return $lastResponse;

        } catch (\Exception $e) {
            $this->logSms($userId, $mobileNo, $message, 'failed', null, $e->getMessage(), $eventName, $sentByUserId);

            return null;
        }
    }

    /**
     * Send SMS to multiple users.
     *
     * @param  iterable  $users  Collection or array of User models
     * @param  string  $message
     * @param  string|null  $eventName
     * @return array Results array
     */
    public function sendToMultiple($users, $message, $eventName = null)
    {
        $results = [];
        $sentByUserId = Auth::id();
        
        foreach ($users as $user) {
            if ($user && ! empty($user->phone)) {
                $results[] = $this->sendSMS($user->id, $user->phone, $message, $eventName);
            } else {
                $results[] = $this->logSms($user->id ?? null, null, $message, 'failed', null, 'User has no phone number', $eventName, $sentByUserId);
            }
        }

        return $results;
    }

    protected function logSms($recipientId, $phone, $message, $status, $apiResponse, $errorMessage, $eventName, $sentByUserId = null)
    {
        SmsLog::create([
            'recipient_user_id' => $recipientId,
            'phone_number' => $phone ?? 'N/A',
            'message' => $message,
            'status' => $status,
            'api_response' => $apiResponse ? json_encode($apiResponse, JSON_UNESCAPED_UNICODE) : null,
            'error_message' => $errorMessage,
            'sent_by_user_id' => $sentByUserId,
            'event_name' => $eventName,
        ]);

        return [
            'status' => $status,
            'phone' => $phone,
            'error' => $errorMessage,
            'response' => $apiResponse,
        ];
    }
}
