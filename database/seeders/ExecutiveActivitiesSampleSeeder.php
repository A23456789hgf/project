<?php

namespace Database\Seeders;

use App\Models\ExecutiveActivity;
use App\Models\Executor;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExecutiveActivitiesSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first project or create one if none exists
        $project = Project::first();

        if (! $project) {
            $this->command->info('لا توجد مشاريع في قاعدة البيانات. يرجى إنشاء مشروع أولاً.');

            return;
        }

        // Get or create executors
        $executors = Executor::take(3)->get();
        if ($executors->count() < 3) {
            $this->command->info('يجب وجود 3 جهات منفذة على الأقل في قاعدة البيانات.');

            return;
        }

        $this->command->info("إنشاء أنشطة تنفيذية نموذجية للمشروع: {$project->project_name}");

        // Create Executive Activities
        $activities = [
            [
                'activity_name' => 'التخطيط والإعداد',
                'activity_description' => 'مرحلة التخطيط الشامل للمشروع وإعداد الخطط التفصيلية والجداول الزمنية',
                'activity_weight' => 20.00,
                'planned_start_date' => Carbon::now()->addDays(5),
                'planned_end_date' => Carbon::now()->addDays(25),
                'status' => 'not_started',
                'deliverables' => 'خطة المشروع التفصيلية، الجداول الزمنية، خطة إدارة المخاطر',
                'success_criteria' => 'اعتماد جميع الخطط من الجهات المعنية',
            ],
            [
                'activity_name' => 'التنفيذ الأساسي',
                'activity_description' => 'تنفيذ الأنشطة الأساسية للمشروع وفقاً للخطة المعتمدة',
                'activity_weight' => 50.00,
                'planned_start_date' => Carbon::now()->addDays(26),
                'planned_end_date' => Carbon::now()->addDays(80),
                'status' => 'not_started',
                'deliverables' => 'المخرجات الأساسية للمشروع، التقارير الدورية',
                'success_criteria' => 'تحقيق 100% من المخرجات المطلوبة بالجودة المحددة',
            ],
            [
                'activity_name' => 'المراقبة والتقييم',
                'activity_description' => 'مراقبة تقدم المشروع وتقييم الأداء والنتائج',
                'activity_weight' => 15.00,
                'planned_start_date' => Carbon::now()->addDays(30),
                'planned_end_date' => Carbon::now()->addDays(85),
                'status' => 'not_started',
                'deliverables' => 'تقارير المراقبة، تقرير التقييم النهائي',
                'success_criteria' => 'تحقيق مؤشرات الأداء المحددة',
            ],
            [
                'activity_name' => 'الإغلاق والتسليم',
                'activity_description' => 'إغلاق المشروع وتسليم جميع المخرجات والوثائق',
                'activity_weight' => 15.00,
                'planned_start_date' => Carbon::now()->addDays(81),
                'planned_end_date' => Carbon::now()->addDays(90),
                'status' => 'not_started',
                'deliverables' => 'تقرير الإغلاق، الوثائق النهائية، تسليم المخرجات',
                'success_criteria' => 'قبول جميع المخرجات من الجهة المستفيدة',
            ],
        ];

        foreach ($activities as $activityData) {
            $activity = $project->executiveActivities()->create($activityData);
            $this->command->info("تم إنشاء النشاط: {$activity->activity_name}");

            // Create actions for each activity
            $this->createActionsForActivity($activity, $executors);

            // Create schedules for each activity
            $this->createSchedulesForActivity($activity);

            // Create assignments for each activity
            $this->createAssignmentsForActivity($activity, $executors);

            // Create progress reports for each activity
            $this->createProgressForActivity($activity);
        }

        $this->command->info('تم إنشاء الأنشطة التنفيذية النموذجية بنجاح!');
    }

    private function createActionsForActivity(ExecutiveActivity $activity, $executors)
    {
        $actionsData = [
            [
                'action_name' => 'مراجعة المتطلبات',
                'action_description' => 'مراجعة شاملة لجميع متطلبات النشاط',
                'action_weight' => 30.00,
                'priority' => 'high',
                'verification_method' => 'مراجعة الوثائق والاعتماد من المدير',
            ],
            [
                'action_name' => 'تحضير الموارد',
                'action_description' => 'تحضير جميع الموارد اللازمة للتنفيذ',
                'action_weight' => 40.00,
                'priority' => 'critical',
                'verification_method' => 'جرد الموارد والتأكد من توفرها',
            ],
            [
                'action_name' => 'التنفيذ والمتابعة',
                'action_description' => 'تنفيذ الأنشطة ومتابعة التقدم',
                'action_weight' => 30.00,
                'priority' => 'medium',
                'verification_method' => 'تقارير التقدم اليومية',
            ],
        ];

        $startDate = $activity->planned_start_date;
        $totalDays = $activity->planned_start_date->diffInDays($activity->planned_end_date);
        $daysPerAction = intval($totalDays / count($actionsData));

        foreach ($actionsData as $index => $actionData) {
            $actionStartDate = $startDate->copy()->addDays($index * $daysPerAction);
            $actionEndDate = $actionStartDate->copy()->addDays($daysPerAction - 1);

            $action = $activity->actions()->create([
                'project_id' => $activity->project_id,
                'action_name' => $actionData['action_name'],
                'action_description' => $actionData['action_description'],
                'action_weight' => $actionData['action_weight'],
                'planned_start_date' => $actionStartDate,
                'planned_end_date' => $actionEndDate,
                'priority' => $actionData['priority'],
                'status' => 'not_started',
                'progress_percentage' => 0,
                'verification_method' => $actionData['verification_method'],
                'dependencies' => $index > 0 ? 'يعتمد على إنجاز الإجراء السابق' : null,
            ]);

            // Create assignment for this action
            $executor = $executors->random();
            $action->assignments()->create([
                'project_id' => $activity->project_id,
                'executive_activity_id' => $activity->id,
                'executor_id' => $executor->id,
                'assigned_person_name' => 'محمد أحمد '.($index + 1),
                'role' => 'مسؤول التنفيذ',
                'responsibilities' => 'تنفيذ الإجراء وفقاً للمعايير المحددة',
                'assignment_date' => Carbon::now(),
                'expected_completion_date' => $actionEndDate,
                'workload_percentage' => 100,
                'assignment_status' => 'assigned',
            ]);
        }
    }

    private function createSchedulesForActivity(ExecutiveActivity $activity)
    {
        $milestones = [
            [
                'milestone_name' => 'بداية النشاط',
                'milestone_type' => 'start',
                'scheduled_date' => $activity->planned_start_date,
                'status' => 'pending',
            ],
            [
                'milestone_name' => 'نقطة تحقق منتصف المدة',
                'milestone_type' => 'checkpoint',
                'scheduled_date' => $activity->planned_start_date->copy()->addDays(
                    intval($activity->planned_start_date->diffInDays($activity->planned_end_date) / 2)
                ),
                'status' => 'pending',
            ],
            [
                'milestone_name' => 'تسليم المخرجات',
                'milestone_type' => 'deliverable',
                'scheduled_date' => $activity->planned_end_date->copy()->subDays(2),
                'status' => 'pending',
            ],
            [
                'milestone_name' => 'إنجاز النشاط',
                'milestone_type' => 'completion',
                'scheduled_date' => $activity->planned_end_date,
                'status' => 'pending',
            ],
        ];

        foreach ($milestones as $milestoneData) {
            $activity->schedules()->create([
                'project_id' => $activity->project_id,
                'milestone_name' => $milestoneData['milestone_name'],
                'milestone_description' => 'معلم زمني مهم في تنفيذ النشاط',
                'scheduled_date' => $milestoneData['scheduled_date'],
                'milestone_type' => $milestoneData['milestone_type'],
                'status' => $milestoneData['status'],
                'deliverable_details' => $milestoneData['milestone_type'] === 'deliverable' ? 'تسليم المخرجات المطلوبة' : null,
                'completion_criteria' => 'تحقيق المعايير المحددة للمعلم',
            ]);
        }
    }

    private function createAssignmentsForActivity(ExecutiveActivity $activity, $executors)
    {
        $executor = $executors->random();

        $activity->assignments()->create([
            'project_id' => $activity->project_id,
            'executor_id' => $executor->id,
            'assigned_person_name' => 'أحمد محمد علي',
            'role' => 'مدير النشاط',
            'responsibilities' => 'الإشراف العام على تنفيذ النشاط وضمان تحقيق الأهداف',
            'assignment_date' => Carbon::now(),
            'expected_completion_date' => $activity->planned_end_date,
            'workload_percentage' => 100,
            'assignment_status' => 'assigned',
        ]);
    }

    private function createProgressForActivity(ExecutiveActivity $activity)
    {
        // Create initial progress report
        $activity->progress()->create([
            'project_id' => $activity->project_id,
            'report_date' => Carbon::now(),
            'progress_percentage' => 0,
            'achievements' => 'تم البدء في التخطيط للنشاط',
            'challenges' => 'لا توجد تحديات حتى الآن',
            'next_steps' => 'البدء في تنفيذ الخطوات الأولى',
            'status' => 'on_track',
            'status_reason' => 'النشاط في بداية التنفيذ',
            'support_needed' => 'لا يوجد دعم مطلوب حالياً',
            'reported_by' => 'مدير المشروع',
        ]);
    }
}
