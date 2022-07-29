<?php

namespace Database\Seeders;

use App\Models\SaleChannel;
use Illuminate\Database\Seeder;

class batchDividendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        SaleChannel::batchDividend();
    }
}
