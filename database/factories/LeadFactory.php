<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Team;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'unit_id' => Unit::factory(),
            'created_by' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => 'new',
            'source' => fake()->randomElement(['website', 'referral', 'direct', 'social']),
        ];
    }

    public function qualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'qualified',
        ]);
    }

    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'lost',
        ]);
    }
}
