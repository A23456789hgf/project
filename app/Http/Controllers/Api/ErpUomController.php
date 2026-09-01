<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErpUomController extends Controller
{
    /**
     * Get all UOM records from ERPNext
     */
    public function index(): JsonResponse
    {
        try {
            $config = config('external.erpnext', []);
            $erpUrl = rtrim($config['base_url'] ?? '', '/');
            $apiKey = $config['user'] ?? '';
            $apiSecret = $config['pass'] ?? '';

            // Validate environment variables
            if (! $erpUrl || ! $apiKey || ! $apiSecret) {
                Log::error('ERPNext configuration missing in environment variables (external.erpnext)');

                return response()->json([
                    'status' => 'error',
                    'message' => 'ERPNext configuration is incomplete. Please check environment variables.',
                ], 500);
            }

            $allUomRecords = [];
            $pageLimit = 100; // Increased limit for better performance
            $start = 0;
            $hasMore = true;

            // Pagination loop to fetch all records
            while ($hasMore) {
                $response = Http::withHeaders([
                    'Authorization' => 'token '.$apiKey.':'.$apiSecret,
                    'Accept' => 'application/json',
                ])->get(
                    $erpUrl.'/api/resource/UOM',
                    [
                        'fields' => json_encode(['name', 'uom_name']),
                        'limit_start' => $start,
                        'limit_page_length' => $pageLimit,
                        'order_by' => 'modified DESC',
                    ]
                );

                if ($response->failed()) {
                    Log::error('ERPNext UOM API request failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url' => $erpUrl.'/api/resource/UOM',
                        'params' => [
                            'limit_start' => $start,
                            'limit_page_length' => $pageLimit,
                        ],
                    ]);

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to connect to ERPNext API. Please check the configuration.',
                        'details' => 'HTTP Status: '.$response->status(),
                    ], 500);
                }

                $responseData = $response->json();
                $currentRecords = $responseData['data'] ?? [];

                if (! empty($currentRecords)) {
                    $allUomRecords = array_merge($allUomRecords, $currentRecords);
                    $start += $pageLimit;

                    // Check if we got fewer records than the page limit (last page)
                    if (count($currentRecords) < $pageLimit) {
                        $hasMore = false;
                    }
                } else {
                    $hasMore = false;
                }
            }

            $totalCount = count($allUomRecords);

            // Log success
            Log::info('ERP UOM data fetched successfully', [
                'total_records' => $totalCount,
                'source' => 'ERPNext',
                'endpoint' => 'UOM',
            ]);

            // If no records found
            if ($totalCount === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No UOM records found in ERPNext.',
                    'count' => 0,
                    'data' => [],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'UOM records retrieved successfully from ERPNext.',
                'count' => $totalCount,
                'data' => $allUomRecords,
            ]);

        } catch (\Throwable $e) {
            Log::error('ERP UOM Exception occurred', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while fetching UOM records.',
                'details' => env('APP_DEBUG') ? $e->getMessage() : 'Please contact system administrator.',
            ], 500);
        }
    }
}
