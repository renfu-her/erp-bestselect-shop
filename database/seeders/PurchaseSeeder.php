<?php

namespace Database\Seeders;

use App\Models\PayingOrder;
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
        $purchaseItemID1 = Purchase::createPurchase(
            1,
            5,
            '中信銀行',
            '822',
            'XX商行',
            '123456789098',
            '12345678',
            '1',
            0,
            '第一筆採購單',
            '2021-12-22 00:00:00',
            null,
        );

        Purchase::createPurchase(
            1,
            6,
            '中信銀行',
            '822',
            'OO企業社',
            '987654321012',
            '87654321',
            '1',
            0,
            null,
            null,
            null,
        );

        PurchaseItem::createPurchase(
            1,
            '100',
            10,
            null,
            1
        );

        PayingOrder::createPayingOrder(
            $purchaseItemID1,
            1,
            'ABCE',
            900,
            '2021-12-13 00:00:00',
            '第一筆備註 訂金'
        );
        PayingOrder::create([
            'purchase_id' => 1,
            'type' => 1,
            'order_num' => 'ABCE',
            'price' => 900,
        ]);

    }
}
