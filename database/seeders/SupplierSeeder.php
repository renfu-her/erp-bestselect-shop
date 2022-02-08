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

        $supplier2 = Supplier::create([
            'name' => '喜多方科技',
            'nickname' => '喜多方',
            'vat_no' => '12866611',
            'contact_tel' => '(03)532-9570',
            'contact_address' => '新竹市湳雅街311巷14號',
            'contact_person' => '喜多方廠商窗口',
            'email' => 'ooo@ooo.com',
            'def_paytype' => Payment::cash()->value,
            'memo' => '第二家廠商'
        ])->id;

        SupplierPayment::create([
            'supplier_id' => $supplier1,
            'type' => Payment::cheque()->value,
            'cheque_payable' => '第一筆支票抬頭',
        ]);
        SupplierPayment::create([
            'supplier_id' => $supplier1,
            'type' => Payment::remittance()->value,
            'bank_cname' => '喜多金庫',
            'bank_code' => '008',
            'bank_acount' => '喜多方科技有限公司',
            'bank_numer' => '01234123456789',
        ]);
        SupplierPayment::create([
            'supplier_id' => $supplier2,
            'type' => Payment::cash()->value,
        ]);
    }
}
