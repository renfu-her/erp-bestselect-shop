<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Purchase::create([
            'supplier_id' => 1,
            'purchase_id' => 5,
            'bank_cname' => '中信銀行',
            'bank_code' => '822',
            'bank_acount' => 'XX商行',
            'bank_numer' => '123456789098',
            'Invoice_num' => '12345678',
            'pay_type' => '1',
        ]);
        Purchase::create([
            'supplier_id' => 1,
            'purchase_id' => 6,
            'bank_cname' => '中信銀行',
            'bank_code' => '822',
            'bank_acount' => 'OO企業社',
            'bank_numer' => '987654321012',
            'Invoice_num' => '87654321',
            'pay_type' => '2',
        ]);

        PurchaseItem::create([
            'ps_id' => 1,
            'price' => '100',
            'num' => 10,
            'temp_id' => 1,
        ]);
    }
}
