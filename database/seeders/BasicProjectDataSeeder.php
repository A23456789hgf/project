<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Intervention;
use App\Models\Program;
use App\Models\Project;
use App\Models\Subdomain;
use Illuminate\Database\Seeder;

class BasicProjectDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create basic programs, domains, etc. if they don't exist
        $program = Program::firstOrCreate(['name' => 'برنامج تجريبي']);
        $domain = Domain::firstOrCreate(['name' => 'مجال تجريبي']);
        $subdomain = Subdomain::firstOrCreate([
            'name' => 'مجال فرعي تجريبي',
            'domain_id' => $domain->id,
        ]);
        $intervention = Intervention::firstOrCreate([
            'name' => 'تدخل تجريبي',
            'domain_id' => $domain->id,
            'subdomain_id' => $subdomain->id,
        ]);

        // Create a sample project
        $project = Project::firstOrCreate([
            'project_name' => 'مشروع تطوير نظام إدارة المشاريع',
            'program_id' => $program->id,
            'domain_id' => $domain->id,
            'subdomain_id' => $subdomain->id,
            'intervention_id' => $intervention->id,
            'start_date_gregorian' => '2024-01-01',
            'start_date_hijri' => '1445-06-20',
            'end_date_gregorian' => '2024-12-31',
            'end_date_hijri' => '1446-06-19',
            'number_of_beneficiaries' => 1000,
        ]);

        /*
        $entityType = \App\Models\EntityType::firstOrCreate(['name' => 'شركة تقنية']);
        $entity = \App\Models\Entity::firstOrCreate([
            'name' => 'شركة التطوير التقني',
            'entity_type_id' => $entityType->id,
        ]);
        */

        $this->command->info('تم إنشاء البيانات الأساسية بنجاح!');
        $this->command->info("معرف المشروع: {$project->id}");
        // $this->command->info("معرف الجهة: {$entity->id}");
    }
}
