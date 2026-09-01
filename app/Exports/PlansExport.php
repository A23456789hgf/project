<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlansExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $plans = $this->query->with([
            'priority',
            'submittingEntity',
            'projects.participatingEntity',
            'projects.fundingSource',
            'projects.goals',
            'projects.activities.actions',
        ])->latest()->get();

        $data = collect();

        foreach ($plans as $plan) {
            foreach ($plan->projects as $project) {
                $goals = $project->goals;

                $actions = collect();
                foreach ($project->activities as $activity) {
                    if ($activity->actions->count() === 0) {
                        $actions->push([
                            'activity' => $activity,
                            'action' => null,
                        ]);
                    } else {
                        foreach ($activity->actions as $action) {
                            $actions->push([
                                'activity' => $activity,
                                'action' => $action,
                            ]);
                        }
                    }
                }

                if ($actions->isEmpty()) {
                    $actions->push([
                        'activity' => null,
                        'action' => null,
                    ]);
                }

                $totalRows = max($goals->count(), $actions->count());

                for ($i = 0; $i < $totalRows; $i++) {
                    $goal = $goals->get($i);
                    $actionData = $actions->get($i);

                    $data->push([
                        'plan' => $plan,
                        'project' => $project,
                        'goal' => $goal,
                        'activity' => $actionData['activity'],
                        'action' => $actionData['action'],
                    ]);
                }
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'رقم الخطة',
            'الأولوية',
            'الجهة المقدمة',
            'اسم المشروع',
            'الأهمية',
            'حالة المشروع',
            'خط الأساس',
            'الهدف المحدد',
            'الوزن %',
            'قيمة المؤشر',
            'وحدة القياس',
            'نوع التكلفة',
            'التكلفة',
            'توفر التمويل',
            'مصدر التمويل',
            'الجهة المشاركة',
            'اسم النشاط',
            'وزن النشاط',
            'اسم الإجراء',
            'وزن الإجراء',
            'تاريخ البداية',
            'تاريخ النهاية',
            'المدة',
        ];
    }

    public function map($row): array
    {
        $plan = $row['plan'];
        $project = $row['project'];
        $goal = $row['goal'] ?? null;
        $activity = $row['activity'];
        $action = $row['action'];

        return [
            $plan->plan_number,
            $plan->priority->priority ?? '',
            $plan->submittingEntity->name ?? '',
            $project->name,
            $this->getImportanceLabel($project->importance),
            $this->getStatusLabel($project->status),
            $project->baseline,
            $goal?->specific_goal ?? '',
            $goal?->weight ?? '',
            $goal?->indicator_value ?? '',
            $goal?->unit_of_measurement ?? '',
            $project->cost_type,
            $project->cost,
            $project->funding_availability ? 'نعم' : 'لا',
            $project->fundingSource->name ?? '',
            $project->participatingEntity->name ?? '',
            $activity?->name ?? '',
            $activity?->weight ?? '',
            $action?->name ?? '',
            $action?->weight ?? '',
            ($action?->start_date_g) ? $action->start_date_g->format('Y-m-d') : '',
            ($action?->end_date_g) ? $action->end_date_g->format('Y-m-d') : '',
            $action?->duration ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    private function getImportanceLabel($importance)
    {
        $labels = [
            'normal' => 'عادي',
            'important' => 'هام',
            'very_important' => 'هام جداً',
        ];

        return $labels[$importance] ?? $importance;
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'new' => 'جديد',
            'terminated' => 'مستمر',
        ];

        return $labels[$status] ?? $status;
    }
}
