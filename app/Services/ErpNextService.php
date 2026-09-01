<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErpNextService
{
    protected string $baseUrl;

    protected string $user;

    protected string $pass;

    protected int $timeout;

    protected string $authType;

    public function __construct()
    {
        $config = config('external.erpnext', []);

        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->user = (string) ($config['user'] ?? '');
        $this->pass = (string) ($config['pass'] ?? '');
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->authType = (string) ($config['auth'] ?? 'basic');

        if (empty($this->baseUrl) || empty($this->user) || empty($this->pass)) {
            Log::warning('ERPNext Service initialized with missing credentials. Please check your .env file.', [
                'base_url' => $this->baseUrl,
                'user' => $this->user ? 'set' : 'missing',
                'pass' => $this->pass ? 'set' : 'missing',
            ]);
        }
    }

    // هنا يتم تعريف الـ Basic Auth
    protected function client()
    {
        $http = Http::timeout($this->timeout)->acceptJson();

        if ($this->authType === 'token') {
            return $http->withHeaders([
                'Authorization' => "token {$this->user}:{$this->pass}",
            ]);
        }

        return $http->withBasicAuth($this->user, $this->pass);
    }

    // دالة GET
    public function get(string $uri, array $params = [])
    {
        Log::info('ERPNext GET Request', ['url' => $this->baseUrl.$uri, 'params' => $params]);

        return $this->client()->get($this->baseUrl.$uri, $params);
    }

    // دالة POST
    public function post(string $uri, array $data = [])
    {
        return $this->client()->post($this->baseUrl.$uri, $data);
    }
}
