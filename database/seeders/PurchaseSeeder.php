<?php

namespace Database\Seeders;

use App\Models\PayingOrder;
use App\Models\ProductStock;
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
        $purchaseID1 = Purchase::createPurchase(
            1,
            5,
            '2021-12-22 00:00:00',
            '12345678',
            '1',
            '第一筆採購單',
            null,
            null,
        );

        Purchase::createPurchase(
            1,
            6,
            '2021-12-23 00:00:00',
            '87654321',
            '1',
            null,
            null,
            null,
        );

        $purchaseItemID = PurchaseItem::createPurchase(
            1,
            1,
            '第一筆採購單款式',
            'P21122800101',
            '100',
            10,
            1,
            'memo',
        );

        PayingOrder::createPayingOrder(
            $purchaseID1,
            1,
            '中信銀行',
            '822',
            'XX商行',
            '123456789098',
            100,
            '2021-12-13 00:00:00',
            0,
            '第一筆備註 訂金'
        );
        PayingOrder::createPayingOrder(
            $purchaseID1,
            1,
            '中信銀行',
            '822',
            'OO企業社',
            '987654321012',
            900,
            '2021-12-14 00:00:00',
            60,
            '第二筆備註 尾款'
        );
        PurchaseItem::updatePurchase(
            $purchaseItemID,
            '2022-12-14 00:00:00',
            1,
            0,
            '2021-12-15 00:00:00',
            100,
            1,
            5,
            0,
            '入庫OK',
        );
        ProductStock::stockChange(1, 100, 'purchase');
    }
}
