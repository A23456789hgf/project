<?php

namespace App\Exports;

use App\Models\Authority;
use Illuminate\Database\Eloquent\Model;
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
        $this->fields = $fields ?? ['id', 'agency_name', 'is_active'];
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $data = $this->authorities ?? Authority::select('id', 'agency_name', 'is_active', 'parent_id', 'created_at')->get();

        return $data->map(function ($authority) {
            $row = [];

            // Handle both object and array/collection data
            $getVal = function ($item, $key) {
                if (is_array($item)) {
                    return $item[$key] ?? null;
                }
                if ($item instanceof Model) {
                    if ($key === 'parent_name') {
                        return $item->parent ? $item->parent->agency_name : null;
                    }

                    return $item->{$key};
                }

                return data_get($item, $key);
            };

            if (in_array('id', $this->fields)) {
                $row['id'] = $getVal($authority, 'id');
            }
            if (in_array('agency_name', $this->fields)) {
                $row['agency_name'] = $getVal($authority, 'agency_name');
            }
            if (in_array('is_active', $this->fields)) {
                $status = $getVal($authority, 'is_active');
                if (is_bool($status)) {
                    $row['is_active'] = $status ? 'نشط' : 'غير نشط';
                } else {
                    $row['is_active'] = $status;
                }
            }
            if (in_array('parent_id', $this->fields)) {
                $row['parent_id'] = $getVal($authority, 'parent_id');
            }
            if (in_array('parent_name', $this->fields)) {
                $row['parent_name'] = $getVal($authority, 'parent_name');
            }
            if (in_array('created_at', $this->fields)) {
                $createdAt = $getVal($authority, 'created_at');
                $row['created_at'] = $createdAt instanceof \DateTime ? $createdAt->format('Y-m-d') : $createdAt;
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
        if (in_array('is_active', $this->fields)) {
            $headings[] = 'الحالة';
        }
        if (in_array('parent_id', $this->fields)) {
            $headings[] = 'معرف الأب';
        }
        if (in_array('parent_name', $this->fields)) {
            $headings[] = 'الجهة الأب';
        }
        if (in_array('created_at', $this->fields)) {
            $headings[] = 'تاريخ الإنشاء';
        }

        return $headings;
    }
}
