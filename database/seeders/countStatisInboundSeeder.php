<?php

namespace Database\Seeders;

use App\Models\PcsStatisInbound;
use App\Models\PurchaseInbound;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class countStatisInboundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PcsStatisInbound::query()->update(['qty' => 0]);
        $inboundList = DB::table(app(PurchaseInbound::class)->getTable(). ' as inbound')
            ->select(
                'inbound.event'
                , 'inbound.product_style_id'
                , 'inbound.depot_id'
                , DB::raw('sum(inbound_num) as inbound_num')
                , DB::raw('sum(sale_num) as sale_num')
                , DB::raw('sum(csn_num) as csn_num')
                , DB::raw('sum(consume_num) as consume_num')
                , DB::raw('sum(back_num) as back_num')
                , DB::raw('sum(scrap_num) as scrap_num')
                , DB::raw('(sum(inbound_num) - sum(sale_num) - sum(csn_num) - sum(consume_num) - sum(back_num) - sum(scrap_num)) as remain_qty')
            )
            ->whereNull('inbound.deleted_at')
            ->groupBy('inbound.event')
            ->groupBy('inbound.product_style_id')
            ->groupBy('inbound.depot_id');
        $inboundList = $inboundList->get();
        if (isset($inboundList) && 0 < count($inboundList)) {
            foreach ($inboundList as $val_ib) {
                if (0 != $val_ib->remain_qty) {
                    PcsStatisInbound::updateData($val_ib->event, $val_ib->product_style_id, $val_ib->depot_id, $val_ib->remain_qty);
                }
            }
        }
    }
}
