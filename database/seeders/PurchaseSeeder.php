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

        $purchase1 = Purchase::createPurchase(
            $supplier2->id,
            $supplier2->name,
            $supplier2->nickname,
            null,
            5,
            '之谷',
            '2021-12-22 00:00:00',
        );
        $purchaseID1 = null;
        if (isset($purchase1['id'])) {
            $purchaseID1 = $purchase1['id'];
        }
        $purchase2 = Purchase::createPurchase(
            $supplier->id,
            $supplier->name,
            $supplier->nickname,
            null,
            2,
            '小姜',
            '2021-12-23 00:00:00',
        );
        $purchaseID2 = null;
        if (isset($purchase2['id'])) {
            $purchaseID2 = $purchase2['id'];
        }

        $purchase3 = Purchase::createPurchase(
            $supplier2->id,
            $supplier2->name,
            $supplier2->nickname,
            null,
            2,
            '小姜',
            '2021-03-23 00:00:00',
        );
        $purchaseID3 = null;
        if (isset($purchase3['id'])) {
            $purchaseID3 = $purchase3['id'];
        }

        $operator_user_id = 5;
        $operator_user_name = '之谷';

        $product_style1 = ProductStyle::where('id', 1)->get()->first();
        $product_style2 = ProductStyle::where('id', 2)->get()->first();
        $product_style3 = ProductStyle::where('id', 3)->get()->first();
        $product_style4 = ProductStyle::where('id', 7)->get()->first();
        $product_style5 = ProductStyle::where('id', 8)->get()->first();

        $purchaseItem1 = PurchaseItem::createPurchase(
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
        $purchaseItemID1 = null;
        if (isset($purchaseItem1['id'])) {
            $purchaseItemID1 = $purchaseItem1['id'];
        }
        $purchaseItem2 = PurchaseItem::createPurchase(
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
        $purchaseItemID2 = null;
        if (isset($purchaseItem2['id'])) {
            $purchaseItemID2 = $purchaseItem2['id'];
        }
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
        $purchaseItem3 = PurchaseItem::createPurchase(
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
        $purchaseItemID3 = null;
        if (isset($purchaseItem3['id'])) {
            $purchaseItemID3 = $purchaseItem3['id'];
        }

        $purchaseItem4 = PurchaseItem::createPurchase(
            [
                'purchase_id' => $purchaseID3,
                'product_style_id' => $product_style2->id,
                'title' => '測試商品-'.$product_style2->title,
                'sku' => $product_style2->sku,
                'price' => '9900',
                'num' => 100,
                'temp_id' => null,
                'memo' => '遊輪販售專用'
            ],
            $operator_user_id,
            $operator_user_name
        );
        $purchaseItemID4 = null;
        if (isset($purchaseItem4['id'])) {
            $purchaseItemID4 = $purchaseItem4['id'];
        }
        $purchaseItem5 = PurchaseItem::createPurchase(
            [
                'purchase_id' => $purchaseID3,
                'product_style_id' => $product_style5->id,
                'title' => '茶葉金禮盒-'.$product_style5->title,
                'sku' => $product_style5->sku,
                'price' => '21750',
                'num' => 250,
                'temp_id' => null,
                'memo' => '節慶送禮'
            ],
            $operator_user_id,
            $operator_user_name
        );
        $purchaseItemID5 = null;
        if (isset($purchaseItem5['id'])) {
            $purchaseItemID5 = $purchaseItem5['id'];
        }

        //承辦人yoyo的user id
        $undertakerUserId = 7;

        PayingOrder::createPayingOrder(
            $purchaseID1,
            $undertakerUserId,
            0,
            100,
            '2021-12-13 00:00:00',
            '訂金測試1',
            '第一筆備註 訂金'
        );
        PayingOrder::createPayingOrder(
            $purchaseID1,
            $undertakerUserId,
            1,
            900,
            '2021-12-14 00:00:00',
            '訂金測試2',
            '第二筆備註 尾款'
        );

        $user_id_3 = 3;
        $user_id_5 = 5;
        $user_name_3 = '理查';
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
        $purchaseInboundID1 = null;
        if (isset($purchaseInbound1['id'])) {
            $purchaseInboundID1 = $purchaseInbound1['id'];
        }
        PurchaseInbound::delInbound($purchaseInboundID1, $user_id_5);

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
        $purchaseInboundID2 = null;
        if (isset($purchaseInbound2['id'])) {
            $purchaseInboundID2 = $purchaseInbound2['id'];
        }
        PurchaseInbound::delInbound($purchaseInboundID1, $user_id_5);
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
        $purchaseInbound6 = PurchaseInbound::createInbound(
            $purchaseID3,
            $purchaseItemID4,
            $product_style2->id,
            '2022-11-14 00:00:00',
            '2022-02-03 00:00:00',
            35,
            $depot_id,
            $depot_name,
            $user_id_3,
            $user_name_3,
            '退換貨',
        );
        $purchaseInbound7 = PurchaseInbound::createInbound(
            $purchaseID3,
            $purchaseItemID5,
            $product_style5->id,
            '2022-09-15 00:00:00',
            '2022-03-14 00:00:00',
            45,
            $depot_id,
            $depot_name,
            $user_id_3,
            $user_name_3,
            '退換貨',
        );

        $sellCount = 2;
        PurchaseInbound::shippingInbound(
            $purchaseInboundID2,
            $sellCount,
        );
    }
}
