<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstimatedCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
