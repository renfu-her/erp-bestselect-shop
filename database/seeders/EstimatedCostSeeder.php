<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstimatedCostSeeder extends Seeder
{
    /**
     * 由原ERP匯入參考成本單價 estimated_cost
     *
     * @return void
     */
    public function run()
    {
        $data = DB::table('prd_product_styles')
            ->whereNull('deleted_at')
            ->join('product_content', 'product_content.SKUCode', '=', 'prd_product_styles.sku')
            ->select([
                'SKUCode',
                'sku',
                'Cost',
            ])
            ->get();

        foreach ($data as $datum) {
            DB::table('prd_product_styles')
                ->where('sku', $datum->SKUCode)
                ->update(['estimated_cost' => $datum->Cost]);
        }
    }
}
