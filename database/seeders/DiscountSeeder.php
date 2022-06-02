<?php

namespace Database\Seeders;

use App\Enums\Discount\DisMethod;
use App\Models\Discount;
use App\Models\DividendSetting;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Discount::createDiscount('全館85折', 100, DisMethod::percent(), 85);
        $cid = Discount::createCoupon('黃金假期折抵', 100, DisMethod::cash(), 20);
        Discount::createDiscount('送抵用券', 0, DisMethod::coupon(), $cid);
        Discount::createCode("apple", "特惠碼", 300, DisMethod::cash(), 30);
        DividendSetting::updateSetting(3, 15);
    }
}
