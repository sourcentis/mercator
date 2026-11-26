<?php

namespace App\Factories;

use App\Models\Bay;
use App\Models\Building;
use App\Models\PhysicalSecurityDevice;
use App\Models\Site;
use App\Support\FakerPatterns;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PhysicalSecurityDeviceFactory extends Factory
{
    protected $model = PhysicalSecurityDevice::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->regexify(FakerPatterns::HOSTNAME),
            'type' => $this->faker->word(),
            'description' => $this->faker->text(),
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
            'bay_id' => Bay::factory(),
        ];
    }
}
