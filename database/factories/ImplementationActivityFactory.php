<?php

namespace Database\Factories;

use App\Models\ImplementationActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImplementationActivity>
 */
class ImplementationActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_name' => $this->faker->sentence(3),
            'activity_weight' => $this->faker->randomFloat(2, 10, 100),
            'outputs' => $this->faker->paragraph(),
            'risks' => $this->faker->paragraph(),
        ];
    }
}
