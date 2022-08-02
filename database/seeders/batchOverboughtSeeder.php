<?php

namespace Database\Seeders;

use App\Models\ProductStyle;
use Illuminate\Database\Seeder;

class batchOverboughtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        ProductStyle::batchOverbought();
    }
}
