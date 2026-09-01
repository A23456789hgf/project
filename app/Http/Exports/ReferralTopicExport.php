<?php

namespace App\Exports;

use App\Models\ReferralTopic;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReferralTopicExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $topic;

    public function __construct(ReferralTopic $topic)
    {
        $this->topic = $topic->load(['activities.fromUser', 'activities.fromDepartment', 'activities.toDepartment', 'activities.responder']);
    }

    public function collection()
    {
        return $this->topic->activities;
    }

    public function headings(): array
    {
        return [
            ['الموضوع:', $this->topic->subject],
            ['تاريخ الإنشاء:', $this->topic->created_at->format('Y-m-d H:i')],
            ['بواسطة:', $this->topic->creator->name],
            [],
            [
                'رقم الإحالة',
                'من',
                'إلى',
                'نص الإحالة',
                'تاريخ الإحالة',
                'الرد',
                'تاريخ الرد',
                'بواسطة (الرد)',
            ],
        ];
    }

    public function map($activity): array
    {
        return [
            $activity->referral_number,
            $activity->fromUser->name.' ('.$activity->fromDepartment->name.')',
            $activity->toDepartment->name,
            $activity->referral_text,
            $activity->referral_date->format('Y-m-d H:i'),
            $activity->response_text ?? 'لا يوجد رد',
            $activity->response_date ? $activity->response_date->format('Y-m-d H:i') : '-',
            $activity->responder ? $activity->responder->name : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            5 => ['font' => ['bold' => true]],
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
