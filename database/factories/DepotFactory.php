<?php

namespace Database\Factories;

use App\Models\Depot;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepotFactory extends Factory
{
    protected $model = Depot::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->companySuffix . '倉庫',
            'sender' => $this->faker->name,
            'address' => '300 新竹市東區忠孝西路30號',
            'tel' => $this->faker->phoneNumber,
            'city_id' => 73,
            'region_id' => 74,
            'addr' => '忠孝西路30號'
        ];
    }
}
