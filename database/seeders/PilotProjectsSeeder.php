<?php

namespace Database\Seeders;

use App\Models\PreliminaryActivity;
use App\Models\PreliminaryCost;
use App\Models\PreliminaryFinancialSummary;
use App\Models\PreliminaryProcedure;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectDetail;
use ArPHP\I18N\Arabic;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PilotProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 5 comprehensive pilot projects with activities, procedures, and costs
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🚀 Starting Pilot Projects Seeder...');

        DB::transaction(function () {
            $projectNames = [
                'تطوير البنية التحتية الرقمية',
                'برنامج التدريب والتأهيل المهني',
                'مبادرة الرعاية الصحية المجتمعية',
                'مشروع التنمية الزراعية المستدامة',
                'برنامج دعم ريادة الأعمال الشبابية',
            ];

            $activityTemplates = [
                ['التخطيط والإعداد', 'إجراء الدراسات الأولية', 'تحديد المتطلبات', 'إعداد الخطة التنفيذية', 'تشكيل فريق العمل', 'إعداد الموازنة التقديرية'],
                ['التنفيذ والتطبيق', 'البدء في التنفيذ', 'متابعة التقدم', 'حل المشكلات', 'تطبيق المعايير', 'ضمان الجودة'],
                ['المتابعة والتقييم', 'جمع البيانات', 'تحليل الأداء', 'إعداد التقارير', 'تقييم النتائج', 'توثيق الدروس المستفادة'],
                ['التطوير والتحسين', 'تحديد فرص التحسين', 'تطبيق التحسينات', 'اختبار التحديثات', 'تدريب الفريق', 'نشر التحديثات'],
                ['الإنجاز والتسليم', 'مراجعة نهائية', 'إعداد التسليمات', 'تدريب المستخدمين', 'نقل المعرفة', 'إغلاق المشروع'],
            ];

            for ($i = 1; $i <= 5; $i++) {
                $startDate = Carbon::now()->addDays($i * 7);
                $endDate = $startDate->copy()->addMonths(6);

                $this->command->info("📋 Creating Project {$i}/5: {$projectNames[$i - 1]}");

                // Create Project
                $project = Project::create([
                    'project_name' => $projectNames[$i - 1],
                    'start_date_gregorian' => $startDate->toDateString(),
                    'end_date_gregorian' => $endDate->toDateString(),
                    'start_date_hijri' => $this->gregorianToHijri($startDate->toDateString()),
                    'end_date_hijri' => $this->gregorianToHijri($endDate->toDateString()),
                    'number_of_beneficiaries' => rand(500, 2000),
                    'main_directives' => "تنفيذ {$projectNames[$i - 1]} وفقاً للمعايير والمواصفات المعتمدة لتحقيق الأهداف الاستراتيجية للجمعية",
                    'subdirectives' => 'التركيز على الجودة والاستدامة وتحقيق أقصى استفادة للمستفيدين المستهدفين',
                    'priority' => $i <= 2 ? 'عالية' : ($i <= 4 ? 'متوسطة' : 'عادية'),
                    'status' => 'draft',
                    'created_by_user_id' => 1,
                    'created_by_entity' => 'الجمعية الخيرية للاختبار',
                    'updated_by_user_id' => 1,
                    'updated_by_entity' => 'الجمعية الخيرية للاختبار',
                ]);

                // Create Project Detail
                ProjectDetail::create([
                    'project_id' => $project->id,
                    'description' => "وصف تفصيلي لـ {$projectNames[$i - 1]} يتضمن الأهداف والنتائج المتوقعة والمخرجات الرئيسية",
                    'objectives' => 'تحقيق التنمية المستدامة وتحسين جودة الحياة للمستفيدين',
                    'expected_results' => 'نتائج ملموسة ومستدامة تساهم في تحقيق رؤية الجمعية',
                ]);

                // Create Project Cost
                $totalProjectCost = rand(500000, 2000000);
                ProjectCost::create([
                    'project_id' => $project->id,
                    'total_cost' => $totalProjectCost,
                    'currency' => 'SAR',
                ]);

                $this->command->info("  ✓ Project created with ID: {$project->id}");

                // Create 5 Activities
                $activityCostShare = $totalProjectCost / 5;

                for ($j = 1; $j <= 5; $j++) {
                    $activityName = $activityTemplates[$j - 1][0];

                    $activity = PreliminaryActivity::create([
                        'project_id' => $project->id,
                        'name' => $activityName,
                        'weight' => 20.00, // 5 activities * 20 = 100%
                    ]);

                    $this->command->info("    📌 Activity {$j}: {$activityName}");

                    // Create 5 Procedures for each Activity
                    $procedureCostShare = $activityCostShare / 5;
                    $daysPerProcedure = floor($endDate->diffInDays($startDate) / 25); // 25 procedures total

                    for ($k = 1; $k <= 5; $k++) {
                        $procedureName = $activityTemplates[$j - 1][$k];

                        // Calculate procedure dates
                        $procedureIndex = ($j - 1) * 5 + ($k - 1);
                        $procStart = $startDate->copy()->addDays($procedureIndex * $daysPerProcedure);
                        $procEnd = $procStart->copy()->addDays($daysPerProcedure - 1);

                        // Ensure procedure end doesn't exceed project end
                        if ($procEnd->gt($endDate)) {
                            $procEnd = $endDate->copy();
                        }

                        $procedure = PreliminaryProcedure::create([
                            'project_id' => $project->id,
                            'activity_id' => $activity->id,
                            'procedure_name' => $procedureName,
                            'weight' => 20.00, // 5 procedures * 20 = 100% of activity
                            'start_date' => $procStart->toDateString(),
                            'end_date' => $procEnd->toDateString(),
                            'verification_means' => 'تقرير متابعة، مستندات إثبات، محاضر اجتماعات، صور فوتوغرافية',
                        ]);

                        // Create Procedure Cost
                        $procedureCost = round($procedureCostShare + rand(-10000, 10000), 2);
                        PreliminaryCost::create([
                            'project_id' => $project->id,
                            'activity_id' => $activity->id,
                            'procedure_id' => $procedure->id,
                            'cost_amount' => $procedureCost,
                            'cost_type' => 'تشغيلية',
                            'description' => "تكلفة تنفيذ {$procedureName}",
                        ]);

                        // Create Financial Summary for Procedure
                        PreliminaryFinancialSummary::create([
                            'project_id' => $project->id,
                            'activity_id' => $activity->id,
                            'procedure_id' => $procedure->id,
                            'total_budget' => $procedureCost,
                            'spent_amount' => 0,
                            'remaining_amount' => $procedureCost,
                        ]);

                        $this->command->info("      ✓ Procedure {$k}: {$procedureName}");
                    }
                }

                $this->command->info("  ✅ Project {$i} completed with 5 activities and 25 procedures\n");
            }
        });

        $this->command->info('✨ Successfully created 5 pilot projects!');
        $this->command->info('📊 Total: 5 projects, 25 activities, 125 procedures');
        $this->command->info('🎯 All projects are ready for testing and submission');
    }

    /**
     * Convert Gregorian date to Hijri date
     *
     * @param  string  $gregorianDate
     * @return string|null
     */
    private function gregorianToHijri($gregorianDate)
    {
        try {
            $date = Carbon::parse($gregorianDate);

            // Try to use ArPHP library if available
            if (class_exists('\ArPHP\I18N\Arabic')) {
                $arPHP = new Arabic;

                return $arPHP->date('Y/m/d', $date->timestamp, 1);
            }

            // Fallback to Gregorian format
            return $date->format('Y/m/d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
