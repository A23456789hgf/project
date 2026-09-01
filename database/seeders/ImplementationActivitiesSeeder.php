<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\ImplementationActionAssignee;
use App\Models\ImplementationActionCost;
use App\Models\ImplementationActivity;
use App\Models\ImplementationActivityAction;
use App\Models\ImplementationFinancialSummary;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ImplementationActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This seeder creates sample implementation activities for testing
        // It assumes you have at least one project and one entity in the database

        $project = Project::first();
        $entity = Entity::first();

        if (! $project || ! $entity) {
            $this->command->info('No project or entity found. Please create a project and entity first.');

            return;
        }

        // Create implementation activities
        $activity1 = ImplementationActivity::create([
            'project_id' => $project->id,
            'activity_name' => 'تطوير النظام الإلكتروني',
            'activity_weight' => 60.00,
            'outputs' => 'نظام إلكتروني متكامل لإدارة المشاريع',
            'risks' => 'تأخير في التطوير، مشاكل تقنية',
        ]);

        $activity2 = ImplementationActivity::create([
            'project_id' => $project->id,
            'activity_name' => 'التدريب والتأهيل',
            'activity_weight' => 40.00,
            'outputs' => 'فريق مدرب ومؤهل لاستخدام النظام',
            'risks' => 'عدم توفر المدربين المؤهلين',
        ]);

        // Create actions for activity 1
        $action1 = ImplementationActivityAction::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity1->id,
            'action_name' => 'تحليل المتطلبات',
            'action_weight' => 30.00,
            'start_date' => '2024-01-01',
            'completion_date' => '2024-01-15',
            'verification_means' => 'وثيقة المتطلبات المعتمدة',
        ]);

        $action2 = ImplementationActivityAction::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity1->id,
            'action_name' => 'التطوير والبرمجة',
            'action_weight' => 30.00,
            'start_date' => '2024-01-16',
            'completion_date' => '2024-03-15',
            'verification_means' => 'النظام المطور والمختبر',
        ]);

        // Create actions for activity 2
        $action3 = ImplementationActivityAction::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity2->id,
            'action_name' => 'إعداد المواد التدريبية',
            'action_weight' => 20.00,
            'start_date' => '2024-02-01',
            'completion_date' => '2024-02-15',
            'verification_means' => 'المواد التدريبية المعتمدة',
        ]);

        $action4 = ImplementationActivityAction::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity2->id,
            'action_name' => 'تنفيذ التدريب',
            'action_weight' => 20.00,
            'start_date' => '2024-03-16',
            'completion_date' => '2024-03-30',
            'verification_means' => 'شهادات التدريب وتقارير الحضور',
        ]);

        // Create costs for actions
        ImplementationActionCost::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity1->id,
            'implementation_activity_action_id' => $action1->id,
            'financial_item' => 'استشارات تحليل المتطلبات',
            'unit' => 'يوم',
            'unit_price' => 500.00,
            'quantity' => 10,
            'total' => 5000.00,
        ]);

        ImplementationActionCost::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity1->id,
            'implementation_activity_action_id' => $action2->id,
            'financial_item' => 'تطوير وبرمجة النظام',
            'unit' => 'شهر',
            'unit_price' => 15000.00,
            'quantity' => 2,
            'total' => 30000.00,
        ]);

        ImplementationActionCost::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity2->id,
            'implementation_activity_action_id' => $action3->id,
            'financial_item' => 'إعداد المواد التدريبية',
            'unit' => 'مجموعة',
            'unit_price' => 2000.00,
            'quantity' => 1,
            'total' => 2000.00,
        ]);

        ImplementationActionCost::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity2->id,
            'implementation_activity_action_id' => $action4->id,
            'financial_item' => 'تنفيذ التدريب',
            'unit' => 'دورة',
            'unit_price' => 3000.00,
            'quantity' => 2,
            'total' => 6000.00,
        ]);

        // Create assignees
        ImplementationActionAssignee::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity1->id,
            'implementation_activity_action_id' => $action1->id,
            'entity_id' => $entity->id,
            'name' => 'محلل الأنظمة الرئيسي',
            'task' => 'تحليل وتوثيق متطلبات النظام',
        ]);

        ImplementationActionAssignee::create([
            'project_id' => $project->id,
            'implementation_activity_id' => $activity1->id,
            'implementation_activity_action_id' => $action2->id,
            'entity_id' => $entity->id,
            'name' => 'فريق التطوير',
            'task' => 'تطوير وبرمجة النظام حسب المتطلبات',
        ]);

        // Generate financial summary
        ImplementationFinancialSummary::createFromActionCosts($project->id);

        $this->command->info('Implementation activities seeded successfully!');
    }
}
