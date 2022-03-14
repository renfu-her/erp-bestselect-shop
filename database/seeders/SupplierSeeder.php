<?php

namespace Database\Seeders;

use App\Enums\Supplier\Payment;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('prd_suppliers_account')->insert(['account_code' => '幾號']);
        DB::table('prd_suppliers_account')->insert(['account_code' => '月底']);
        DB::table('prd_suppliers_account')->insert(['account_code' => '其它']);

        DB::table('prd_suppliers_invoice_ship')->insert(['invoice_code' => '郵寄']);
        DB::table('prd_suppliers_invoice_ship')->insert(['invoice_code' => '電子檔']);

        DB::table('prd_suppliers_invoice_date')->insert(['invoice_date_code' => '月底前']);
        DB::table('prd_suppliers_invoice_date')->insert(['invoice_date_code' => '次月幾日前']);
        DB::table('prd_suppliers_invoice_date')->insert(['invoice_date_code' => '其它']);

        DB::table('prd_suppliers_shipment')->insert(['shipment_code' => '未洽談']);
        DB::table('prd_suppliers_shipment')->insert(['shipment_code' => '洽談中']);
        DB::table('prd_suppliers_shipment')->insert(['shipment_code' => '是']);
        DB::table('prd_suppliers_shipment')->insert(['shipment_code' => '否']);

        $supplier1 = Supplier::create([
            'name' => '喜鴻國際',
            'nickname' => '喜鴻',
            'vat_no' => '70381925',
            'postal_code' => 123,
            'contact_tel' => '(02)2536-2692',
            'contact_address' => '台北市松江路148號8樓之1',
            'contact_person' => '廠商窗口',
            'job' => '業務',
            'extension' => '123',
            'fax' => '02-2511-8866',
            'mobile_line' => 'besttour',
            'invoice_address' => '台北市中山區松江路148號8樓',
            'invoice_postal_code' => 111,
            'invoice_recipient' => '王大明',
            'invoice_email' => 'besttour@besttour.com.tw',
            'invoice_phone' => '02-2522-7799',
            'invoice_date' => 20,
            'invoice_ship_fk' => '2',
            'invoice_date_fk' => '2',
            'shipping_address' => '桃園市經國路9號11樓之2',
            'shipping_postal_code' => 220,
            'shipping_recipient' => '陳小華',
            'shipping_phone' => '03-316-7121',
            'shipping_method_fk' => '1',
            'pay_date' => '2022-03-10 00:00:00',
            'account_fk' => '1',
            'account_date' => 15,
            'request_data' => '無',
            'email' => 'xxx@xxx.com',
            'def_paytype' => Payment::Cheque()->value,
            'memo' => '第一家廠商',
        ])->id;

        $supplier2 = Supplier::create([
            'name' => '茶衣創意行銷有限公司',
            'nickname' => '茶衣創意',
            'vat_no' => '27519267',
            'postal_code' => 414,
            'contact_tel' => '04-23350441',
            'contact_address' => '臺中市烏日區南里里溪南路三段117號1樓',
            'contact_person' => '林碧卿',
            'job' => '業務',
            'extension' => '123',
            'fax' => '04-1234-5678',
            'mobile_line' => 'sense',
            'invoice_address' => '臺中市烏日區南里里溪南路三段117號1樓',
            'invoice_postal_code' => 414,
            'invoice_recipient' => '王大明',
            'invoice_email' => 'mac.sense@msa.hinet.net',
            'invoice_phone' => '04-23350441',
            'invoice_date' => 20,
            'invoice_ship_fk' => '1',
            'invoice_date_fk' => '2',
            'shipping_address' => '臺中市烏日區南里里溪南路三段117號1樓',
            'shipping_postal_code' => 414,
            'shipping_recipient' => '陳小華',
            'shipping_phone' => '04-216-7121',
            'shipping_method_fk' => '1',
            'pay_date' => '2022-04-15 00:00:00',
            'account_fk' => '2',
            'account_date' => 15,
            'request_data' => '無',
            'email' => 'mac.sense@hinet.com',
            'def_paytype' => Payment::Cheque()->value,
            'memo' => '',
        ])->id;

        SupplierPayment::create([
            'supplier_id' => $supplier1,
            'type' => Payment::Cheque()->value,
            'cheque_payable' => '第一筆支票抬頭',
        ]);
        SupplierPayment::create([
            'supplier_id' => $supplier2,
            'type' => Payment::Cheque()->value,
            'cheque_payable' => '支票抬頭',
        ]);

        SupplierPayment::create([
            'supplier_id' => $supplier1,
            'type' => Payment::Remittance()->value,
            'bank_cname' => '喜鴻金庫',
            'bank_code' => '008',
            'bank_acount' => '喜鴻國際有限公司',
            'bank_numer' => '01234123456789',
        ]);
        SupplierPayment::create([
            'supplier_id' => $supplier2,
            'type' => Payment::Remittance()->value,
            'bank_cname' => '合作金庫長春分行',
            'bank_code' => '006',
            'bank_acount' => '喜鴻旅行社有限公司',
            'bank_numer' => '0844-717-214289',
        ]);
    }
}
