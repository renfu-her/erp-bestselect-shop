<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEvent;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseLog extends Model
{
    use HasFactory;
    protected $table = 'pcs_purchase_log';
    protected $guarded = [];

    public static function stockChange($event_parent_id, $product_style_id, $event, $event_id, $feature, $qty, $note = null, $operator_user_id, $operator_user_name)
    {
        if (!LogEvent::hasKey($event)) {
            return ['success' => 0, 'error_msg' => 'feature error'];
        }

        if (!LogEventFeature::hasKey($feature)) {
            return ['success' => 0, 'error_msg' => 'event error'];
        }

        return DB::transaction(function () use ($event_parent_id, $product_style_id, $event, $event_id, $feature, $qty, $note, $operator_user_id, $operator_user_name) {
            self::create([
                'event_parent_id' => $event_parent_id,
                'product_style_id' => $product_style_id,
                'event' => $event,
                'event_id' => $event_id,
                'feature' => $feature,
                'qty' => $qty,
                'note' => $note,
                'user_id' => $operator_user_id,
                'user_name' => $operator_user_name]);

            return ['success' => 1];

        });
    }

    public static function getData($event, $event_id) {
        $logEventFeatureKey_purchase = [];
        foreach (LogEventFeature::asArray() as $key => $value) {
            if (Event::purchase()->value == $event) {
                if (0 === strpos($key, 'pcs')) {
                    array_push($logEventFeatureKey_purchase, $key);
                }
            } else if (Event::consignment()->value == $event) {
                if (0 === strpos($key, 'csn')) {
                    array_push($logEventFeatureKey_purchase, $key);
                }
            }
        }
        $eventTable = '';
        $eventItemTable = '';
        if (Event::purchase()->value == $event) {
            $eventTable = 'pcs_purchase';
            $eventItemTable = 'pcs_purchase_items';
        } else if (Event::consignment()->value == $event) {
            $eventTable = 'csn_consignment';
            $eventItemTable = 'csn_consignment_items';
        }


        $log_purchase = DB::table('pcs_purchase_log as log')
            ->leftJoin($eventTable.' as purchase', function($join) use($event, $logEventFeatureKey_purchase) {
                $join->on('purchase.id', '=', 'log.event_id');
                $join->where('log.event', $event);
                $join->whereIn('log.feature', $logEventFeatureKey_purchase);
            })
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
            )
            ->selectRaw('CONCAT(log.note) as title')
            ->whereNotNull('purchase.id')
            ->where('log.event_parent_id', '=', $event_id)
            ->where('log.event', '=', $event);

        $logEventFeatureKey_style = [];
        foreach (LogEventFeature::asArray() as $key => $value) {
            if (0 === strpos($key, 'style')) {
                array_push($logEventFeatureKey_style, $key);
            }
        }
        $log_style = DB::table('pcs_purchase_log as log')
            ->leftJoin($eventItemTable.' as items', function($join) use($event, $logEventFeatureKey_style) {
                $join->on('items.id', '=', 'log.event_id');
                $join->where('log.event', $event);
                $join->whereIn('log.feature', $logEventFeatureKey_style);
            })
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
                , 'items.title'
            )
            ->whereNotNull('items.id')
            ->where('log.event_parent_id', '=', $event_id)
            ->where('log.event', '=', $event);

        $logEventFeatureKey_inbound = [];
        foreach (LogEventFeature::asArray() as $key => $value) {
            if (0 === strpos($key, 'inbound')) {
                array_push($logEventFeatureKey_inbound, $key);
            }
        }
        $log_inbound = DB::table('pcs_purchase_log as log')
            ->leftJoin('pcs_purchase_inbound as inbound', function($join) use($event, $logEventFeatureKey_inbound) {
                $join->on('inbound.id', '=', 'log.event_id');
                $join->where('log.event', $event);
                $join->whereIn('log.feature', $logEventFeatureKey_inbound);
            })
            ->leftJoin('pcs_purchase_items as items', 'items.id', '=', 'inbound.event_item_id')
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
                , 'items.title as title'
            )
            ->whereNotNull('inbound.id')
            ->where('log.event_parent_id', '=', $event_id)
            ->where('log.event', '=', $event);

//        dd($log_inbound->get());
        $logEventFeatureKey_pay = [];
        foreach (LogEventFeature::asArray() as $key => $value) {
            if (0 === strpos($key, 'pay')) {
                array_push($logEventFeatureKey_pay, $key);
            }
        }
        $log_pay_order = null;
        if (Event::purchase()->value == $event) {
            $log_pay_order = DB::table('pcs_purchase_log as log')
                ->leftJoin('pcs_paying_orders as orders', function($join) use($logEventFeatureKey_pay) {
                    $join->on('orders.id', '=', 'log.event_id');
                    $join->whereIn('log.event', [LogEvent::pcs_pay()->key]);
                    $join->whereIn('log.feature', $logEventFeatureKey_pay);
                })
                ->select('log.id'
                    , 'log.event'
                    , 'log.feature'
                    , 'log.user_name'
                    , 'log.created_at'
                    , 'log.qty'
                )
                ->selectRaw('CONCAT("") as title')
                ->where('log.event_parent_id', '=', $event_id)
                ->where('log.event', '=', LogEvent::pcs_pay()->key);
        }

        $log_purchase->union($log_style);
        $log_purchase->union($log_inbound);
        if (null != $log_pay_order) {
            $log_purchase->union($log_pay_order);
        }

        $log_purchase->orderByDesc('id');

        return $log_purchase;
    }

}
