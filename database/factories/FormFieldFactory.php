<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'label' => fake()->words(2, true),
            'key' => fake()->unique()->slug(2),
            'type' => fake()->randomElement(['text', 'email', 'select', 'textarea', 'checkbox']),
            'is_required' => false,
            'order' => fake()->numberBetween(1, 20),
            'options' => null,
        ];
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function select(array $options = []): static
    {
        $options = $options ?: ['Option A', 'Option B', 'Option C'];

        return $this->state(fn (array $attributes) => [
            'type' => 'select',
            'options' => $options,
        ]);
    }
}
