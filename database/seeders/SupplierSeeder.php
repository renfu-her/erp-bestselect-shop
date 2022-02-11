<?php

namespace Database\Seeders;

use App\Enums\Supplier\Payment;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $supplier1 = Supplier::create([
            'name' => '喜鴻國際',
            'nickname' => '喜鴻',
            'vat_no' => '70381925',
            'contact_tel' => '(02)2536-2692',
            'contact_address' => '台北市松江路148號8樓之1',
            'contact_person' => '廠商窗口',
            'email' => 'xxx@xxx.com',
            'def_paytype' => Payment::cheque()->value,
            'memo' => '第一家廠商',
        ])->id;

        SupplierPayment::create([
            'supplier_id' => $supplier1,
            'type' => Payment::cheque()->value,
            'cheque_payable' => '第一筆支票抬頭',
        ]);
        SupplierPayment::create([
            'supplier_id' => $supplier1,
            'type' => Payment::remittance()->value,
            'bank_cname' => '喜鴻金庫',
            'bank_code' => '008',
            'bank_acount' => '喜鴻國際有限公司',
            'bank_numer' => '01234123456789',
        ]);
    }
}
