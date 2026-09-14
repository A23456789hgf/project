<?php

namespace App\Imports;

use App\Models\EntityFather;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EntityFathersImport implements ToModel, WithHeadingRow
{
    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        // Check if the entity_father is unique
        $existing = EntityFather::where('entity_father', $row['entity_father'])->first();
        if ($existing) {
            // You can choose to skip or update. Here we skip.
            return null;
        }

        return new EntityFather([
            'entity_father' => $row['entity_father'],
        ]);
    }
}
