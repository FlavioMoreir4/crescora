<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'data' => [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'message' => fake()->paragraph(),
            ],
        ];
    }
}
