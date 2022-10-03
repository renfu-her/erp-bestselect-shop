<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorrectionSubOrderPickupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $re = DB::table('ord_orders as order')
            ->leftJoin('ord_sub_orders as sub_order', 'order.id', '=', 'sub_order.order_id')
            ->select(['sub_order.id', 'sub_order.ship_category', 'sub_order.ship_event_id'])
            ->where('sub_order.ship_category', 'pickup')
            ->where('sub_order.ship_event_id', '>', 20)->get();
        $c = 0;
        foreach ($re as $value) {
            $d = DB::table('prd_pickup')->where('id', $value->ship_event_id)->get()->first();
            if ($d) {
                DB::table('ord_sub_orders')->where('id', $value->id)->update([
                    'ship_event_id' => $d->depot_id_fk,
                ]);
                $c++;
            }
        }

        echo "$c done";
        DB::commit();

        //prd_pickup

    }
}
