<?php

namespace Database\Seeders;

use App\Enums\Purchase\InboundStatus;
use App\Models\PayingOrder;
use App\Models\Purchase;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\Supplier;
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
        $supplier = Supplier::where('id', '=', 1)->get()->first();

        $purchaseID1 = Purchase::createPurchase(
            $supplier->id,
            $supplier->name,
            $supplier->nickname,
            5,
            '之谷',
            '2021-12-22 00:00:00',
            '第一筆採購單',
        );

        $purchaseID2 = Purchase::createPurchase(
            $supplier->id,
            $supplier->name,
            $supplier->nickname,
            6,
            '之谷',
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

        $user_id_5 = 5;
        $user_name_5 = '之谷';
        $depot_id = 1;
        $depot_name = '集運本倉';


        $purchaseInbound1 = PurchaseInbound::createInbound(
            $purchaseID1,
            $product_style_id1,
            '2022-12-14 00:00:00',
            InboundStatus::not_yet()->value,
            null,
            0,
            0,
            $depot_id,
            $depot_name,
            $user_id_5,
            $user_name_5,
            null,
        );
        PurchaseInbound::delInbound($purchaseInbound1, $user_id_5);

        $purchaseInbound2 = PurchaseInbound::createInbound(
            $purchaseID1,
            $product_style_id1,
            '2022-12-14 00:00:00',
            InboundStatus::normal()->value,
            '2022-01-05 00:00:00',
            99,
            1,
            $depot_id,
            $depot_name,
            5,
            $user_name_5,
            '入庫OK 1物品退換貨',
        );
        PurchaseInbound::delInbound($purchaseInbound1, $user_id_5);
        $purchaseInbound3 = PurchaseInbound::createInbound(
            $purchaseID1,
            $product_style_id1,
            '2022-12-14 00:00:00',
            InboundStatus::overflow()->value,
            '2022-01-06 00:00:00',
            1,
            0,
            $depot_id,
            $depot_name,
            5,
            $user_name_5,
            '退換貨',
        );

        $sellCount = 2;
        PurchaseInbound::shippingInbound(
            $purchaseInbound2,
            $sellCount,
        );

        $errorCount = 1;
        PurchaseInbound::sendBackInbound(
            $purchaseInbound2,
            $errorCount,
        );
    }
}
