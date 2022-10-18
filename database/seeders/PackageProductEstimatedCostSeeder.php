<?php

namespace Database\Seeders;

use App\Models\ProductStyleCombo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageProductEstimatedCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $styles = DB::table('prd_products as product')
            ->leftJoin('prd_product_styles as style', 'product.id', '=', 'style.product_id')
            ->select('style.id as style_id')
            ->where('product.type', 'c')
        //   ->whereIn('style.id', [3844, 3859])
        //  ->limit(10)
            ->get();

        $aa = 0;
        foreach ($styles as $value) {

            $re = ProductStyleCombo::estimatedCost($value->style_id);
            if ($re) {
                $aa++;
            }

        }

        echo $aa."筆 done";

    }
}
