<?php

namespace App\Factories;

use App\Models\MacroProcessus;
use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    protected $model = Process::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(10, true),
            'description' => fake()->optional()->sentence(),
            'in_out' => fake()->optional()->text(200),
            'security_need_c' => fake()->optional()->numberBetween(0, 4),
            'security_need_i' => fake()->optional()->numberBetween(0, 4),
            'security_need_a' => fake()->optional()->numberBetween(0, 4),
            'security_need_t' => fake()->optional()->numberBetween(0, 4),
            'security_need_auth' => fake()->optional()->numberBetween(0, 4),
            'owner' => fake()->optional()->name(),
            'macroprocess_id' => null,
        ];
    }

    /**
     * Process avec un macro-processus associé
     */
    public function withMacroProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'macroprocess_id' => MacroProcessus::factory(),
        ]);
    }

    /**
     * Process avec des besoins de sécurité élevés
     */
    public function highSecurity(): static
    {
        return $this->state(fn (array $attributes) => [
            'security_need_c' => fake()->numberBetween(3, 4),
            'security_need_i' => fake()->numberBetween(3, 4),
            'security_need_a' => fake()->numberBetween(3, 4),
            'security_need_t' => fake()->numberBetween(3, 4),
            'security_need_auth' => fake()->numberBetween(3, 4),
        ]);
    }

    /**
     * Process avec des besoins de sécurité faibles
     */
    public function lowSecurity(): static
    {
        return $this->state(fn (array $attributes) => [
            'security_need_c' => fake()->numberBetween(0, 1),
            'security_need_i' => fake()->numberBetween(0, 1),
            'security_need_a' => fake()->numberBetween(0, 1),
            'security_need_t' => fake()->numberBetween(0, 1),
            'security_need_auth' => fake()->numberBetween(0, 1),
        ]);
    }
}
