<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErpNextReportService
{
    protected $baseUrl;

    protected $apiKey;

    protected $apiSecret;

    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('external.erpnext.base_url');
        $this->apiKey = config('external.erpnext.user');
        $this->apiSecret = config('external.erpnext.pass');
        $this->timeout = config('external.erpnext.timeout', 30);
    }

    /**
     * Get a report from ERPNext.
     */
    public function getReport(string $reportName, array $filters = [], bool $useCache = false): array
    {
        $cacheKey = 'erpnext_report_'.md5($reportName.json_encode($filters));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                    // Default for Http::post with array is JSON (application/json)
                ])
                ->post($this->baseUrl.'/api/method/frappe.desk.query_report.run', [
                    'report_name' => $reportName,
                    'filters' => $filters,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $result = [
                    'success' => true,
                    'columns' => $data['message']['columns'] ?? [],
                    'result' => $data['message']['result'] ?? [],
                ];

                if ($useCache) {
                    Cache::put($cacheKey, $result, now()->addMinutes(15));
                }

                return $result;
            }

            // Handle known error responses
            $status = $response->status();
            $errorMessage = 'حدث خطأ غير متوقع أثناء الاتصال بنظام ERPNext.';

            $errorData = $response->json();

            if (is_array($errorData) && isset($errorData['_server_messages'])) {
                try {
                    $messages = json_decode($errorData['_server_messages'], true);
                    if (is_array($messages) && count($messages) > 0) {
                        $firstMsg = json_decode($messages[0], true);
                        if (is_array($firstMsg) && isset($firstMsg['message'])) {
                            $errorMessage = strip_tags($firstMsg['message']);
                        }
                    }
                } catch (\Exception $ex) {
                }
            }

            if ($status === 401 || $status === 403) {
                $errorMessage = 'غير مصرح لك بالوصول. يرجى التحقق من صلاحيات API.';
            } elseif ($status === 417) {
                // Try to extract the validation error message from response
                if (isset($errorData['exception'])) {
                    $exception = $errorData['exception'];
                    // e.g., "frappe.exceptions.ValidationError: From Date and To Date are mandatory"
                    $parts = explode(':', $exception, 2);
                    $errorMessage = isset($parts[1]) ? trim($parts[1]) : $exception;
                } else {
                    $errorMessage = 'يوجد خطأ في الفلاتر المدخلة (Validation Error).';
                }
            } elseif ($status === 404) {
                $errorMessage = 'التقرير المطلوب غير موجود.';
            } elseif ($status >= 500 && $errorMessage === 'حدث خطأ غير متوقع أثناء الاتصال بنظام ERPNext.') {
                $errorMessage = 'خطأ داخلي في خادم ERPNext.';
            }

            Log::error("ERPNext API Error [{$status}]", [
                'report' => $reportName,
                'filters' => $filters,
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $status,
            ];

        } catch (ConnectionException $e) {
            Log::error('ERPNext Connection Refused', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'تعذر الاتصال بخادم ERPNext. الخادم قد يكون معطلاً أو غير متاح.',
            ];
        } catch (\Exception $e) {
            Log::error('ERPNext General Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'حدث خطأ داخلي أثناء معالجة التقرير: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get list of companies from ERPNext.
     */
    public function getCompanies(): array
    {
        $cacheKey = 'erpnext_companies_list';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl.'/api/resource/Company', [
                    'limit_page_length' => 100, // fetch up to 100 companies
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $companies = [];

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $company) {
                        if (isset($company['name'])) {
                            $companies[] = $company['name'];
                        }
                    }
                }

                Cache::put($cacheKey, $companies, now()->addHours(24)); // cache for 24 hours

                return $companies;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('ERPNext Get Companies Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get list of child companies for a given parent company recursively.
     */
    public function getChildCompanies(string $parentCompany): array
    {
        $cacheKey = 'erpnext_child_companies_'.md5($parentCompany);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl.'/api/resource/Company', [
                    'limit_page_length' => 100,
                    'filters' => json_encode([['parent_company', '=', $parentCompany]]),
                    'fields' => '["name"]',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $children = [];

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $comp) {
                        if (isset($comp['name'])) {
                            $children[] = $comp['name'];
                            // Recursively fetch children's children
                            $subChildren = $this->getChildCompanies($comp['name']);
                            $children = array_merge($children, $subChildren);
                        }
                    }
                }

                Cache::put($cacheKey, $children, now()->addHours(24));

                return $children;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('ERPNext Get Child Companies Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get list of open projects from ERPNext.
     */
    public function getProjects(?string $company = null): array
    {
        $cacheKey = 'erpnext_projects_list_'.md5($company ?? 'all');

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $filters = [
                ['Project', 'status', '=', 'Open'],
            ];

            if ($company) {
                $filters[] = ['Project', 'company', '=', $company];
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl.'/api/resource/Project', [
                    'limit_page_length' => 500, // fetch up to 500 projects
                    'filters' => json_encode($filters),
                    'fields' => '["name","project_name"]',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $projects = [];

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $project) {
                        if (isset($project['name'])) {
                            $projects[] = [
                                'name' => $project['name'],
                                'project_name' => $project['project_name'] ?? $project['name'],
                            ];
                        }
                    }
                }

                Cache::put($cacheKey, $projects, now()->addHours(24)); // cache for 24 hours

                return $projects;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('ERPNext Get Projects Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get Accounts and their Account Numbers map.
     */
    public function getAccountsMap(): array
    {
        $cacheKey = 'erpnext_accounts_map';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl.'/api/resource/Account', [
                    'limit_page_length' => 5000,
                    'fields' => '["name","account_number"]',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $accounts = [];

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $acc) {
                        $accounts[$acc['name']] = $acc['account_number'] ?? '';
                    }
                }

                Cache::put($cacheKey, $accounts, now()->addHours(24));

                return $accounts;
            }

            return [];
        } catch (\Exception $e) {
            Log::error('ERPNext Get Accounts Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get P&L summary.
     */
    public function getPlSummary(array $filters): array
    {
        try {
            // As per the user's JS example, it uses URLSearchParams so it's a GET request with JSON stringified filters
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl.'/api/method/get_pl_summary', [
                    'filters' => json_encode($filters),
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['message']['report_summary'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('ERPNext Get PL Summary Error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get P&L Expense Type Summary.
     */
    public function getPlExpenseSummary(array $filters, bool $useCache = false): array
    {
        $cacheKey = 'erpnext_pl_expense_summary_'.md5(json_encode($filters));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl.'/api/method/get_pl_expense_type_summary', [
                    'filters' => json_encode($filters),
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Frappe usually wraps custom method responses in "message"
                $responseData = $data['message'] ?? $data;

                $result = [
                    'success' => true,
                    'columns' => $responseData['columns'] ?? [],
                    'result' => $responseData['result'] ?? [],
                    'account_to_expense_type' => $responseData['account_to_expense_type'] ?? [],
                    'report_summary' => $responseData['report_summary'] ?? [],
                ];

                if ($useCache) {
                    Cache::put($cacheKey, $result, now()->addMinutes(15));
                }

                return $result;
            }

            $status = $response->status();
            $errorMessage = 'حدث خطأ غير متوقع أثناء جلب ملخص النفقات.';

            $errorData = $response->json();

            if (is_array($errorData) && isset($errorData['_server_messages'])) {
                try {
                    $messages = json_decode($errorData['_server_messages'], true);
                    if (is_array($messages) && count($messages) > 0) {
                        $firstMsg = json_decode($messages[0], true);
                        if (is_array($firstMsg) && isset($firstMsg['message'])) {
                            $errorMessage = strip_tags($firstMsg['message']);
                        }
                    }
                } catch (\Exception $ex) {
                }
            }

            if ($status === 401 || $status === 403) {
                $errorMessage = 'غير مصرح لك بالوصول. يرجى التحقق من صلاحيات API.';
            } elseif ($status === 417) {
                if (isset($errorData['exception'])) {
                    $exception = $errorData['exception'];
                    $parts = explode(':', $exception, 2);
                    $errorMessage = isset($parts[1]) ? trim($parts[1]) : $exception;
                } else {
                    $errorMessage = 'يوجد خطأ في الفلاتر المدخلة (Validation Error).';
                }
            }

            Log::error("ERPNext PL Expense Summary Error [{$status}]", [
                'filters' => $filters,
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $status,
            ];

        } catch (\Exception $e) {
            Log::error('ERPNext Get PL Expense Summary Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'حدث خطأ داخلي أثناء معالجة التقرير: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get General Ledger Report transactions.
     */
    public function getGlReport(array $filters, bool $useCache = false): array
    {
        $cacheKey = 'erpnext_gl_report_'.md5(json_encode($filters));

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'token '.$this->apiKey.':'.$this->apiSecret,
                    'Accept' => 'application/json',
                ])
                ->get($this->baseUrl.'/api/method/get_gl_report', [
                    'filters' => json_encode($filters),
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $responseData = $data['message'] ?? $data;

                $result = [
                    'success' => true,
                    'result' => $responseData['result'] ?? [],
                ];

                if ($useCache) {
                    Cache::put($cacheKey, $result, now()->addMinutes(15));
                }

                return $result;
            }

            $status = $response->status();
            $errorMessage = 'حدث خطأ غير متوقع أثناء جلب العمليات المالية.';

            $errorData = $response->json();

            if (is_array($errorData) && isset($errorData['_server_messages'])) {
                try {
                    $messages = json_decode($errorData['_server_messages'], true);
                    if (is_array($messages) && count($messages) > 0) {
                        $firstMsg = json_decode($messages[0], true);
                        if (is_array($firstMsg) && isset($firstMsg['message'])) {
                            $errorMessage = strip_tags($firstMsg['message']);
                        }
                    }
                } catch (\Exception $ex) {
                }
            }

            if ($status === 401 || $status === 403) {
                $errorMessage = 'غير مصرح لك بالوصول. يرجى التحقق من صلاحيات API.';
            } elseif ($status === 417) {
                if (isset($errorData['exception'])) {
                    $exception = $errorData['exception'];
                    $parts = explode(':', $exception, 2);
                    $errorMessage = isset($parts[1]) ? trim($parts[1]) : $exception;
                } else {
                    $errorMessage = 'يوجد خطأ في الفلاتر المدخلة (Validation Error).';
                }
            }

            Log::error("ERPNext GL Report Error [{$status}]", [
                'filters' => $filters,
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $status,
            ];

        } catch (\Exception $e) {
            Log::error('ERPNext Get GL Report Exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'حدث خطأ داخلي أثناء معالجة التقرير: '.$e->getMessage(),
            ];
        }
    }
}
