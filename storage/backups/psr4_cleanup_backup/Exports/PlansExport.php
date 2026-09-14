<?php

namespace App\Exports;

use App\Models\Plan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlansExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    protected $search;

    protected $planId;

    public function __construct(?string $search = null, $planId = null)
    {
        $this->search = $search;
        $this->planId = $planId;
    }

    public function title(): string
    {
        return 'المصفوفة التشغيلية';
    }

    public function headings(): array
    {
        return [
            'رقم الخطة',
            'الأولوية',
            'الجهة المقدمة للخطة',
            'اسم المشروع',
            'الأهمية',
            'الحالة',
            'التكلفة',
            'العملة',
            'مؤشرات الأداء',
            'المخرجات',
            'الوضع الراهن (Baseline)',
            'القيمة المستهدفة',
            'توفر التمويل',
            'مصدر التمويل',
            'جهة التنفيذ',
            'اسم النشاط',
            'وزن النشاط (%)',
            'اسم الإجراء',
            'وزن الإجراء (%)',
            'تاريخ البدء',
            'تاريخ الانتهاء',
            'المدة (أيام)',
        ];
    }

    public function collection(): Collection
    {
        $query = Plan::with([
            'projects.participatingEntity',
            'projects.fundingSource',
            'projects.activities.actions',
            'submittingEntity',
            'priority',
        ]);

        if ($this->planId) {
            $query->where('id', $this->planId);
        } elseif ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('plan_number', 'like', "%{$search}%")
                    ->orWhereHas('submittingEntity', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('priority', fn ($sq) => $sq->where('priority', 'like', "%{$search}%"));
            });
        }

        $plans = $query->latest()->get();
        $rows = collect();

        $importanceMap = [
            'normal' => 'عادي',
            'important' => 'مهم',
            'very_important' => 'مهم جداً',
        ];

        $statusMap = [
            'new' => 'جديد',
            'terminated' => 'منتهٍ',
        ];

        foreach ($plans as $plan) {
            foreach ($plan->projects as $project) {
                $activities = $project->activities;

                if ($activities->isEmpty()) {
                    // Export project row with no activities/actions
                    $rows->push([
                        $plan->plan_number,
                        optional($plan->priority)->priority ?? '',
                        optional($plan->submittingEntity)->name ?? '',
                        $project->name,
                        $importanceMap[$project->importance] ?? $project->importance,
                        $statusMap[$project->status] ?? $project->status,
                        $project->cost,
                        $project->cost_type,
                        $project->indicators ?? '',
                        $project->outputs ?? '',
                        $project->baseline ?? '',
                        $project->target_value ?? '',
                        $project->funding_availability ? 'نعم' : 'لا',
                        optional($project->fundingSource)->name ?? '',
                        optional($project->participatingEntity)->name ?? '',
                        '', // activity name
                        '', // activity weight
                        '', // action name
                        '', // action weight
                        '', // start date
                        '', // end date
                        '', // duration
                    ]);
                }

                foreach ($activities as $activity) {
                    $actions = $activity->actions;

                    if ($actions->isEmpty()) {
                        $rows->push([
                            $plan->plan_number,
                            optional($plan->priority)->priority ?? '',
                            optional($plan->submittingEntity)->name ?? '',
                            $project->name,
                            $importanceMap[$project->importance] ?? $project->importance,
                            $statusMap[$project->status] ?? $project->status,
                            $project->cost,
                            $project->cost_type,
                            $project->indicators ?? '',
                            $project->outputs ?? '',
                            $project->baseline ?? '',
                            $project->target_value ?? '',
                            $project->funding_availability ? 'نعم' : 'لا',
                            optional($project->fundingSource)->name ?? '',
                            optional($project->participatingEntity)->name ?? '',
                            $activity->name,
                            $activity->weight,
                            '', // action name
                            '', // action weight
                            '', // start date
                            '', // end date
                            '', // duration
                        ]);

                        continue;
                    }

                    foreach ($actions as $action) {
                        $rows->push([
                            $plan->plan_number,
                            optional($plan->priority)->priority ?? '',
                            optional($plan->submittingEntity)->name ?? '',
                            $project->name,
                            $importanceMap[$project->importance] ?? $project->importance,
                            $statusMap[$project->status] ?? $project->status,
                            $project->cost,
                            $project->cost_type,
                            $project->indicators ?? '',
                            $project->outputs ?? '',
                            $project->baseline ?? '',
                            $project->target_value ?? '',
                            $project->funding_availability ? 'نعم' : 'لا',
                            optional($project->fundingSource)->name ?? '',
                            optional($project->participatingEntity)->name ?? '',
                            $activity->name,
                            $activity->weight,
                            $action->name,
                            $action->weight,
                            $action->start_date_g ? $action->start_date_g->format('Y-m-d') : '',
                            $action->end_date_g ? $action->end_date_g->format('Y-m-d') : '',
                            $action->duration ?? '',
                        ]);
                    }
                }
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        // Apply RTL direction
        $sheet->setRightToLeft(true);

        return [
            // Heading row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1B2A4A'], // Navy
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                ],
            ],
        ];
    }
}
