<?php

namespace Database\Seeders;

use App\Models\ProductSpec;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        ProductSpec::insert([['title' => '尺寸'], ['title' => '容量']]);

    }
}
