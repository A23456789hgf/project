<?php

namespace App\Console\Commands;

use App\Models\InternalEntity;
use App\Services\FrappeAPIService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class SyncEntitiesToFrappe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erpnext:sync-entities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all internal entities to ERPNext (Frappe) keeping the hierarchy';

    /**
     * Execute the console command.
     */
    public function handle(FrappeAPIService $frappeService)
    {
        Model::preventLazyLoading(false);
        $this->info('Starting entity synchronization to ERPNext...');

        // نجلب جميع الجهات، يفضل البدء من الآباء (الجذور) ثم الأبناء لتسريع العملية وتقليل التكرار
        $entities = InternalEntity::orderByRaw('parent_id IS NULL DESC')->get();
        $total = $entities->count();

        $this->output->progressStart($total);

        $successCount = 0;
        $errorCount = 0;

        foreach ($entities as $entity) {
            try {
                $frappeService->ensureParentHierarchyExists($entity);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("\nFailed to sync entity: {$entity->name}. Error: {$e->getMessage()}");
                $errorCount++;
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info('Synchronization completed!');
        $this->info("Successfully synced: {$successCount}");
        if ($errorCount > 0) {
            $this->warn("Failed: {$errorCount}");
        }
    }
}
