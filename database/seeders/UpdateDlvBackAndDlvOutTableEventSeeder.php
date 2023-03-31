<?php

namespace Database\Seeders;

use App\Enums\Delivery\Event;
use App\Models\ConsignmentItem;
use App\Models\Delivery;
use App\Models\DlvBack;
use App\Models\DlvOutStock;
use App\Models\OrderItem;
use App\Models\ProductStyle;
use App\Models\SubOrders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateDlvBackAndDlvOutTableEventSeeder extends Seeder
{
    public function run()
    {
        $dlvbacklist = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')->get();
        $this->updateEvent($dlvbacklist, app(DlvBack::class)->getTable());

        $dlvoutlist = DB::table(app(DlvOutStock::class)->getTable(). ' as dlv_out')->get();
        $this->updateEvent($dlvoutlist, app(DlvOutStock::class)->getTable());
        echo 'done';
    }

    private function updateEvent($queryList, $tableName) {
        if ($queryList && count($queryList) > 0) {
            foreach ($queryList as $item) {
                $delivery = DB::table(app(Delivery::class)->getTable(). ' as delivery')
                    ->where('delivery.id', $item->delivery_id)
                    ->get()->first();

                $event = null;
                $event_id = null;
                $sub_event_id = null;

                //計算毛利
                $gross_profit = $this->calc_gross_profit($delivery->event, $item->product_style_id, $item->event_item_id, $item->qty);
                if (Event::order()->value == $delivery->event) {
                    $event = $delivery->event;
                    $sub_order = SubOrders::where('id', '=', $delivery->event_id)->first();
                    $event_id = $sub_order->order_id;
                    $sub_event_id = $delivery->event_id;
                } else if (Event::consignment()->value == $delivery->event) {
                    $event = $delivery->event;
                    $event_id = $delivery->event_id;
                } else {
                    $event = $delivery->event;
                    $event_id = $delivery->event_id;
                }
                DB::table($tableName)
                    ->where('id', '=', $item->id)
                    ->update([
                        'event' => $event,
                        'event_id' => $event_id,
                        'sub_event_id' => $sub_event_id,
                        'gross_profit' => $gross_profit,
                    ]);
            }
        }
    }

    //計算毛利
    private function calc_gross_profit($event, $product_style_id, $event_item_id, $back_qty) {
        $gross_profit = 0;
        if ($product_style_id) {
            $style = ProductStyle::where('id', '=', $product_style_id)->first();
            if (Event::order()->value == $event) {
                $ordItem = OrderItem::where('id', '=', $event_item_id)->first();
                $gross_profit = $ordItem->price * $back_qty - $style->estimated_cost * $back_qty;
            } else if (Event::consignment()->value == $event) {
                $csnItem = ConsignmentItem::where('id', '=', $event_item_id)->first();
                $gross_profit = $csnItem->price * $back_qty - $style->estimated_cost * $back_qty;
            }
        }
        return $gross_profit;
    }
}
