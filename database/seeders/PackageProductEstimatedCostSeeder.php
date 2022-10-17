<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ProductStyleCombo;
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
      
        $aa = [];
        foreach ($styles as $value) {

            ProductStyleCombo::estimatedCost($value->style_id);
            

        }

       

    }
}
