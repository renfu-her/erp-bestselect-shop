<?php

namespace Database\Seeders;

use App\Enums\Delivery\Event;
use App\Models\Delivery;

use App\Models\ReceiveDepot;
use App\Models\SubOrders;
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

        $sub_order = SubOrders::getListWithShiGroupById(3)->get()->first();

        $delivery = Delivery::createData(
            Event::order()->value
            , $sub_order->id
            , $sub_order->sn
            , $sub_order->ship_temp_id
            , $sub_order->ship_temp
            , $sub_order->ship_category
            , $sub_order->ship_category_name
            , $sub_order->ship_group_id);
        if ($delivery['success'] == 0) {
            return $delivery;
        }
        $delivery_id = null;
        if (isset($delivery['id'])) {
            $delivery_id = $delivery['id'];
        }

        ReceiveDepot::setDatasWithDeliveryIdWithItemId(['inbound_id' => [2], 'qty' => [3]], $delivery_id, 3);
        ReceiveDepot::setDatasWithDeliveryIdWithItemId(['inbound_id' => [2], 'qty' => [1]], $delivery_id, 4);

        //收貨單成立 扣除入庫數量
        //ReceiveDepot::setUpData($delivery_id1);
    }
}
