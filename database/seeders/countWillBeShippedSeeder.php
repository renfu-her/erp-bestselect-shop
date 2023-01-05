<?php

namespace Database\Seeders;

use App\Models\DlvOutStock;
use App\Models\ProductStyle;
use Illuminate\Database\Seeder;

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

        //訂單未出貨
        //減回訂單缺貨未出貨
        $re = DlvOutStock::getAllOrderToDlvQty()->get();
        foreach ($re as $p) {
            ProductStyle::willBeShipped($p->product_style_id, $p->stock_qty);
        }

        //寄倉未出貨
        //減回寄倉缺貨未出貨
        $re_csn = DlvOutStock::getAllCsnToDlvQty()->get();
//        dd($re, $re_csn);
        foreach ($re_csn as $p) {
            ProductStyle::willBeShipped($p->product_style_id, $p->stock_qty);
        }
    }
}
