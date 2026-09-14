<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectEntity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncProjectEntities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:sync-entities {--project= : Specific project ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all supervising, implementing, and participating entities to project_entities table for all projects';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $projectId = $this->option('project');

        $query = Project::with([
            'supervisingAuthorities.internalEntity', 'supervisingAuthorities.authority',
            'implementingEntities.internalEntity', 'implementingEntities.authority',
            'participatingEntities.internalEntity', 'participatingEntities.authority',
        ]);

        if ($projectId) {
            $query->where('id', $projectId);
        }

        $totalCount = (clone $query)->count();
        $this->info("Found {$totalCount} projects. Starting sync...");

        $bar = $this->output->createProgressBar($totalCount);
        $totalAdded = 0;

        DB::beginTransaction();
        try {
            $query->chunkById(200, function ($projects) use (&$totalAdded, $bar) {
                $projectIds = $projects->pluck('id')->all();
                ProjectEntity::whereIn('project_id', $projectIds)->delete();

                $records = [];
                $now = now()->toDateTimeString();

                foreach ($projects as $project) {
                    $entities = collect();

                    foreach ($project->supervisingAuthorities as $entity) {
                        $name = $entity->authority_type == 'internal'
                            ? optional($entity->internalEntity)->name
                            : optional($entity->authority)->agency_name;
                        if ($name) {
                            $entities->push($name);
                        }
                    }

                    foreach ($project->implementingEntities as $entity) {
                        $name = $entity->authority_type == 'internal'
                            ? optional($entity->internalEntity)->name
                            : optional($entity->authority)->agency_name;
                        if ($name) {
                            $entities->push($name);
                        }
                    }

                    foreach ($project->participatingEntities as $entity) {
                        $name = $entity->authority_type == 'internal'
                            ? optional($entity->internalEntity)->name
                            : optional($entity->authority)->agency_name;
                        if ($name) {
                            $entities->push($name);
                        }
                    }

                    $uniqueNames = $entities->unique()->values();

                    foreach ($uniqueNames as $name) {
                        $records[] = [
                            'project_id' => $project->id,
                            'entity_name' => $name,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    $bar->advance();
                }

                if (! empty($records)) {
                    ProjectEntity::insert($records);
                    $totalAdded += count($records);
                }
            });

            DB::commit();

            $bar->finish();
            $this->newLine();
            $this->info("Sync completed! Added {$totalAdded} entities across {$totalCount} projects.");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: '.$e->getMessage());

            return 1;
        }
    }
}
