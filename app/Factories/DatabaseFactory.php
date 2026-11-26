<?php

namespace App\Factories;

use App\Models\Database;
use App\Models\Entity;
use App\Support\FakerPatterns;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DatabaseFactory extends Factory
{
    protected $model = Database::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->regexify(FakerPatterns::SYSTEM_NAME),
            'type' => $this->faker->word(),
            'description' => $this->faker->text(),
            'responsible' => $this->faker->word(),
            'external' => $this->faker->word(),
            'security_need_c' => $this->faker->optional()->numberBetween(0, 4),
            'security_need_i' => $this->faker->optional()->numberBetween(0, 4),
            'security_need_a' => $this->faker->optional()->numberBetween(0, 4),
            'security_need_t' => $this->faker->optional()->numberBetween(0, 4),
            'security_need_auth' => $this->faker->optional()->numberBetween(0, 4),
            'install_date' => $this->faker->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'entity_resp_id' => Entity::factory(),
        ];
    }
}
