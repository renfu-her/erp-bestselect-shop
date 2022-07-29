<?php

namespace Database\Seeders;

use App\Models\SaleChannel;
use Illuminate\Database\Seeder;

class batchBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        SaleChannel::batchBonus();
    }
}
