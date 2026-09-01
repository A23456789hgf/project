<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use App\Models\Authority;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAssociationFlows();
        $this->seedUnionFlows();
        $this->seedCommitteeFlows();
        $this->seedGeneralDirectorateFlows();
        $this->seedSectorFlows();
    }

    /**
     * Seed approval flows for Association
     */
    private function seedAssociationFlows(): void
    {
        $association = Authority::where('agency_name', 'Association')->first();

        if (! $association) {
            return;
        }

        ApprovalFlow::create([
            'authority_id' => $association->id,
            'step_order' => 1,
            'step_name' => 'Assembly Review',
            'description' => 'Initial review by assembly members to check project alignment with association goals',
            'is_active' => true,
        ]);

        ApprovalFlow::create([
            'authority_id' => $association->id,
            'step_order' => 2,
            'step_name' => 'Finance Review',
            'description' => 'Financial review to validate budget, funding sources, and financial feasibility',
            'is_active' => true,
        ]);

        ApprovalFlow::create([
            'authority_id' => $association->id,
            'step_order' => 3,
            'step_name' => 'Final Approval',
            'description' => 'Final approval by assembly board after all reviews are complete',
            'is_active' => true,
        ]);
    }

    /**
     * Seed approval flows for Union
     */
    private function seedUnionFlows(): void
    {
        $union = Authority::where('agency_name', 'Union')->first();

        if (! $union) {
            return;
        }

        ApprovalFlow::create([
            'authority_id' => $union->id,
            'step_order' => 1,
            'step_name' => 'Technical Review',
            'description' => 'Union technical team reviews project specifications and implementation plan',
            'is_active' => true,
        ]);

        ApprovalFlow::create([
            'authority_id' => $union->id,
            'step_order' => 2,
            'step_name' => 'Union Board Approval',
            'description' => 'Final approval by union board',
            'is_active' => true,
        ]);
    }

    /**
     * Seed approval flows for Committee
     */
    private function seedCommitteeFlows(): void
    {
        $committee = Authority::where('agency_name', 'Committee')->first();

        if (! $committee) {
            return;
        }

        ApprovalFlow::create([
            'authority_id' => $committee->id,
            'step_order' => 1,
            'step_name' => 'Committee Approval',
            'description' => 'Committee review and approval of project',
            'is_active' => true,
        ]);
    }

    /**
     * Seed approval flows for General Directorate
     */
    private function seedGeneralDirectorateFlows(): void
    {
        $gd = Authority::where('agency_name', 'General Directorate')->first();

        if (! $gd) {
            return;
        }

        ApprovalFlow::create([
            'authority_id' => $gd->id,
            'step_order' => 1,
            'step_name' => 'Initial Review',
            'description' => 'General Directorate initial review of project proposal',
            'is_active' => true,
        ]);

        ApprovalFlow::create([
            'authority_id' => $gd->id,
            'step_order' => 2,
            'step_name' => 'Director Approval',
            'description' => 'Approval by General Directorate Director',
            'is_active' => true,
        ]);
    }

    /**
     * Seed approval flows for Sector
     */
    private function seedSectorFlows(): void
    {
        $sector = Authority::where('agency_name', 'Sector')->first();

        if (! $sector) {
            return;
        }

        ApprovalFlow::create([
            'authority_id' => $sector->id,
            'step_order' => 1,
            'step_name' => 'Sector Review',
            'description' => 'Sector team review and assessment',
            'is_active' => true,
        ]);

        ApprovalFlow::create([
            'authority_id' => $sector->id,
            'step_order' => 2,
            'step_name' => 'Sector Approval',
            'description' => 'Final approval by sector management',
            'is_active' => true,
        ]);
    }
}
