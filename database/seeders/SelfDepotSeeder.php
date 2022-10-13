<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SelfDepotSeeder extends Seeder
{
    /**
     * 將以下宅配商品都加上這些常溫自取倉
        1.宅配990免運
        2酒類-常溫-100宅配
       將第2點以外的宅配商品的自取項目都移除
     *
     * @return void
     */
    public function run()
    {
        $shipIdData = DB::table('shi_group')
                        ->whereIn('name', [
                            '酒類-常溫-100宅配',
                            '常溫990免運',
                        ])
                        ->select('id')
                        ->get();
        $shipIds = [];
        foreach ($shipIdData as $shipIdDatum) {
            $shipIds[] = $shipIdDatum->id;
        }

        /**
         * 台北集運(限喜鴻員工)
        桃園集貨(限喜鴻員工)
        新竹集運(限喜鴻員工)
        台中集運(限喜鴻員工)
        台南集運(限喜鴻員工)
        高雄集運(限喜鴻員工)
         */
        $depIds = [
            5,
            6,
            7,
            8,
            9,
            10,
        ];

        $shipProductIds = DB::table('prd_product_shipment')
                                ->where('category_id', '1')
                                ->whereIn('group_id', $shipIds)
                                ->select('product_id')
                                ->get();
        $shipProductIdData = [];
        foreach ($shipProductIds as $shipProductId) {
            $shipProductIdData[] = $shipProductId->product_id;
        }

        foreach ($depIds as $depId) {
            foreach ($shipProductIdData as $shipProductId) {
                if (
                    DB::table('prd_pickup')
                    ->where('product_id_fk', $shipProductId)
                    ->where('depot_id_fk', $depId)
                    ->doesntExist()
                ){
                    DB::table('prd_pickup')
                        ->insert([
                            'product_id_fk' => $shipProductId,
                            'depot_id_fk'  => $depId,
                        ]);
                }
            }
        }

        $toDeleteProductIds = DB::table('prd_product_shipment')
            ->where('category_id', '1')
            ->whereNotIn('product_id', $shipProductIdData)
            ->select('product_id')
            ->get();
        $toDeleteProductIdArray = [];
        foreach ($toDeleteProductIds as $toDeleteProductId) {
            $toDeleteProductIdArray[] = $toDeleteProductId->product_id;
        }
        DB::table('prd_pickup')
            ->whereIn('product_id_fk', $toDeleteProductIdArray)
            ->delete();
    }
}
