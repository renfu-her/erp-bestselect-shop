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

        ReceiveDepot::setDatasWithDeliveryIdWithItemId(['inbound_id' => [2], 'qty' => [3]], 1, 3);
        ReceiveDepot::setDatasWithDeliveryIdWithItemId(['inbound_id' => [2], 'qty' => [1]], 1, 4);

        //收貨單成立 扣除入庫數量
        //ReceiveDepot::setUpData($delivery_id1);
    }
}
