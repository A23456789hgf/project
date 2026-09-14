<?php

namespace App\Exports\Sheets;

use App\Models\Domain;
use App\Models\Intervention;
use App\Models\Priority;
use App\Models\Program;
use App\Models\Subdomain;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LookupSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'القيم المرجعية';
    }

    public function headings(): array
    {
        return [
            'program_id',
            'program_name',
            '',
            'domain_id',
            'domain_name',
            '',
            'subdomain_id',
            'subdomain_name',
            '',
            'intervention_id',
            'intervention_name',
            '',
            'priority_id',
            'priority_name',
        ];
    }

    public function array(): array
    {
        try {
            $programs = Program::select('id', 'name')->orderBy('id')->get();
            $domains = Domain::select('id', 'name')->orderBy('id')->get();
            $subdomains = Subdomain::select('id', 'name')->orderBy('id')->get();
            $interventions = Intervention::select('id', 'name')->orderBy('id')->get();
            $priorities = Priority::select('id', 'priority')->orderBy('id')->get();

            $maxRows = max(
                $programs->count(),
                $domains->count(),
                $subdomains->count(),
                $interventions->count(),
                $priorities->count(),
                1
            );

            $rows = [];
            for ($i = 0; $i < $maxRows; $i++) {
                $rows[] = [
                    $programs->get($i)->id ?? '',
                    $programs->get($i)->name ?? '',
                    '',
                    $domains->get($i)->id ?? '',
                    $domains->get($i)->name ?? '',
                    '',
                    $subdomains->get($i)->id ?? '',
                    $subdomains->get($i)->name ?? '',
                    '',
                    $interventions->get($i)->id ?? '',
                    $interventions->get($i)->name ?? '',
                    '',
                    $priorities->get($i)->id ?? '',
                    $priorities->get($i)->priority ?? '',
                ];
            }

            return $rows;
        } catch (\Exception $e) {
            \Log::error('Failed to load lookup data for template export: '.$e->getMessage());

            // Return empty row to prevent Excel corruption
            return [
                ['', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ];
        }
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF808080'],
                ],
            ],
        ];
    }
}
