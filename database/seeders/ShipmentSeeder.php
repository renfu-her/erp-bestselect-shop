<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShipmentCategory;
use App\Models\ShipmentGroup;
use App\Models\ShipmentMethod;
use App\Models\Temps;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shipmentCategoryHomeDeliveryId = ShipmentCategory::create(['code' => 'deliver',
                                                                    'category' => '宅配'])->id;
        $shipmentCategorySelfTakeId = ShipmentCategory::create(['code' => 'pickup',
                                                                'category' => '自取'])->id;
        $shipmentCategoryFamilyId = ShipmentCategory::create(['code' => 'family',
                                                              'category' => '全家'])->id;
        $bestMethodId = ShipmentMethod::create(['method' => '喜鴻出貨'])->id;
        $otherMethodId = ShipmentMethod::create(['method' => '廠商出貨'])->id;

        $bestFreezeGroupId = ShipmentGroup::create([
                                'category_fk' => $shipmentCategoryHomeDeliveryId,
                                'name' => 'BEST-宅配',
                                'temps_fk' => Temps::findTempsIdByName('冷凍'),
                                'method_fk' => $bestMethodId,
                                'note' => '不含箱子費用、不含離島地區',
                            ])->id;
        $best990GroupId = ShipmentGroup::create([
                            'category_fk' => $shipmentCategoryHomeDeliveryId,
                            'name' => 'BEST-宅配990免運',
                            'temps_fk' => Temps::findTempsIdByName('常溫'),
                            'method_fk' => $bestMethodId,
                            'note' => '不含箱子費用、不含離島地區',
                        ])->id;
        $familyGroupId = ShipmentGroup::create([
                            'category_fk' => $shipmentCategoryFamilyId,
                            'name' => '全家店到店',
                            'temps_fk' => Temps::findTempsIdByName('常溫'),
                            'method_fk' => $otherMethodId,
                            'note' => '限本島、不含離島地區',
                        ])->id;
        $freeRefrigeShipmentGroupId = ShipmentGroup::create([
                                        'category_fk' => $shipmentCategoryHomeDeliveryId,
                                        'name' => '宅配免運費',
                                        'temps_fk' => Temps::findTempsIdByName('冷藏'),
                                        'method_fk' => $otherMethodId,
                                        'note' => '不含箱子費用、不含離島地區',
                                    ])->id;
        $taipeiNormalGroupId = ShipmentGroup::create([
                                'category_fk' => $shipmentCategorySelfTakeId,
                                'name' => '台北公司自取',
                                'temps_fk' => Temps::findTempsIdByName('常溫'),
                                'method_fk' => $bestMethodId,
                                'note' => '不含箱子費用、不含離島地區',
                            ])->id;

//        BEST-宅配(冷凍)
        Shipment::create([
            'group_id_fk' => $bestFreezeGroupId,
            'min_price' => 0,
            'max_price' => 1990,
            'dlv_fee' => 200,
            'dlv_cost' => 60,
            'at_most' => 5,
            'is_above' => 'false',
        ]);
        Shipment::create([
            'group_id_fk' => $bestFreezeGroupId,
            'min_price' => 1990,
            'max_price' => 1990,
            'dlv_fee' => 0,
            'dlv_cost' => 60,
            'at_most' => 5,
            'is_above' => 'true',
        ]);

//        BEST-宅配990免運
        Shipment::create([
            'group_id_fk' => $best990GroupId,
            'min_price' => 0,
            'max_price' => 990,
            'dlv_fee' => 100,
            'dlv_cost' => 50,
            'at_most' => 5,
            'is_above' => 'false',
        ]);
        Shipment::create([
            'group_id_fk' => $best990GroupId,
            'min_price' => 990,
            'max_price' => 990,
            'dlv_fee' => 0,
            'dlv_cost' => 50,
            'at_most' => 5,
            'is_above' => 'true',
        ]);

        //      全家店到店
        Shipment::create([
            'group_id_fk' => $familyGroupId,
            'min_price' => 0,
            'max_price' => 0,
            'dlv_fee' => 60,
            'dlv_cost' => 60,
            'at_most' => 3,
            'is_above' => 'true',
        ]);

//        宅配免運費
        Shipment::create([
            'group_id_fk' => $freeRefrigeShipmentGroupId,
            'min_price' => 0,
            'max_price' => 0,
            'dlv_fee' => 0,
            'dlv_cost' => 50,
            'at_most' => 7,
            'is_above' => 'true',
        ]);

//        台北公司自取
        Shipment::create([
            'group_id_fk' => $taipeiNormalGroupId,
            'min_price' => 0,
            'max_price' => 0,
            'dlv_fee' => 0,
            'dlv_cost' => 40,
            'at_most' => 5,
            'is_above' => 'true',
        ]);
    }
}
