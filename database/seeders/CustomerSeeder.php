<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('usr_identity')->insert([[
            'title' => '消費者',
            'code' => 'customer',
            'can_bind' => 1,
        ], [
            'title' => '喜鴻員工',
            'code' => 'employee',
            'can_bind' => 1,
        ], [
            'title' => '企業會員',
            'code' => 'company',
            'can_bind' => 0,
        ]]);

        Customer::createCustomer('Hans', 'hayashi0126@gmail.com', '12345');
        Customer::createCustomer('小姜', 'program03@ittms.com.tw', '12345');
        Customer::createCustomer('理查', 'richardyuan30@gmail.com', '12345');
        Customer::createCustomer('阿君', 'ccps961032326@gmail.com', '12345');
        Customer::createCustomer('之谷', 'pntcwz@gmail.com', '12345');
        Customer::createCustomer('烏梅', 'hsihung08079@gmail.com', '12345');
        Customer::createCustomer('yoyo', 'yoyo@writingbeing.com', '12345');
    }
}
