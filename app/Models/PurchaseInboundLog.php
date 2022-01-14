<?php

namespace App\Models;

use App\Enums\InboundEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseInboundLog extends Model
{
    use HasFactory;
    protected $table = 'pcs_inbound_log';
    protected $guarded = [];

    public static function stockChange($inbound_id, $qty, $event, $note = null)
    {
        if (!is_numeric($qty)) {
            return ['success' => 0, 'error_msg' => 'qty type error'];
        }

        if (!InboundEvent::hasKey($event)) {
            return ['success' => 0, 'error_msg' => 'event error'];
        }

        if (!PurchaseInbound::where('id', $inbound_id)->get()->first()) {
            return ['success' => 0, 'error_msg' => 'inbound not exists'];
        }

        return DB::transaction(function () use ($inbound_id, $qty, $event, $note) {
            $inbound = PurchaseInbound::where('id', $inbound_id)->get()->first();

            self::create(['inbound_id' => $inbound_id,
                'qty' => $qty,
                'event' => $event,
                'note' => $note]);

            return ['success' => 1];

        });
    }

}
