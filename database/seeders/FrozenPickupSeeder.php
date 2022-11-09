<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrozenPickupSeeder extends Seeder
{
    /**
     *將宅配為"冷凍1599免運"的商品的自取都設定上冷凍的自取，例如台北集運-冷凍
     *
     * @return void
     */
    public function run()
    {
        $frozenId = DB::table('shi_temps')
            ->where('temps', '=', '冷凍')
            ->select('id')
            ->get()
            ->first()
            ->id;
        $depotIds = DB::table('depot_temp')
            ->where('temp_id', '=', $frozenId)
            ->select('depot_id')
            ->get();
        $depotFrozenIds = [];
        foreach ($depotIds as $depotId) {
            $depotFrozenIds[] = $depotId->depot_id;
        }
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
                ->whereIn('prd_pickup.depot_id_fk', $depotFrozenIds)
                ->doesntExist()
            ) {
                foreach ($depotFrozenIds as $depotFrozenId) {
                    DB::table('prd_pickup')->insert([
                        'product_id_fk' => $productId->product_id,
                        'depot_id_fk' => $depotFrozenId,
                    ]);
                }
            }
        }
    }
}
