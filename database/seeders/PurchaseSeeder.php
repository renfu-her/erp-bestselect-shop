<?php

namespace Database\Seeders;

use App\Models\PayingOrder;
use App\Models\ProductStyle;
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
        $supplier2 = Supplier::where('id', '=', 2)->get()->first();

        $purchaseID1 = Purchase::createPurchase(
            $supplier2->id,
            $supplier2->name,
            $supplier2->nickname,
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
            '小明',
            '2021-12-23 00:00:00',
            null,
        );
        $operator_user_id = 5;
        $operator_user_name = '之谷';

        $product_style1 = ProductStyle::where('id', 1)->get()->first();
        $product_style2 = ProductStyle::where('id', 2)->get()->first();
        $product_style3 = ProductStyle::where('id', 3)->get()->first();

        $purchaseItemID1 = PurchaseItem::createPurchase(
            [
                'purchase_id' => $purchaseID1,
                'product_style_id' => $product_style1->id,
                'title' => '測試商品-'.$product_style1->title,
                'sku' => $product_style1->sku,
                'price' => '11',
                'num' => 10,
                'temp_id' => null,
                'memo' => '第一筆款式'
            ],
            $operator_user_id,
            $operator_user_name
        );
        $purchaseItemID2 = PurchaseItem::createPurchase(
            [
                'purchase_id' => $purchaseID1,
                'product_style_id' => $product_style2->id,
                'title' => '測試商品-'.$product_style2->title,
                'sku' => $product_style2->sku,
                'price' => '12',
                'num' => 10,
                'temp_id' => null,
                'memo' => '第二筆款式'
            ],
            $operator_user_id,
            $operator_user_name
        );
        PurchaseItem::createPurchase(
            [
                'purchase_id' => $purchaseID2,
                'product_style_id' => $product_style1->id,
                'title' => '測試商品-'.$product_style1->title,
                'sku' => $product_style1->sku,
                'price' => '13',
                'num' => 10,
                'temp_id' => null,
                'memo' => '第三筆款式'
            ],
            $operator_user_id,
            $operator_user_name
        );
        $purchaseItemID3 = PurchaseItem::createPurchase(
            [
                'purchase_id' => $purchaseID1,
                'product_style_id' => $product_style3->id,
                'title' => '測試商品-'.$product_style3->title,
                'sku' => $product_style3->sku,
                'price' => '13',
                'num' => 13,
                'temp_id' => null,
                'memo' => null
            ],
            $operator_user_id,
            $operator_user_name
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
            $purchaseItemID1,
            $product_style1->id,
            '2022-12-14 00:00:00',
            null,
            5,
            $depot_id,
            $depot_name,
            $user_id_5,
            $user_name_5,
            null,
        );
        PurchaseInbound::delInbound($purchaseInbound1, $user_id_5);

        $purchaseInbound2 = PurchaseInbound::createInbound(
            $purchaseID1,
            $purchaseItemID1,
            $product_style1->id,
            '2022-12-14 00:00:00',
            '2022-01-05 00:00:00',
            99,
            $depot_id,
            $depot_name,
            5,
            $user_name_5,
            '入庫OK 1物品退換貨',
        );
        PurchaseInbound::delInbound($purchaseInbound1, $user_id_5);
        $purchaseInbound3 = PurchaseInbound::createInbound(
            $purchaseID1,
            $purchaseItemID1,
            $product_style1->id,
            '2022-12-14 00:00:00',
            '2022-01-06 00:00:00',
            1,
            $depot_id,
            $depot_name,
            5,
            $user_name_5,
            '退換貨',
        );

        $purchaseInbound4 = PurchaseInbound::createInbound(
            $purchaseID2,
            $purchaseItemID2,
            $product_style2->id,
            '2022-11-14 00:00:00',
            '2022-02-03 00:00:00',
            25,
            $depot_id,
            $depot_name,
            5,
            $user_name_5,
            '退換貨',
        );
        $purchaseInbound5 = PurchaseInbound::createInbound(
            $purchaseID1,
            $purchaseItemID3,
            $product_style3->id,
            '2022-11-14 00:00:00',
            '2022-02-03 00:00:00',
            26,
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
    }
}
