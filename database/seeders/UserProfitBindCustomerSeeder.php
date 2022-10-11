<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserProfitBindCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $re = DB::table('usr_customer_profit as profit')
            ->join('usr_customers as customer', 'profit.customer_id', '=', 'customer.id')
            ->join('usr_users as user', 'user.name', '=', 'customer.name')
            ->select('customer.id as customer_id', 'user.id as user_id')
            ->where('profit.status', 'success')
            ->whereNull('user.customer_id')
            ->whereNull('customer.id')
            ->get();

        $cc = 0;
     
        foreach ($re as $value) {
            User::where('id', $value->user_id)->whereNull('customer_id')
                ->update(['customer_id' => $value->customer_id]);
            $cc++;

        }

        echo $cc . " 筆完成";

    }
}
