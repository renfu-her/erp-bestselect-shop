<?php

namespace Database\Seeders;

use App\Enums\Purchase\InboundStatus;
use App\Models\PayingOrder;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
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
            '第一筆採購單',
        );

        $purchaseID2 = Purchase::createPurchase(
            1,
            6,
            '2021-12-23 00:00:00',
            null,
        );

        $product_style_id1 = 1;
        $product_style_id2 = 2;
        $purchaseItemID1 = PurchaseItem::createPurchase(
            $purchaseID1,
            $product_style_id1,
            '測試商品-M',
            'P22010600101',
            '11',
            10,
            null,
            '第一筆款式',
        );
        $purchaseItemID2 = PurchaseItem::createPurchase(
            $purchaseID1,
            $product_style_id2,
            '測試商品-X',
            'P22010600102',
            '12',
            10,
            null,
            '第二筆款式',
        );

        PurchaseItem::createPurchase(
            $purchaseID2,
            $product_style_id1,
            '測試商品-M',
            'P22010600101',
            '13',
            10,
            null,
            null,
        );

        PayingOrder::createPayingOrder(
            $purchaseID1,
            0,
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

        $purchaseInbound1 = PurchaseInbound::createInbound(
            $purchaseItemID1,
            '2022-12-14 00:00:00',
            InboundStatus::not_yet()->value,
            null,
            0,
            0,
            1,
            5,
            null,
            null,
        );
        ProductStock::stockChange($product_style_id1, 0, 'purchase', $purchaseInbound1, InboundStatus::not_yet()->description);
        $purchaseInbound2 = PurchaseInbound::createInbound(
            $purchaseItemID1,
            '2022-12-14 00:00:00',
            InboundStatus::normal()->value,
            '2022-01-05 00:00:00',
            99,
            1,
            1,
            5,
            '2022-01-05 00:00:00',
            '入庫OK 1物品退換貨',
        );
        ProductStock::stockChange($product_style_id1, 99, 'purchase', $purchaseInbound1, InboundStatus::shortage()->description);
        $purchaseInbound3 = PurchaseInbound::createInbound(
            $purchaseItemID1,
            '2022-12-14 00:00:00',
            InboundStatus::overflow()->value,
            '2022-01-06 00:00:00',
            1,
            0,
            1,
            5,
            '2022-01-05 00:00:00',
            '退換貨',
        );
        ProductStock::stockChange($product_style_id1, 1, 'purchase', $purchaseInbound1, InboundStatus::normal()->description);
        PurchaseInbound::updateInbound(
            $purchaseInbound2,
            '2022-12-14 00:00:00',
            InboundStatus::shortage()->value,
            '2022-01-06 00:00:00',
            10,
            0,
            1,
            5,
            '2022-01-06 00:00:00',
            0,
            '廠商贈送',
        );
        ProductStock::stockChange($product_style_id1, 10, 'purchase', $purchaseInbound1, InboundStatus::overflow()->description);

        $sellCount = 2;
        PurchaseInbound::sellInbound(
            $purchaseInbound2,
            $sellCount,
        );
        ProductStock::stockChange($product_style_id1, -1 * $sellCount, 'purchase', $purchaseInbound1, '銷售數量 '. $sellCount);

    }
}
