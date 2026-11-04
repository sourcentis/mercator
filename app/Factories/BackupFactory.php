<?php

namespace App\Factories;

use App\Models\Backup;
use Illuminate\Database\Eloquent\Factories\Factory;

class BackupFactory extends Factory
{
    protected $model = Backup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'type' => $this->faker->optional()->word(),
            'description' => $this->faker->optional()->sentence(),
            'backup_frequency' => $this->faker->randomElement([1, 2, 3]),
            'backup_cycle' => $this->faker->numberBetween(1, 5),
            'backup_retention' => $this->faker->randomElement([7, 14, 30, 60, 90, 180, 365]),
        ];
    }
}
