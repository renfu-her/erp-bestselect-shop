<?php

namespace App\Models;

use App\Enums\StockEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductStock extends Model
{
    use HasFactory;
    protected $table = 'prd_stock_log';
    protected $guarded = [];

    public static function stockChange($product_style_id, $qty, $event, $event_id = null, $note = null, $is_pcs_inbound = false, $inbound_can_tally = false)
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

        return DB::transaction(function () use ($product_style_id, $qty, $event, $event_id, $note, $is_pcs_inbound, $inbound_can_tally) {
            $style = ProductStyle::where('id', $product_style_id)->select('id');
            if ($event == 'order') {
                $style->selectRaw('in_stock + overbought as in_stock');
            } else {
                $style->addSelect('in_stock');
            }
            $style = $style->get()->first();

            //若為理貨倉 才需做判斷是否通路庫存in_stock是否足夠
            if (true == $inbound_can_tally && $style['in_stock'] + $qty < 0 && false == $is_pcs_inbound) {
                //回傳具體商品款式名稱
                $style = DB::table(app(ProductStyle::class)->getTable() . ' as style')
                    ->leftJoin(app(Product::class)->getTable(). ' as product', 'product.id', '=', 'style.product_id')
                    ->where('style.id', '=', $product_style_id)
                    ->select('product.title as product_title', 'style.title as spec')
                    ->get()->first();
                return ['success' => 0, 'error_msg' => '數量超出範圍 ' . $style->product_title. '-'. $style->spec, 'event' => 'stock', 'event_id' => $product_style_id];
            }

            $product_style = ProductStyle::where('id', $product_style_id);
            $product_style_get = $product_style->get()->first();

            if (null != $product_style_get) {
                //判斷若為採購入庫單，則總進貨量total_inbound需加上數量
                if (false == $is_pcs_inbound) {
                    $product_style->update(['in_stock' => DB::raw("in_stock + $qty")]);
                } else {
                    $updateArr = [];
                    $updateArr['total_inbound'] = DB::raw("total_inbound + $qty");
                    //入庫時判斷倉庫需理貨 則再加到in_stock
                    if ($inbound_can_tally) {
                        $updateArr['in_stock'] = DB::raw("in_stock + $qty");
                    }
                    $product_style->update($updateArr);
                }
            }

            self::create(['product_style_id' => $product_style_id,
                'qty' => $qty,
                'event' => $event,
                'event_id' => $event_id,
                'note' => $note,
                'create_user_id' => Auth::user()->id ?? null,
                'create_user_name' => Auth::user()->name ?? null]);

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
                print_r($_qty);
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

    //查找明細 依照款式ID
    public static function getDataWithStyleID($style_id) {
        $stock_event = '';
        foreach (StockEvent::asArray() as $key => $val) {
            $stock_event = $stock_event. ' when stock_log.event = "'. $val. '" then "'. StockEvent::getDescription($val). '"';
        }

        $stock_log = DB::table(app(ProductStock::class)->getTable(). ' as stock_log')
            ->select('stock_log.*'
                , DB::raw('(case '. $stock_event. ' else stock_log.event end) as event_str'))
            ->where('stock_log.product_style_id', '=', $style_id)
            ->orderByDesc('stock_log.id')
        ;
        return $stock_log;
    }
}
