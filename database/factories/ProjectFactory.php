<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Intervention;
use App\Models\Program;
use App\Models\Subdomain;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        // Create or get related models
        $program = Program::first() ?? Program::create(['name' => fake()->sentence(2)]);
        $domain = Domain::first() ?? Domain::create(['name' => fake()->sentence(2)]);
        $subdomain = Subdomain::first() ?? Subdomain::create(['name' => fake()->sentence(2), 'domain_id' => $domain->id]);
        $intervention = Intervention::first() ?? Intervention::create(['name' => fake()->sentence(2), 'domain_id' => $domain->id, 'subdomain_id' => $subdomain->id]);

        return [
            'project_name' => fake()->sentence(3),
            'program_id' => $program->id,
            'domain_id' => $domain->id,
            'subdomain_id' => $subdomain->id,
            'intervention_id' => $intervention->id,
            'start_date_gregorian' => now()->addDays(1)->toDateString(),
            'start_date_hijri' => '1446/01/01',
            'end_date_gregorian' => now()->addDays(30)->toDateString(),
            'end_date_hijri' => '1446/02/01',
            'number_of_beneficiaries' => 100,
            'status' => 'draft',
            'last_saved_step' => 1,
            'draft_saved_at' => now(),
            'created_by_user_id' => null,
        ];
    }

    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'final',
            'last_saved_step' => 7,
            'finalized_at' => now(),
        ]);
    }

    public function withStep($step): static
    {
        return $this->state(fn (array $attributes) => [
            'last_saved_step' => $step,
        ]);
    }
}
