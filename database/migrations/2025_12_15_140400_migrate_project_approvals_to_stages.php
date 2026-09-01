<?php

use App\Models\Stage;
use App\Models\StageStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $stageMap = [
            'assembly' => 'assembly',
            'union' => 'union',
            'committee' => 'committee',
            'implementation' => 'implementation',
        ];

        $statusMap = [
            'pending' => 'pending',
            'approved' => 'approved',
            'rejected' => 'rejected',
            'need_action' => 'needs_revision',
            'needs_revision' => 'needs_revision',
            'on_hold' => 'on_hold',
        ];

        $projectApprovals = DB::table('project_approvals')
            ->whereNull('deleted_at')
            ->get();

        foreach ($projectApprovals as $approval) {
            $stageName = $stageMap[$approval->drop] ?? $approval->drop;
            $stage = Stage::where('code', $stageName)->first();

            $statusCode = $statusMap[$approval->status] ?? $approval->status;
            $stageStatus = StageStatus::where('code', $statusCode)->first();

            if ($stage && $stageStatus) {
                DB::table('project_approvals')
                    ->where('id', $approval->id)
                    ->update([
                        'stage_id' => $stage->id,
                        'stage_status_id' => $stageStatus->id,
                    ]);
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('project_approvals')
            ->update([
                'stage_id' => null,
                'stage_status_id' => null,
            ]);

        Schema::enableForeignKeyConstraints();
    }
};
