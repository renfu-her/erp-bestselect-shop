<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrozenPickupTaiChongSeeder extends Seeder
{
    /**
     *將宅配為"冷凍1599免運"的商品自取都補上「台中」冷凍的自取
     *
     * @return void
     */
    public function run()
    {
        $taiChungDepotId = DB::table('depot')
            ->where('name', '=', '台中集運-冷凍(限喜鴻員工)')
            ->select('id')
            ->get()
            ->first()
            ->id;

        $productIds = DB::table('shi_group')
            ->where('shi_group.name', '=', '冷凍1599免運')
            ->leftJoin('prd_product_shipment', 'prd_product_shipment.group_id', '=', 'shi_group.id')
            ->select([
                'prd_product_shipment.product_id',
            ])
            ->get();
        foreach ($productIds as $productId) {
            if (DB::table('prd_pickup')
                ->where('prd_pickup.product_id_fk', '=', $productId->product_id)
                ->where('prd_pickup.depot_id_fk', '=', $taiChungDepotId)
                ->doesntExist()
            ) {
                DB::table('prd_pickup')->insert([
                    'product_id_fk' => $productId->product_id,
                    'depot_id_fk' => $taiChungDepotId,
                ]);
            }
        }
    }
}
