<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderPaymentMethod;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Product::changePickup(1, [1, 2, 3]);
        Product::changeShipment(1, 1, 1);

        OrderPaymentMethod::insert([
            ['title' => '現金', 'code' => 'cash'],
            ['title' => '信用卡', 'code' => 'credit'],
            ['title' => '匯款', 'code' => 'remit'],
        ]);

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
        ];

        dd(Order::createOrder('hayashi0126@gmail.com', 1, 'cash', $address, $items));
    }
}
