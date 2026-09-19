<?php

namespace Database\Factories;

use App\Models\InternalEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternalEntity>
 */
class InternalEntityFactory extends Factory
{
    protected $model = InternalEntity::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'status' => 'active',
            'entity_type' => $this->faker->randomElement(['Company', 'Department']),
            'entity_code' => strtoupper($this->faker->unique()->lexify('??')),

            'parent_id' => null,
            'authority_id' => null,
            'governorate_id' => null,
            'directorate_id' => null,
            'is_active' => true,
            'creator_username' => 'system',
            'creator_entity_id' => null,
            'erpnext_id' => null,
            'erpnext_type' => null,
            'erpnext_parent_id' => null,
        ];
    }
}
