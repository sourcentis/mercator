<?php

namespace App\Factories;

use App\Models\Entity;
use App\Models\Relation;
use App\Support\FakerPatterns;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RelationFactory extends Factory
{
    protected $model = Relation::class;

    public function definition(): array
    {
        return [
            'importance' => $this->faker->randomNumber(),
            'name' => $this->faker->regexify(FakerPatterns::ORG_NAME),
            'type' => $this->faker->word(),
            'description' => $this->faker->text(),
            'attributes' => $this->faker->word(),
            'reference' => $this->faker->word(),
            'responsible' => $this->faker->word(),
            'order_number' => $this->faker->word(),
            'active' => $this->faker->boolean(),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
            'comments' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'source_id' => Entity::factory(),
            'destination_id' => Entity::factory(),
        ];
    }
}
