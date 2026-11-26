<?php

namespace App\Factories;

use App\Models\Building;
use App\Models\Phone;
use App\Models\PhysicalSwitch;
use App\Models\Site;
use App\Support\FakerPatterns;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PhoneFactory extends Factory
{
    protected $model = Phone::class;

    /**
     * Define default attribute values for creating Phone model instances.
     *
     * The returned array maps Phone model attributes to generated values:
     * - `name`, `type`, `description`, `vendor`, `product`, `version`, `address_ip` are populated with faker data.
     * - `physical_switch_id`, `site_id`, `building_id` are factory definitions that create the related models.
     * - `created_at` and `updated_at` are set to the current timestamp.
     *
     * @return array<string,mixed> Associative array of Phone attributes keyed by column name.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->regexify(FakerPatterns::HOSTNAME),
            'type' => $this->faker->word(),
            'description' => $this->faker->text(),
            'vendor' => $this->faker->word(),
            'product' => $this->faker->word(),
            'version' => $this->faker->regexify(FakerPatterns::SEMVER),
            'physical_switch_id' => PhysicalSwitch::factory(),
            'address_ip' => $this->faker->ipv4(),
            'install_date' => $this->faker->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
            'warranty_end_date' => $this->faker->dateTimeBetween('-1 year', '+4 years')->format('Y-m-d'),
            'end_of_life_date' => $this->faker->dateTimeBetween('+1 year', '+8 years')->format('Y-m-d'),
            'manufacturer' => $this->faker->randomElement(['Cisco', 'HPE', 'Dell', 'Fortinet', 'Juniper', 'NetApp', 'Yealink']),
            'model' => strtoupper($this->faker->bothify('??-####')),
            'serial_number' => strtoupper($this->faker->bothify('??########')),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'site_id' => Site::factory(),
            'building_id' => Building::factory(),
        ];
    }
}
