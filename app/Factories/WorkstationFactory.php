<?php

namespace App\Factories;

use App\Models\AdminUser;
use App\Models\Building;
use App\Models\Domain;
use App\Models\Entity;
use App\Models\Network;
use App\Models\Site;
use App\Models\Workstation;
use App\Support\FakerPatterns;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WorkstationFactory extends Factory
{
    protected $model = Workstation::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->regexify(FakerPatterns::HOSTNAME),
            'description' => $this->faker->text(),
            'vendor' => $this->faker->word(),
            'product' => $this->faker->word(),
            'version' => $this->faker->regexify(FakerPatterns::SEMVER),
            'physical_switch_id' => null,
            'type' => $this->faker->word(),
            'icon_id' => null,
            'operating_system' => $this->faker->word(),
            'address_ip' => $this->faker->ipv4(),
            'cpu' => $this->faker->word(),
            'memory' => $this->faker->word(),
            'disk' => $this->faker->randomNumber(),
            'other_user' => $this->faker->word(),
            'status' => $this->faker->word(),
            'manufacturer' => $this->faker->word(),
            'model' => $this->faker->word(),
            'serial_number' => $this->faker->word(),
            'last_inventory_date' => Carbon::now(),
            'warranty_end_date' => Carbon::now(),
            'warranty' => $this->faker->word(),
            'warranty_start_date' => Carbon::now(),
            'warranty_period' => $this->faker->word(),
            'agent_version' => $this->faker->regexify(FakerPatterns::SEMVER),
            'update_source' => $this->faker->word(),
            'network_port_type' => $this->faker->word(),
            'mac_address' => $this->faker->regexify(FakerPatterns::MAC_ADDRESS),
            'purchase_date' => Carbon::now(),
            'fin_value' => $this->faker->randomFloat(),
            'install_date' => $this->faker->dateTimeBetween('-8 years', '-1 year')->format('Y-m-d'),
            'end_of_life_date' => $this->faker->dateTimeBetween('+1 year', '+8 years')->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'entity_id' => Entity::factory(),
            'site_id' => Site::factory(),
            'building_id' => Building::factory(),
            'user_id' => AdminUser::factory(),
            'domain_id' => Domain::factory(),
            'network_id' => Network::factory(),
        ];
    }
}
