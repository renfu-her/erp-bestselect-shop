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

    public static function stockChange($product_style_id, $qty, $event, $event_id = null, $note = null, $is_inbound = false)
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

        return DB::transaction(function () use ($product_style_id, $qty, $event, $event_id, $note, $is_inbound) {
            $style = ProductStyle::where('id', $product_style_id)->select('id','in_stock')->get()->first();
            if ($style['in_stock'] + $qty < 0) {
                return ['success' => 0, 'error_msg' => '數量超出範圍'];
            }

            $product_style = ProductStyle::where('id', $product_style_id);
            $product_style_get = $product_style->get()->first();

            if (null != $product_style_get) {
                //判斷若為入庫單，則總進貨量total_inbound需加上數量
                if (false == $is_inbound) {
                    $product_style->update(['in_stock' => DB::raw("in_stock + $qty")]);
                } else {
                    $product_style->update([
                        'total_inbound' => DB::raw("total_inbound + $qty")
                        , 'in_stock' => DB::raw("in_stock + $qty")
                    ]);
                }
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
                $re = self::stockChange($combo['product_style_child_id'], $_qty, 'combo', $style_id);
                if (!$re['success']) {
                    DB::rollBack();
                    return $re;
                }
            }

            $re = self::stockChange($style_id, $qty, 'combo');
            if (!$re['success']) {
                DB::rollBack();
                return $re;
            }

            return ['success' => 1];

        });

    }
}
