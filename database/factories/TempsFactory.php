<?php

namespace Database\Factories;

use App\Models\Temps;
use Illuminate\Database\Eloquent\Factories\Factory;

class TempsFactory extends Factory
{
    protected $model = Temps::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $temps_array = [
            '常溫',
            '冷藏',
            '冷凍'
        ];
        return [
            'temps' => $temps_array[array_rand($temps_array)]
        ];
    }
}
