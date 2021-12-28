<?php

namespace App\Models;

use App\Enums\StockEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductStock extends Model
{
    use HasFactory;
    protected $table = 'prd_stock_log';
    protected $guarded = [];

    public static function stockChange($product_style_id, $qty, $event, $event_id = null, $note = null)
    {

        if (!is_numeric($qty)) {
            DB::rollBack();
            return ['success' => 0, 'error_msg' => 'qty type error'];
        }

        if (!StockEvent::hasKey($event)) {
            DB::rollBack();
            return ['success' => 0, 'error_msg' => 'event error'];
        }

        if (!ProductStyle::where('id', $product_style_id)->get()->first()) {
            DB::rollBack();
            return ['success' => 0, 'error_msg' => 'style not exists'];
        }

        return DB::transaction(function () use ($product_style_id, $qty, $event, $event_id, $note) {

            ProductStyle::where('id', $product_style_id)
                ->update(['in_stock' => DB::raw("in_stock + $qty")]);

            if (ProductStyle::where('id', $product_style_id)->where('in_stock', '<', 0)->get()->first()) {
                DB::rollBack();
                return ['success' => 0, 'error_msg' => 'style overdraft'];
            }

            self::create(['product_style_id' => $product_style_id,
                'qty' => $qty,
                'event' => $event,
                'event_id' => $event_id,
                'note' => $note]);

            return ['success' => 1];

        });
    }
}
