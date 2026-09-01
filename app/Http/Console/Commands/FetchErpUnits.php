<?php

namespace App\Console\Commands;

use App\Services\ErpNextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchErpUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erp:fetch-units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all UOM records from ERPNext with pagination';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting fetch from ERPNext...');

        try {
            $erp = new ErpNextService;

            // Use * for limit_page_length to fetch all records at once
            $response = $erp->get('/api/resource/UOM', [
                'fields' => json_encode(['*']),
                'limit_page_length' => '*',
            ]);

            if (! $response->successful()) {
                $this->error('Error fetching ERPNext data: '.$response->status());
                Log::error('ERPNext UOM fetch failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => '/api/resource/UOM',
                ]);

                return;
            }

            $allData = $response->json('data') ?? [];

            $this->info('Fetch completed. Total UOM records: '.count($allData));
            Log::info('Total UOM fetched via command', ['count' => count($allData)]);

            // هنا يمكنك حفظ البيانات في قاعدة البيانات إذا أردت
            // \App\Models\Unit::upsert($allData, ['name'], [...fields]);

        } catch (\Exception $e) {
            $this->error('Exception: '.$e->getMessage());
            Log::error('ERPNext UOM fetch exception', ['message' => $e->getMessage()]);
        }
    }
}
