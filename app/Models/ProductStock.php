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
            return ['success' => 0, 'error_msg' => 'qty type error'];
        }

        if (!StockEvent::hasKey($event)) {
            return ['success' => 0, 'error_msg' => 'event error'];
        }

        if (!ProductStyle::where('id', $product_style_id)->get()->first()) {
            return ['success' => 0, 'error_msg' => 'style not exists'];
        }

        return DB::transaction(function () use ($product_style_id, $qty, $event, $event_id, $note) {

            ProductStyle::where('id', $product_style_id)
                ->update(['in_stock' => DB::raw("in_stock + $qty")]);

            if (ProductStyle::where('id', $product_style_id)->where('in_stock', '<', 0)->get()->first()) {
                DB::rollBack();
                return ['success' => 0, 'error_msg' => '數量超出範圍'];
            }

            self::create(['product_style_id' => $product_style_id,
                'qty' => $qty,
                'event' => $event,
                'event_id' => $event_id,
                'note' => $note]);

            return ['success' => 1];

        });
    }

    public static function comboProcess($style_id, $qty)
    {
        return DB::transaction(function () use ($style_id, $qty) {

            $style = ProductStyle::where('id', $style_id)->whereNotNull('sku')->where('type', 'c')->get()->first();
            if (!$style) {
                return ['success' => 0, 'error_msg' => '無此商品'];
            }

            $combos = ProductStyleCombo::where('product_style_id', $style_id)->get()->toArray();

            foreach ($combos as $combo) {
                $_qty = $qty * -1 * $combo['qty'];

                ProductStyle::where('id', $combo['id'])
                    ->update(['in_stock' => DB::raw("in_stock + $_qty")]);

                if (ProductStyle::where('id', $combo['id'])->where('in_stock', '<', 0)->get()->first()) {
                    DB::rollBack();
                    return ['success' => 0, 'error_msg' => '數量超出範圍'];
                }

                self::create(['product_style_id' => $combo['id'],
                    'qty' => $_qty,
                    'event' => 'combo',
                    'event_id' => $style_id,
                    'note' => ($qty < 0) ? '拆包' : '合包']);

            }

            ProductStyle::where('id', $style_id)
                ->update(['in_stock' => DB::raw("in_stock + $qty")]);

            if (ProductStyle::where('id', $style_id)->where('in_stock', '<', 0)->get()->first()) {
                DB::rollBack();
                return ['success' => 0, 'error_msg' => '數量超出範圍'];
            }

            self::create(['product_style_id' => $style_id,
                'qty' => $qty,
                'event' => 'combo',
                'event_id' => null,
                'note' => '']);

            return ['success' => 1];

        });

    }
}
