<?php

namespace Database\Seeders;

use App\Models\Authority;
use App\Models\BeneficiaryGroup;
use App\Models\Directorate;
use App\Models\Domain;
use App\Models\ExecutiveActionCost;
use App\Models\ExecutiveActivity;
use App\Models\ExecutiveActivityAction;
use App\Models\FinancialItem;
use App\Models\FinancingType;
use App\Models\FundingSource;
use App\Models\Governorate;
use App\Models\Intervention;
use App\Models\MainObjective;
use App\Models\MainRouter;
use App\Models\ObjectiveResult;
use App\Models\PreliminaryActivity;
use App\Models\PreliminaryCost;
use App\Models\PreliminaryProcedure;
use App\Models\Priority;
use App\Models\Program;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectDetail;
use App\Models\ProjectFinancing;
use App\Models\ProjectImplementingEntity;
use App\Models\ProjectLocation;
use App\Models\ProjectRisk;
use App\Models\ProjectSupervisingAuthority;
use App\Models\ResultOutput;
use App\Models\SpecialObjective;
use App\Models\Subdomain;
use App\Models\SubRouter;
use App\Models\User;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompleteProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creating 5 complete projects with all data...');

        try {
            // Get required reference data
            $user = User::first();
            if (! $user) {
                $this->command->error('❌ No users found. Please run UserSeeder first.');

                return;
            }

            $program = Program::first();
            $domain = Domain::first();
            $subdomain = Subdomain::first();
            $intervention = Intervention::first();
            $priority = Priority::first();
            $mainRouter = MainRouter::first();
            $subRouter = SubRouter::first();
            $governorate = Governorate::first();
            $directorate = Directorate::first();
            $village = Village::first();
            $authority = Authority::first();
            $financialItem = FinancialItem::first();
            $fundingSource = FundingSource::first();
            $financingType = FinancingType::first();
            $beneficiaryGroup = BeneficiaryGroup::first();

            if (! $program || ! $domain) {
                $this->command->error('❌ Missing required reference data. Please run other seeders first.');

                return;
            }

            $projectTemplates = [
                [
                    'name' => 'مشروع تطوير البنية التحتية الزراعية',
                    'summary' => 'مشروع شامل لتطوير البنية التحتية الزراعية في المناطق الريفية',
                    'introduction' => 'يهدف هذا المشروع إلى تحسين البنية التحتية الزراعية من خلال توفير المعدات الحديثة وتطوير أنظمة الري',
                    'problem' => 'ضعف البنية التحتية الزراعية وقلة الإنتاجية',
                    'components' => 'توفير معدات زراعية، تطوير أنظمة الري، تدريب المزارعين',
                    'impact' => 'زيادة الإنتاجية الزراعية بنسبة 40% وتحسين دخل المزارعين',
                ],
                [
                    'name' => 'مشروع تحديث أنظمة الري الحديثة',
                    'summary' => 'تحديث وتطوير أنظمة الري لترشيد استهلاك المياه',
                    'introduction' => 'مشروع يهدف إلى تحديث أنظمة الري التقليدية بأنظمة حديثة موفرة للمياه',
                    'problem' => 'هدر المياه في أنظمة الري التقليدية',
                    'components' => 'تركيب أنظمة ري بالتنقيط، صيانة شبكات الري، تدريب فني',
                    'impact' => 'توفير 50% من استهلاك المياه وزيادة كفاءة الري',
                ],
                [
                    'name' => 'مشروع دعم الصيادين وتطوير الثروة السمكية',
                    'summary' => 'برنامج دعم شامل للصيادين وتطوير قطاع الثروة السمكية',
                    'introduction' => 'يستهدف المشروع دعم الصيادين وتطوير قطاع الصيد من خلال توفير المعدات والتدريب',
                    'problem' => 'ضعف الإمكانيات المتاحة للصيادين وقلة الإنتاج السمكي',
                    'components' => 'توفير قوارب صيد، معدات صيد حديثة، تدريب الصيادين',
                    'impact' => 'زيادة الإنتاج السمكي وتحسين دخل الصيادين بنسبة 35%',
                ],
                [
                    'name' => 'مشروع التحول الرقمي في القطاع الزراعي',
                    'summary' => 'تطبيق التقنيات الرقمية الحديثة في القطاع الزراعي',
                    'introduction' => 'مشروع رقمنة العمليات الزراعية وتطبيق تقنيات الزراعة الذكية',
                    'problem' => 'الاعتماد على الأساليب التقليدية وضعف استخدام التقنية',
                    'components' => 'نظام إدارة المزارع الإلكتروني، أجهزة استشعار، تطبيقات ذكية',
                    'impact' => 'تحسين الكفاءة الإنتاجية وتقليل الهدر بنسبة 30%',
                ],
                [
                    'name' => 'مشروع تطوير وإدارة الموارد المائية',
                    'summary' => 'إدارة وتطوير الموارد المائية بشكل مستدام',
                    'introduction' => 'مشروع يهدف إلى الحفاظ على الموارد المائية وتحسين إدارتها',
                    'problem' => 'استنزاف الموارد المائية وسوء الإدارة',
                    'components' => 'بناء سدود تخزينية، أنظمة رصد المياه، برامج توعية',
                    'impact' => 'الحفاظ على 60% من الموارد المائية وتحسين الاستخدام',
                ],
            ];

            foreach ($projectTemplates as $index => $template) {
                DB::beginTransaction();

                try {
                    $this->command->info("\n📝 Creating Project ".($index + 1).": {$template['name']}");

                    // 1. Create main project
                    $project = Project::create([
                        'form_number' => 'PRJ-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT).'-'.date('Y'),
                        'project_name' => $template['name'],
                        'program_id' => $program->id,
                        'domain_id' => $domain->id,
                        'subdomain_id' => $subdomain?->id,
                        'intervention_id' => $intervention?->id,
                        'priority_id' => $priority?->id,
                        'main_router_id' => $mainRouter?->id,
                        'sub_router_id' => $subRouter?->id,
                        'created_by_user_id' => $user->id,
                        'created_by_entity' => $user->entity?->name ?? $user->department ?? 'مركز تقنية المعلومات',
                        'status' => 'draft',
                        'start_date_gregorian' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                        'start_date_hijri' => '1446-07-01',
                        'end_date_gregorian' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                        'end_date_hijri' => '1447-06-30',
                        'number_of_beneficiaries' => rand(500, 2000),
                    ]);

                    // 2. Project Details
                    ProjectDetail::create([
                        'project_id' => $project->id,
                        'is_part_of_plan' => rand(0, 1),
                        'project_summary' => $template['summary'],
                        'project_introduction' => $template['introduction'],
                        'problem_and_justification' => $template['problem'],
                        'project_components' => $template['components'],
                        'expected_impact' => $template['impact'],
                    ]);

                    // 3. Project Location (if available)
                    if ($governorate && $directorate) {
                        ProjectLocation::create([
                            'project_id' => $project->id,
                            'governorate_id' => $governorate->id,
                            'directorate_id' => $directorate->id,
                            'sub_area_id' => $village?->id,
                        ]);
                    }

                    // 4. Main Objective
                    MainObjective::create([
                        'project_id' => $project->id,
                        'objective' => 'تحقيق التنمية المستدامة في القطاع الزراعي',
                    ]);

                    // 5. Special Objectives with Results and Outputs
                    for ($j = 0; $j < 2; $j++) {
                        $specialObjective = SpecialObjective::create([
                            'project_id' => $project->id,
                            'objective' => 'الهدف الخاص رقم '.($j + 1).': تحسين الإنتاجية والكفاءة',
                        ]);

                        for ($k = 0; $k < 2; $k++) {
                            $result = ObjectiveResult::create([
                                'project_id' => $project->id,
                                'special_objective_id' => $specialObjective->id,
                                'result_name' => 'النتيجة رقم '.($k + 1).': تحسين ملموس في المؤشرات',
                            ]);

                            for ($l = 0; $l < 2; $l++) {
                                ResultOutput::create([
                                    'project_id' => $project->id,
                                    'special_objective_id' => $specialObjective->id,
                                    'objective_result_id' => $result->id,
                                    'output' => 'المخرج رقم '.($l + 1).': تقرير تفصيلي عن التحسينات',
                                ]);
                            }
                        }
                    }

                    // 6. Project Risks
                    $risks = [
                        ['risk' => 'تأخر في تنفيذ المشروع', 'rate' => 5, 'solution' => 'وضع خطة زمنية محكمة ومتابعة دورية'],
                        ['risk' => 'نقص التمويل', 'rate' => 6, 'solution' => 'تأمين مصادر تمويل بديلة'],
                        ['risk' => 'نقص الكوادر المؤهلة', 'rate' => 4, 'solution' => 'برامج تدريب مكثفة'],
                    ];

                    foreach ($risks as $riskData) {
                        ProjectRisk::create([
                            'project_id' => $project->id,
                            'risk' => $riskData['risk'],
                            'risk_rate' => $riskData['rate'],
                            'proposed_solution' => $riskData['solution'],
                        ]);
                    }

                    // 7. Entities (if available)
                    if ($authority) {
                        ProjectSupervisingAuthority::create([
                            'project_id' => $project->id,
                            'authority_id' => $authority->id,
                            'role' => 'الإشراف العام',
                        ]);

                        ProjectImplementingEntity::create([
                            'project_id' => $project->id,
                            'authority_id' => $authority->id,
                            'role' => 'التنفيذ المباشر',
                        ]);
                    }

                    // 8. Preliminary Activities
                    if ($financialItem) {
                        for ($m = 0; $m < 2; $m++) {
                            $activity = PreliminaryActivity::create([
                                'project_id' => $project->id,
                                'activity' => 'النشاط التمهيدي رقم '.($m + 1),
                            ]);

                            $procedure = PreliminaryProcedure::create([
                                'project_id' => $project->id,
                                'preliminary_activity_id' => $activity->id,
                                'procedure' => 'الإجراء رقم 1',
                                'start_date' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                                'end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                            ]);

                            PreliminaryCost::create([
                                'project_id' => $project->id,
                                'activity_id' => $activity->id,
                                'preliminary_procedure_id' => $procedure->id,
                                'financial_item_id' => $financialItem->id,
                                'quantity' => 10,
                                'unit_price' => 5000,
                                'total_cost' => 50000,
                            ]);
                        }
                    }

                    // 9. Executive Activities
                    if ($financialItem) {
                        $execActivity = ExecutiveActivity::create([
                            'project_id' => $project->id,
                            'activity_name' => 'النشاط التنفيذي الرئيسي',
                        ]);

                        $action = ExecutiveActivityAction::create([
                            'project_id' => $project->id,
                            'executive_activity_id' => $execActivity->id,
                            'action_name' => 'الإجراء التنفيذي',
                            'start_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                        ]);

                        ExecutiveActionCost::create([
                            'project_id' => $project->id,
                            'executive_activity_id' => $execActivity->id,
                            'executive_activity_action_id' => $action->id,
                            'financial_item_id' => $financialItem->id,
                            'quantity' => 20,
                            'unit_price' => 10000,
                            'total_cost' => 200000,
                        ]);
                    }

                    // 10. Project Financing
                    if ($fundingSource && $authority && $financingType) {
                        ProjectFinancing::create([
                            'project_id' => $project->id,
                            'funding_source_id' => $fundingSource->id,
                            'authority_id' => $authority->id,
                            'financing_type_id' => $financingType->id,
                            'financing_amount' => 1000000,
                            'financing_percentage' => 100,
                        ]);
                    }

                    // 11. Project Cost
                    ProjectCost::create([
                        'project_id' => $project->id,
                        'total_cost' => 1000000,
                        'year_type' => 'hijri',
                        'approval_date_hijri' => '1446-06-15',
                        'approval_year_gregorian' => 2024,
                    ]);

                    // 12. Beneficiary Groups
                    if ($beneficiaryGroup) {
                        $project->beneficiaryGroups()->attach($beneficiaryGroup->id);
                    }

                    DB::commit();
                    $this->command->info("   ✅ Project created successfully (ID: {$project->id})");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->command->error('   ❌ Failed to create project: '.$e->getMessage());

                    continue;
                }
            }

            $this->command->info("\n🎉 Seeding completed!");

        } catch (\Exception $e) {
            $this->command->error('❌ Seeder failed: '.$e->getMessage());
        }
    }
}
