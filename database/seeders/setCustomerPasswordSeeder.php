<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class setCustomerPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::beginTransaction();

        foreach (Customer::get() as $c) {
            if ($c->phone) {
                Customer::where('id', $c->id)->update([
                    'password' => Hash::make(substr($c->phone, -6)),
                ]);
            }
        }

        DB::commit();

    }
}
