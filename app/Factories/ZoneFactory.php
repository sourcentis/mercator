<?php

namespace App\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'type' => $this->faker->optional()->word(),
            'attributes' => null,
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
