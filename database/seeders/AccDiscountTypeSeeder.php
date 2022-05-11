<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccDiscountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('acc_discount_type')->insert([
            ['code' => 'global',
                'title' => '全館優惠'],
            ['code' => 'coupon',
                'title' => '優惠券與序號'],
            ['code' => 'bonus',
                'title' => '紅利'],
            ['code' => 'optional',
                'title' => '任選擇扣'],
        ]);
    }
}
