<?php

namespace App\Models;

use App\Enums\Purchase\LogFeature;
use App\Enums\Purchase\LogFeatureEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseLog extends Model
{
    use HasFactory;
    protected $table = 'pcs_purchase_log';
    protected $guarded = [];

    public static function stockChange($purchase_id, $product_style_id, $feature, $feature_id, $event, $qty, $note = null, $operator_user_id, $operator_user_name)
    {
        if (!LogFeature::hasKey($feature)) {
            return ['success' => 0, 'error_msg' => 'feature error'];
        }

        if (!LogFeatureEvent::hasKey($event)) {
            return ['success' => 0, 'error_msg' => 'event error'];
        }

        return DB::transaction(function () use ($purchase_id, $product_style_id, $feature, $feature_id, $event, $qty, $note, $operator_user_id, $operator_user_name) {
            self::create([
                'purchase_id' => $purchase_id,
                'product_style_id' => $product_style_id,
                'feature' => $feature,
                'feature_id' => $feature_id,
                'event' => $event,
                'qty' => $qty,
                'note' => $note,
                'user_id' => $operator_user_id,
                'user_name' => $operator_user_name]);

            return ['success' => 1];

        });
    }

    public static function getData($purchase_id) {
        $log_purchase = DB::table('pcs_purchase_log as log')
            ->leftJoin('pcs_purchase as purchase', function($join) {
                $join->on('purchase.id', '=', 'log.feature_id');
            })
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
            )
            ->selectRaw('CONCAT("") as title')
            ->where('log.purchase_id', '=', $purchase_id)
            ->where('log.feature', '=', 'purchase');
        $log_style = DB::table('pcs_purchase_log as log')
            ->leftJoin('pcs_purchase_items as items', function($join) {
                $join->on('items.id', '=', 'log.feature_id');
            })
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
                , 'items.title'
            )
            ->where('log.purchase_id', '=', $purchase_id)
            ->where('log.feature', '=', 'style');
        $log_inbound = DB::table('pcs_purchase_log as log')
            ->leftJoin('pcs_purchase_inbound as inbound', function($join) {
                $join->on('inbound.id', '=', 'log.feature_id');
            })
            ->leftJoin('pcs_purchase_items as items', 'items.id', '=', 'inbound.purchase_item_id')
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
                , 'items.title as title'
            )
            ->where('log.purchase_id', '=', $purchase_id)
            ->where('log.feature', '=', 'inbound');
        $log_pay_order = DB::table('pcs_purchase_log as log')
            ->leftJoin('pcs_paying_orders as orders', function($join) {
                $join->on('orders.id', '=', 'log.feature_id');
            })
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
            )
            ->selectRaw('CONCAT("") as title')
            ->where('log.purchase_id', '=', $purchase_id)
            ->where('log.feature', '=', 'pay');

        $log_purchase->union($log_style);
        $log_purchase->union($log_inbound);
        $log_purchase->union($log_pay_order);

        return $log_purchase;
    }

}
