<?php

namespace Database\Seeders;

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
      
        $aa = [];
        foreach ($styles as $value) {
            $re = DB::table('prd_style_combos as combo')
                ->leftJoin('prd_product_styles as style', 'combo.product_style_child_id', '=', 'style.id')
                ->select(['combo.product_style_id as style_id',
                'style.estimated_cost',
                'combo.qty'
                ])
                ->selectRaw('SUM(style.estimated_cost * combo.qty) as total')
                ->where('combo.product_style_id', $value->style_id)
                ->groupBy('combo.product_style_id')->get()->first();

          
            
            DB::table('prd_product_styles')->where('id',$value->style_id)->update([
                'estimated_cost'=>$re->total
            ]);

        }

       

    }
}
