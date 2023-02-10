<?php

namespace Database\Seeders;

use App\Mail\CouponExpiringSoon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CouponExpiringSoonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $result = DB::table('usr_customer_coupon as coupon')
            ->leftJoin('usr_customers as customer', 'customer.id', '=', 'coupon.customer_id')
            ->where('coupon.used', '=', 0)
            ->where('coupon.active_edate', '>', '2023-02-10')
            ->where('coupon.active_edate', '<', '2023-02-17')
            ->select(
                'coupon.*'
                , 'customer.email'
                , 'customer.name'
            )
            ->get()
        ;

        if (isset($result) && 0 < count($result)) {
            foreach ($result as $val_coupon) {
                if(env('APP_ENV') == 'rel'){
                    $data = [
                        'email' => $val_coupon->email
                        , 'active_edate' => date('Y-m-d', strtotime($val_coupon->active_edate))
                        , 'link_url' => env('FRONTEND_URL'),
                    ];
                    Mail::to($val_coupon->email)->queue(new CouponExpiringSoon($data));
                }
            }
        }
    }
}
