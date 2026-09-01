<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Intervention;
use App\Models\Program;
use App\Models\Project;
use App\Models\Subdomain;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first available user (or create one if none exists)
        $user = User::first();
        if (! $user) {
            $this->command->error('No users found. Please create a user first.');

            return;
        }

        // Get first available program, domain, subdomain, intervention
        $program = Program::first();
        $domain = Domain::first();
        $subdomain = Subdomain::first();
        $intervention = Intervention::first();

        $projects = [
            [
                'name' => 'مشروع تطوير البنية التحتية الزراعية',
                'description' => 'مشروع لتحسين وتطوير البنية التحتية الزراعية في المناطق الريفية',
                'objectives' => 'تحسين الإنتاجية الزراعية وزيادة الدخل للمزارعين',
            ],
            [
                'name' => 'مشروع تحديث أنظمة الري',
                'description' => 'تحديث وتطوير أنظمة الري الحديثة لتوفير المياه',
                'objectives' => 'ترشيد استهلاك المياه وزيادة كفاءة الري',
            ],
            [
                'name' => 'مشروع دعم الصيادين',
                'description' => 'برنامج دعم شامل للصيادين وتطوير قطاع الثروة السمكية',
                'objectives' => 'تحسين دخل الصيادين وتطوير قطاع الصيد',
            ],
            [
                'name' => 'مشروع التحول الرقمي الزراعي',
                'description' => 'تطبيق التقنيات الحديثة في القطاع الزراعي',
                'objectives' => 'رقمنة العمليات الزراعية وتحسين الإنتاجية',
            ],
            [
                'name' => 'مشروع تطوير الموارد المائية',
                'description' => 'إدارة وتطوير الموارد المائية بشكل مستدام',
                'objectives' => 'الحفاظ على الموارد المائية وتحسين استخدامها',
            ],
        ];

        $this->command->info('Creating 5 test projects...');

        foreach ($projects as $index => $projectData) {
            $project = Project::create([
                'form_number' => 'TEST-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'project_name' => $projectData['name'],
                'program_id' => $program?->id,
                'domain_id' => $domain?->id,
                'subdomain_id' => $subdomain?->id,
                'intervention_id' => $intervention?->id,
                'created_by_user_id' => $user->id,
                'created_by_entity' => $user->entity?->name ?? $user->department ?? 'مركز تقنية المعلومات',
                'status' => 'draft',
                'start_date_gregorian' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'end_date_gregorian' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);

            $this->command->info("✅ Created: {$projectData['name']} (ID: {$project->id})");
        }

        $this->command->info('✅ Successfully created 5 test projects!');
        $this->command->info('You can now test the approval workflow with these projects.');
    }
}
