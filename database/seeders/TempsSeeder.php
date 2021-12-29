<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Temps;

class TempsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Temps::factory()->create(['temps' => '常溫']);
        Temps::factory()->create(['temps' => '冷藏']);
        Temps::factory()->create(['temps' => '冷凍']);
    }
}
