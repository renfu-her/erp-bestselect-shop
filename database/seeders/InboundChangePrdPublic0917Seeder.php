<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InboundChangePrdPublic0917Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //找到目前入庫單尚未審核的商品id
        $query = DB::select('SELECT
            prd.id, prd.title
            FROM pcs_inbound_inventory as inv
            left join pcs_purchase_inbound  as inbound on inbound.id = inv.inbound_id
            left join prd_product_styles as style on style.id = inbound.product_style_id
            left join prd_products as prd on prd.id = style.product_id
            where inv.status = 0
            group by prd.id');
        $ids = [];
        if (isset($query) && 0 < count($query)) {
            foreach ($query as $item) {
                $ids[] = $item->id;
            }
        }
//        dd($query, $ids);
        Product::whereIn('id', $ids)->update([
            'public' => 0
        ]);
        echo "相關商品修改為不公開 完成";
    }
}
