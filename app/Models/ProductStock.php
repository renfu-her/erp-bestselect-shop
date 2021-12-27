<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductStock extends Model
{
    use HasFactory;
    protected $table = 'prd_stock_log';
    protected $guarded = [];

    public static function stockChange($style_id, $qty, $event, $event_id = null, $note = null)
    {

        if (!ProductStyle::where('id', $style_id)->get()->first()) {
            return;
        }

        return DB::transaction(function () use ($style_id, $qty, $event, $event_id, $note) {
            self::create(['style_id' => $style_id,
                'qty' => $qty,
                'event' => $event,
                'event_id' => $event_id,
                'note' => $note]);

            ProductStyle::where('id', $style_id)
                ->update(['in_stock' => DB::raw("in_stock + $qty")]);

        });
    }
}
