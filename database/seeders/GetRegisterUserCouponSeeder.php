<?php

namespace Database\Seeders;

use App\Models\CustomerCoupon;
use App\Models\Discount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GetRegisterUserCouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $_coupon = Discount::where('title', '會員註冊')->get()->first();
        if (!$_coupon) {
            return;
        }

        $users = DB::table('temp_register_customer as r')
            ->join('usr_customers as c', 'r.email', '=', 'c.email')
            ->get();

        foreach ($users as $u) {
            CustomerCoupon::create([
                'from_order_id' => 0,
                'limit_day' => 0,
                'customer_id' => $u->id,
                'discount_id' => $_coupon->id,
                'active_sdate'=>now(),
                'active_edate'=>date('Y-m-d 23:59:59', strtotime('+3 year'))
            ]);
        }

        CustomerCoupon::create([
            'from_order_id' => 0,
            'limit_day' => 0,
            'customer_id' => 1,
            'discount_id' => $_coupon->id,
            'active_sdate'=>now(),
            'active_edate'=>date('Y-m-d 23:59:59', strtotime('+5 year'))
        ]);
      

        echo 'done';

    }
}
