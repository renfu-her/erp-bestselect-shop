<?php

namespace Database\Seeders;

use App\Enums\Delivery\Event;
use App\Models\Delivery;

use App\Models\PurchaseInbound;
use App\Models\ReceiveDepot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('dlv_logistic_status')->insert([
            [
                'title' => '新增',
                'content' => '',
                'code' => 'a10',
            ],
            [
                'title' => '檢貨中',
                'content' => '',
                'code' => 'a20',
            ],
            [
                'title' => '理貨中',
                'content' => '',
                'code' => 'a30',
            ],
            [
                'title' => '待配送',
                'content' => '',
                'code' => 'a40',
            ],
            [
                'title' => '配送中',
                'content' => '',
                'code' => 'b10',
            ],
            [
                'title' => '已送達',
                'content' => '',
                'code' => 'b20',
            ],
            [
                'title' => '未送達',
                'content' => '',
                'code' => 'b30',
            ],
            [
                'title' => '訂單取消',
                'content' => '',
                'code' => 'd40',
            ],
        ]);

        $shipmentQuery = DB::table('shi_group')
            ->leftJoin('shi_method', 'shi_method.id', '=', 'shi_group.method_fk')
            ->select('shi_group.id     as id'
                , 'shi_group.method_fk                as method_fk'
                , 'shi_method.method             as method'
            );

        $ord_sub_orders_id1 = 1;
        $ord_sub_orders_1 = DB::table('ord_sub_orders')->where('ord_sub_orders.id', $ord_sub_orders_id1)
            ->leftJoin(DB::raw("({$shipmentQuery->toSql()}) as shi"), function ($join) {
                $join->on('ord_sub_orders.ship_event_id', '=', 'shi.id');
            })
            ->where('ship_category', '=', 'deliver')
            ->mergeBindings($shipmentQuery)
            ->get()->first();

        $delivery_id1 = Delivery::createData(
            Event::order()->value
            , $ord_sub_orders_1->id
            , $ord_sub_orders_1->sn
            , $ord_sub_orders_1->ship_temp_id
            , $ord_sub_orders_1->ship_temp
            , $ord_sub_orders_1->ship_category
            , $ord_sub_orders_1->ship_category_name
            , null);

        $product_style_id = 1;
        $ord_items = DB::table('ord_items')
            ->select('ord_items.id as id'
                , 'ord_items.order_id as order_id'
                , 'ord_items.sub_order_id as sub_order_id'
                , 'ord_items.product_style_id as product_style_id'
                , 'ord_items.sku as sku'
                , 'ord_items.product_title as product_title'
                , 'ord_items.price as price'
                , 'ord_items.qty as qty'
                , 'ord_items.type as type'
                , 'ord_items.total_price as total_price'
            )
            ->where('sub_order_id', '=', $ord_sub_orders_id1)
            ->where('product_style_id', '=', $product_style_id)
            ->get()->first();

        $inbound_list = PurchaseInbound::getInboundList(['product_style_id' => $product_style_id])->get();
        $inbound_1 = $inbound_list[0]; //已刪除
        $inbound_2 = $inbound_list[1];
        $inbound_3 = $inbound_list[2];

//        $ReceiveDepot_id1 = ReceiveDepot::setData(
//            null,
//            $delivery_id1,
//            0,
//            $inbound_3->inbound_id,
//            $inbound_3->depot_id,
//            $inbound_3->depot_name,
//            $product_style_id,
//            $inbound_3->product_title. '-'. $inbound_3->style_title,
//            1,
//            $inbound_3->expiry_date);

        $ReceiveDepot_id2 = ReceiveDepot::setData(
            null,
            $delivery_id1,
            0,
            $inbound_2->inbound_id,
            $inbound_2->depot_id,
            $inbound_2->depot_name,
            $product_style_id,
            $inbound_2->style_sku,
            $inbound_2->product_title. '-'. $inbound_2->style_title,
            4,
            $inbound_2->expiry_date);

        //收貨單成立 扣除入庫數量
        //ReceiveDepot::setUpData($delivery_id1);
    }
}
