<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('ord_order_status')->insert([
            [
                'title' => '新增',
                'content' => '新增訂單',
                'code' => 'O01',
            ],
            [
                'title' => '訂單取消',
                'content' => '',
                'code' => 'O02',
            ],
            [
                'title' => '已結案',
                'content' => '',
                'code' => 'O03',
            ],
            [
                'title' => '已收款',
                'content' => '已收錢但會計未確認',
                'code' => 'P01',
            ],
            [
                'title' => '已入款',
                'content' => '會計已確定收到款項',
                'code' => 'P02',
            ],
            [
                'title' => '刪除收款單',
                'content' => '把收款單刪掉',
                'code' => 'P03',
            ],

        ]);

//        Product::changePickup(1, [1, 2, 3]);
//        Product::changePickup(2, [1, 2, 3]);
//        Product::changeShipment(1, 1, 1);
//        Product::changeShipment(2, 1, 1);
//
//
//        ProductStock::comboProcess(4, 5);
//        ProductStock::comboProcess(5, 6);

        $address = [
            ['name' => 'hans', 'phone' => '0123313', 'address' => '桃園市八德區永福街', 'type' => 'reciver'],
            ['name' => 'hans', 'phone' => '0123313', 'address' => '桃園市八德區永福街', 'type' => 'orderer'],
            ['name' => 'hans', 'phone' => '0123313', 'address' => '桃園市八德區永福街', 'type' => 'sender'],
        ];

        $items = [
            [
                'product_id' => 1,
                'product_style_id' => 1,
                'customer_id' => 1,
                'qty' => 10,
                'shipment_type' => 'deliver',
                'shipment_event_id' => 1,
            ],
            [
                'product_id' => 1,
                'product_style_id' => 1,
                'customer_id' => 1,
                'qty' => 2,
                'shipment_type' => 'pickup',
                'shipment_event_id' => 2,
            ],
            [
                'product_id' => 1,
                'product_style_id' => 1,
                'customer_id' => 1,
                'qty' => 2,
                'shipment_type' => 'pickup',
                'shipment_event_id' => 3,
            ],
            [
                'product_id' => 2,
                'product_style_id' => 4,
                'customer_id' => 1,
                'qty' => 2,
                'shipment_type' => 'pickup',
                'shipment_event_id' => 3,
            ],
        ];

//        $val = Order::createOrder('hayashi0126@gmail.com', 1, $address, $items, '備註');

    }
}
