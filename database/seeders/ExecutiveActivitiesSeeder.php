<?php

namespace Database\Seeders;

use App\Models\Executor;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExecutiveActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first project (or create one if none exists)
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

        $this->command->info("إنشاء أنشطة تنفيذية للمشروع: {$project->project_name}");

        // Create Executive Activities
        $activities = [
            [
                'activity_name' => 'التخطيط الاستراتيجي للمشروع',
                'activity_description' => 'وضع الخطة الاستراتيجية الشاملة للمشروع وتحديد الأهداف والمؤشرات',
                'activity_weight' => 25.00,
                'planned_start_date' => Carbon::now()->addDays(5),
                'planned_end_date' => Carbon::now()->addDays(20),
                'status' => 'not_started',
                'deliverables' => 'الخطة الاستراتيجية، مصفوفة الأهداف، مؤشرات الأداء',
                'success_criteria' => 'اعتماد الخطة من الجهات المعنية، تحديد المؤشرات بوضوح',
            ],
            [
                'activity_name' => 'تنفيذ الأنشطة الميدانية',
                'activity_description' => 'تنفيذ الأنشطة الميدانية وفقاً للخطة المعتمدة',
                'activity_weight' => 40.00,
                'planned_start_date' => Carbon::now()->addDays(21),
                'planned_end_date' => Carbon::now()->addDays(60),
                'status' => 'not_started',
                'deliverables' => 'تقارير التنفيذ، المخرجات الميدانية، قوائم المستفيدين',
                'success_criteria' => 'تحقيق 90% من الأهداف المحددة، رضا المستفيدين',
            ],
            [
                'activity_name' => 'المتابعة والتقييم',
                'activity_description' => 'متابعة تنفيذ الأنشطة وتقييم الأداء والنتائج',
                'activity_weight' => 20.00,
                'planned_start_date' => Carbon::now()->addDays(30),
                'planned_end_date' => Carbon::now()->addDays(70),
                'status' => 'not_started',
                'deliverables' => 'تقارير المتابعة، تقرير التقييم النهائي، التوصيات',
                'success_criteria' => 'تقديم تقارير دورية، تحقيق معايير الجودة',
            ],
            [
                'activity_name' => 'إعداد التقرير النهائي',
                'activity_description' => 'إعداد التقرير النهائي الشامل للمشروع وتوثيق النتائج',
                'activity_weight' => 15.00,
                'planned_start_date' => Carbon::now()->addDays(65),
                'planned_end_date' => Carbon::now()->addDays(75),
                'status' => 'not_started',
                'deliverables' => 'التقرير النهائي، ملف المشروع الكامل، قاعدة البيانات',
                'success_criteria' => 'اكتمال التوثيق، اعتماد التقرير النهائي',
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

        $this->command->info('تم إنشاء الأنشطة التنفيذية بنجاح!');
    }

    private function createActionsForActivity($activity, $executors)
    {
        $actions = [
            [
                'action_name' => 'مراجعة المتطلبات',
                'action_description' => 'مراجعة وتحليل متطلبات النشاط',
                'action_weight' => 30.00,
                'priority' => 'high',
                'status' => 'not_started',
            ],
            [
                'action_name' => 'التنفيذ الفعلي',
                'action_description' => 'تنفيذ الإجراءات المطلوبة',
                'action_weight' => 50.00,
                'priority' => 'critical',
                'status' => 'not_started',
            ],
            [
                'action_name' => 'المراجعة والتدقيق',
                'action_description' => 'مراجعة النتائج والتأكد من الجودة',
                'action_weight' => 20.00,
                'priority' => 'medium',
                'status' => 'not_started',
            ],
        ];

        foreach ($actions as $index => $actionData) {
            $actionData['project_id'] = $activity->project_id;
            $actionData['planned_start_date'] = $activity->planned_start_date->addDays($index * 3);
            $actionData['planned_end_date'] = $actionData['planned_start_date']->copy()->addDays(5);
            $actionData['verification_method'] = 'مراجعة المخرجات والتأكد من مطابقتها للمعايير';

            $action = $activity->actions()->create($actionData);
        }
    }

    private function createSchedulesForActivity($activity)
    {
        $schedules = [
            [
                'milestone_name' => 'بداية النشاط',
                'milestone_description' => 'نقطة البداية الرسمية للنشاط',
                'scheduled_date' => $activity->planned_start_date,
                'milestone_type' => 'start',
                'status' => 'pending',
            ],
            [
                'milestone_name' => 'نقطة تحقق منتصف المدة',
                'milestone_description' => 'مراجعة التقدم في منتصف فترة التنفيذ',
                'scheduled_date' => $activity->planned_start_date->copy()->addDays(
                    $activity->planned_start_date->diffInDays($activity->planned_end_date) / 2
                ),
                'milestone_type' => 'checkpoint',
                'status' => 'pending',
            ],
            [
                'milestone_name' => 'إنجاز النشاط',
                'milestone_description' => 'الإنجاز الكامل للنشاط',
                'scheduled_date' => $activity->planned_end_date,
                'milestone_type' => 'completion',
                'status' => 'pending',
            ],
        ];

        foreach ($schedules as $scheduleData) {
            $scheduleData['project_id'] = $activity->project_id;
            $activity->schedules()->create($scheduleData);
        }
    }

    private function createAssignmentsForActivity($activity, $executors)
    {
        $executor = $executors->random();

        $assignment = $activity->assignments()->create([
            'project_id' => $activity->project_id,
            'executor_id' => $executor->id,
            'assigned_person_name' => 'مدير المشروع',
            'role' => 'مسؤول التنفيذ',
            'responsibilities' => 'الإشراف على تنفيذ النشاط وضمان تحقيق الأهداف',
            'assignment_date' => Carbon::now(),
            'expected_completion_date' => $activity->planned_end_date,
            'workload_percentage' => 100.00,
            'assignment_status' => 'assigned',
        ]);
    }

    private function createProgressForActivity($activity)
    {
        // Create a sample progress report
        $progress = $activity->progress()->create([
            'project_id' => $activity->project_id,
            'report_date' => Carbon::now(),
            'progress_percentage' => 0.00,
            'achievements' => 'تم البدء في التخطيط الأولي للنشاط',
            'challenges' => 'لا توجد تحديات حتى الآن',
            'next_steps' => 'البدء في تنفيذ الإجراءات المخططة',
            'status' => 'on_track',
            'status_reason' => 'النشاط في بداية التنفيذ وفقاً للجدول الزمني',
            'reported_by' => 'مدير المشروع',
        ]);
    }
}
