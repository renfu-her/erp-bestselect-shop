<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserNameSyncToCustomerNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $re = DB::table('usr_users as user')
            ->select(['user.customer_id', 'user.name'])
            ->join('usr_customers as customer', 'user.customer_id', '=', 'customer.id')
            ->get();

        $cc = 0;
        foreach ($re as $r) {
            Customer::where('id', $r->customer_id)->update(['name' => $r->name]);
            $cc++;
        }

        dd($cc . '筆處理');

    }
}
