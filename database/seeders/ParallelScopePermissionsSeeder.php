<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class ParallelScopePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Adds view-parallel permissions for modules that support entity-based data filtering.
     * This permission allows users to view data from sibling entities under the same parent.
     */
    public function run()
    {
        $modules = [
            'projects' => 'عرض مشاريع الجهات الموازية',
            'project-requests' => 'عرض طلبات الجهات الموازية',
            'approvals' => 'عرض موافقات الجهات الموازية',
            'referrals' => 'عرض إحالات الجهات الموازية',
            'executive-activities' => 'عرض أنشطة الجهات الموازية',
            'programs' => 'عرض برامج الجهات الموازية',
            'domains' => 'عرض مجالات الجهات الموازية',
            'interventions' => 'عرض تدخلات الجهات الموازية',
        ];

        foreach ($modules as $module => $name) {
            Permission::firstOrCreate(
                ['slug' => "{$module}.view-parallel"],
                [
                    'name' => $name,
                    'description' => "القدرة على عرض بيانات {$module} من الجهات الموازية (الجهات التي تشترك في نفس الجهة الأم)",
                    'module' => $module,
                ]
            );
        }

        $this->command->info('Parallel scope permissions created successfully!');
    }
}
