<?php

namespace App\Console\Commands;

use App\Models\InternalEntity;
use App\Services\FrappeAPIService;
use Illuminate\Console\Command;

class TestSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyA = InternalEntity::firstOrCreate(
            ['name' => 'Company A'],
            [
                'entity_type' => 'Company',
                'entity_code' => 'CA',
                'is_active' => true,
                'creator_username' => 'system',
            ]
        );

        $companyB = InternalEntity::firstOrCreate(
            ['name' => 'Company B'],
            [
                'entity_type' => 'Company',
                'entity_code' => 'CB',
                'parent_id' => $companyA->id,
                'is_active' => true,
                'creator_username' => 'system',
            ]
        );

        $departmentC = InternalEntity::firstOrCreate(
            ['name' => 'Department C'],
            [
                'entity_type' => 'Department',
                'entity_code' => 'DC',
                'parent_id' => $companyB->id,
                'is_active' => true,
                'creator_username' => 'system',
            ]
        );

        $departmentD = InternalEntity::firstOrCreate(
            ['name' => 'Department D'],
            [
                'entity_type' => 'Department',
                'entity_code' => 'DD',
                'parent_id' => $departmentC->id,
                'is_active' => true,
                'creator_username' => 'system',
            ]
        );

        $service = app(FrappeAPIService::class);

        $this->info('Starting sync for Department D...');
        try {
            $service->ensureEntityHierarchyExists($departmentD);
            $this->info('Sync complete!');
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }

        $this->info('Database values after sync:');
        foreach ([$companyA, $companyB, $departmentC, $departmentD] as $entity) {
            $entity->refresh();
            $this->line("- {$entity->name}: ERP ID={$entity->erpnext_id}, ERP Type={$entity->erpnext_type}, ERP Parent={$entity->erpnext_parent_id}");
        }

        $this->info('Running sync again (should skip API calls due to caching)...');
        try {
            $service->ensureEntityHierarchyExists($departmentD);
            $this->info('Second sync complete!');
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
        }
    }
}
