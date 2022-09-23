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
            ->join('product', 'product.ID', '=', 'product_content.PID')
            ->select([
                'SKUCode',
                'sku',
                'product.Cost',
            ])
            ->get();
        foreach ($data as $datum) {
            DB::table('prd_product_styles')
                ->where('sku', $datum->SKUCode)
                ->update(['estimated_cost' => $datum->Cost]);
        }

        $comboData = DB::table('prd_product_styles')
            ->where('prd_product_styles.type', '=','c')
            ->whereNull('deleted_at')
            ->join('ceremony_table', 'ceremony_table.SKUCode', '=', 'prd_product_styles.sku')
            ->select([
                'SKUCode',
                'prd_product_styles.sku',
                'ceremony_table.Cost',
            ])
            ->get();
        foreach ($comboData as $comboDatum) {
            DB::table('prd_product_styles')
                ->where('sku', $comboDatum->SKUCode)
                ->update(['estimated_cost' => $comboDatum->Cost]);
        }
    }
}
