<?php

namespace Database\Seeders;

use App\Models\Supplier;
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
        Supplier::create([
            'name' => '喜鴻國際',
            'nickname' => '喜鴻',
            'vat_no' => '70381925',
            'chargeman' => '喜鴻負責人',
            'bank_cname' => '合作金庫',
            'bank_code' => '006',
            'bank_acount' => '喜鴻旅行社',
            'bank_numer' => '12345678901234',
            'contact_tel' => '(02)2536-2692',
            'contact_address' => '台北市松江路148號8樓之1',
            'contact_person' => '喜鴻聯絡人',
            'email' => 'xxx@xxx.com'
        ]);

        Supplier::create([
            'name' => '喜多方科技',
            'nickname' => '喜多方',
            'vat_no' => '12866611',
            'chargeman' => '喜多方',
            'bank_cname' => '喜多金庫',
            'bank_code' => '008',
            'bank_acount' => '喜多方科技有限公司',
            'bank_numer' => '01234123456789',
            'contact_tel' => '(03)532-9570',
            'contact_address' => '新竹市湳雅街311巷14號',
            'contact_person' => '喜多方聯絡人',
            'email' => 'ooo@ooo.com'
        ]);
    }
}
