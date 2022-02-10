<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Customer::createCustomer('Hans', 'hayashi0126@ittms.com.tw', '12345');
        Customer::createCustomer('小姜', 'program03@ittms.com.tw', '12345');
        Customer::createCustomer('理查', 'richardyuan30@gmail.com', '12345');
        Customer::createCustomer('阿君', 'ccps961032326@gmail.com', '12345');
        Customer::createCustomer('之谷', 'pntcwz@gmail.com', '12345');
        Customer::createCustomer('烏梅', 'hsihung08079@gmail.com', '12345');
        Customer::createCustomer('yoyo', 'yoyo@writingbeing.com', '12345');
    }
}
