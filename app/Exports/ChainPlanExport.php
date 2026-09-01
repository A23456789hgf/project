<?php

namespace App\Exports;

use App\Models\Authority;
use App\Models\ChainPlan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ChainPlanExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $search;

    protected $authoritiesCache = null;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    protected function getAuthoritiesCache()
    {
        if ($this->authoritiesCache === null) {
            $this->authoritiesCache = Authority::pluck('agency_name', 'id')->toArray();
        }

        return $this->authoritiesCache;
    }

    public function query()
    {
        $query = ChainPlan::with([
            'governorate',
            'directorate',
            'valueChain',
            'domain',
            'financingType',
            'fundingSource',
            'authority',
            'implementingEntity',
        ]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('indicator', 'like', "%{$this->search}%")
                    ->orWhere('number', 'like', "%{$this->search}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'المحافظة',
            'المديرية',
            'السلسلة',
            'المجال',
            'اسم المشروع',
            'اسم النشاط',
            'المؤشر',
            'العدد',
            'نوع التمويل',
            'مصدر التمويل',
            'الجهة المنفذة',
            'الجهة المشرفة',
        ];
    }

    protected function formatAuths($value)
    {
        if (empty($value) || $value === '-') {
            return '';
        }
        $ids = is_array($value) ? $value : (json_decode($value, true) ?: [$value]);
        if (empty($ids)) {
            return '';
        }

        $cache = $this->getAuthoritiesCache();
        $names = [];
        foreach ((array) $ids as $id) {
            if (isset($cache[$id])) {
                $names[] = $cache[$id];
            }
        }

        return ! empty($names) ? implode('، ', $names) : (is_array($value) ? implode('، ', $value) : (string) $value);
    }

    public function map($chainPlan): array
    {
        return [
            $chainPlan->governorate->name ?? $chainPlan->governorate_id ?? '',
            $chainPlan->directorate->name ?? $chainPlan->directorate_id ?? '',
            $chainPlan->valueChain->name ?? $chainPlan->value_chain_id ?? '',
            $chainPlan->domain->name ?? $chainPlan->domain_id ?? '',
            $chainPlan->project_name ?? '',
            $chainPlan->activity_name ?? '',
            $chainPlan->indicator ?? '',
            $chainPlan->number ?? '',
            $chainPlan->financingType->name ?? $chainPlan->value_chain_financing_type_id ?? '',
            $this->formatAuths($chainPlan->funding_source_id),
            $this->formatAuths($chainPlan->authority_id),
            $this->formatAuths($chainPlan->implementing_entity_id),
        ];
    }
}
