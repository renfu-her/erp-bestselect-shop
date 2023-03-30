<?php

namespace Database\Seeders;

use App\Enums\Delivery\Event;
use App\Models\Delivery;
use App\Models\DlvBack;
use App\Models\DlvOutStock;
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
                if (Event::order() == $delivery->event) {
                    $event = $delivery->event;
                    $sub_order = SubOrders::where('id', '=', $delivery->event_id)->first();
                    $event_id = $sub_order->order_id;
                    $sub_event_id = $delivery->event_id;
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
                    ]);
            }
        }
    }
}
