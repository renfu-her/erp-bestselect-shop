<?php

namespace Database\Seeders;


use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentCategory;
use App\Models\ShipmentGroup;
use App\Models\ShipmentMethod;
use App\Models\Temps;
use App\Models\TikType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentETicketSeeder extends Seeder
{
    public function run()
    {
        // 取得或建立 eTicket 類別
        $shipmentCategoryETicket = ShipmentCategory::firstOrCreate(
            ['code' => 'eTicket'],
            ['category' => '電子票券']
        );
        $shipmentCategoryETicketId = $shipmentCategoryETicket->id;

        // 如果是新建立的，則建立相關資料
        if ($shipmentCategoryETicket->wasRecentlyCreated) {
            $bestMethodId = ShipmentMethod::where('method', '喜鴻出貨')->first()->id;

            $bestETicketGroupId = ShipmentGroup::create([
                'category_fk' => $shipmentCategoryETicketId,
                'name' => '確認收款後即時開票',
                'temps_fk' => Temps::findTempsIdByName('常溫'),
                'method_fk' => $bestMethodId,
                'note' => '確認收款後開票',
            ])->id;

            Shipment::create([
                'group_id_fk' => $bestETicketGroupId,
                'min_price' => 0,
                'max_price' => 99999,
                'dlv_fee' => 0,
                'dlv_cost' => 0,
                'at_most' => 99,
                'is_above' => 'false',
            ]);
        }

        // 找到資料表 Product.tik_type_id 等於 $shipmentCategoryETicketId
        $tikTypeEYoubon = TikType::where('code', 'eYoubon')->first();
        $products = Product::where('tik_type_id', $tikTypeEYoubon->id)->get();
        if ($products->count() > 0) {
            foreach ($products as $product) {
                Product::updateETicketProductShipment($product->id);
            }
        }
    }
}
