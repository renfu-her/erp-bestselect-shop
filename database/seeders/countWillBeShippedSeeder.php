<?php

namespace Database\Seeders;

use App\Models\ProductStyle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class countWillBeShippedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductStyle::query()->update(['will_be_shipped' => 0]);
        $re = DB::table('ord_orders as order')
            ->leftJoin('ord_sub_orders as sub_order', 'order.id', '=', 'sub_order.order_id')
            ->leftJoin('ord_items as item', 'item.sub_order_id', '=', 'sub_order.id')
            ->select([
                'item.product_style_id', 'item.qty',
            ])
            ->where('order.status_code', '<>', 'canceled')
            ->get();

        foreach ($re as $p) {
            ProductStyle::willBeShipped($p->product_style_id, $p->qty);
        }

        
    }
}
