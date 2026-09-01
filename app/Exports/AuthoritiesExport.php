<?php

namespace App\Exports;

use App\Models\Authority;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AuthoritiesExport implements FromCollection, WithHeadings
{
    protected $authorities;

    protected $fields;

    public function __construct($authorities = null, $fields = null)
    {
        $this->authorities = $authorities;
        $this->fields = $fields ?? ['id', 'agency_name', 'parent_id', 'governorate_id', 'directorate_id', 'type_entity_id', 'entity_scope', 'financing_type_id', 'is_active', 'created_at'];
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $data = $this->authorities ?? Authority::with(['parent', 'governorate', 'directorate', 'financingType', 'typeEntity'])
            ->select('id', 'agency_name', 'is_active', 'parent_id', 'governorate_id', 'directorate_id', 'type_entity_id', 'entity_scope', 'financing_type_id', 'created_at')->get();

        return $data->map(function ($authority) {
            $row = [];
            if (in_array('id', $this->fields)) {
                $row['id'] = data_get($authority, 'id') ?? data_get($authority, 'row_number') ?? '-';
            }
            if (in_array('agency_name', $this->fields)) {
                $row['agency_name'] = data_get($authority, 'agency_name') ?? '';
            }
            if (in_array('parent_id', $this->fields)) {
                $parent = data_get($authority, 'parent');
                $row['parent_id'] = data_get($parent, 'agency_name') ?? (data_get($authority, 'parent_id') ?? '-');
            }
            if (in_array('governorate_id', $this->fields)) {
                $gov = data_get($authority, 'governorate');
                $row['governorate_id'] = data_get($gov, 'name') ?? (data_get($authority, 'governorate_id') ?? '-');
            }
            if (in_array('directorate_id', $this->fields)) {
                $dir = data_get($authority, 'directorate');
                $row['directorate_id'] = data_get($dir, 'name') ?? (data_get($authority, 'directorate_id') ?? '-');
            }
            if (in_array('type_entity_id', $this->fields)) {
                $typeEntity = data_get($authority, 'typeEntity');
                $row['type_entity_id'] = data_get($typeEntity, 'name') ?? (data_get($authority, 'type_entity_id') ?? '-');
            }
            if (in_array('entity_scope', $this->fields)) {
                $scope = data_get($authority, 'entity_scope');
                $row['entity_scope'] = $scope === 'internal' || $scope === 'داخلي' ? 'داخلي' : ($scope === 'external' || $scope === 'خارجي' ? 'خارجي' : ($scope ?? '-'));
            }
            if (in_array('financing_type_id', $this->fields) || in_array('financing_form_id', $this->fields)) {
                $type = data_get($authority, 'financingType');
                $row['financing_type_id'] = data_get($type, 'name') ?? (data_get($authority, 'financing_type_id') ?? '-');
            }
            if (in_array('is_active', $this->fields)) {
                $isActive = data_get($authority, 'is_active');
                $row['is_active'] = $isActive === 'نشط' || $isActive === true || $isActive === 1 ? 'نشط' : 'غير نشط';
            }
            if (in_array('created_at', $this->fields)) {
                $createdAt = data_get($authority, 'created_at');
                $row['created_at'] = $createdAt instanceof \DateTime ? $createdAt->format('Y-m-d') : ($createdAt ?? '-');
            }
            if (in_array('errors', $this->fields)) {
                $row['errors'] = data_get($authority, 'errors');
            }

            return $row;
        });
    }

    public function headings(): array
    {
        $headings = [];
        if (in_array('id', $this->fields)) {
            $headings[] = 'الرقم';
        }
        if (in_array('agency_name', $this->fields)) {
            $headings[] = 'اسم الجهة';
        }
        if (in_array('parent_id', $this->fields)) {
            $headings[] = 'الجهة الأب';
        }
        if (in_array('governorate_id', $this->fields)) {
            $headings[] = 'المحافظة';
        }
        if (in_array('directorate_id', $this->fields)) {
            $headings[] = 'المديرية';
        }
        if (in_array('type_entity_id', $this->fields)) {
            $headings[] = 'نوع الجهة';
        }
        if (in_array('entity_scope', $this->fields)) {
            $headings[] = 'نطاق الجهة';
        }
        if (in_array('financing_type_id', $this->fields) || in_array('financing_form_id', $this->fields)) {
            $headings[] = 'نوع الجهة (نوع التمويل)';
        }
        if (in_array('is_active', $this->fields)) {
            $headings[] = 'الحالة';
        }
        if (in_array('created_at', $this->fields)) {
            $headings[] = 'تاريخ الإنشاء';
        }
        if (in_array('errors', $this->fields)) {
            $headings[] = 'الأخطاء';
        }

        return $headings;
    }
}
