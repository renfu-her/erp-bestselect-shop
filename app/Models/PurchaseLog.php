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

    public static function stockChange($event_parent_id, $product_style_id, $event, $event_id, $feature, $inbound_id = null, $qty, $note = null
        , $product_title = null, $prd_type = null
        , $operator_user_id, $operator_user_name)
    {
        if (!Event::hasKey($event)) {
            return ['success' => 0, 'error_msg' => 'event error '.$event];
        }

        if (!LogEventFeature::hasKey($feature)) {
            return ['success' => 0, 'error_msg' => 'feature error '. $feature];
        }

        return DB::transaction(function () use ($event_parent_id, $product_style_id, $event, $event_id, $feature, $inbound_id, $qty, $note, $product_title, $prd_type, $operator_user_id, $operator_user_name) {
            self::create([
                'event_parent_id' => $event_parent_id,
                'product_style_id' => $product_style_id,
                'event' => $event,
                'event_id' => $event_id,
                'feature' => $feature,
                'inbound_id' => $inbound_id,
                'product_title' => $product_title,
                'prd_type' => $prd_type,
                'qty' => $qty,
                'note' => $note,
                'user_id' => $operator_user_id,
                'user_name' => $operator_user_name]);

            return ['success' => 1];

        });
    }

    //變更紀錄
    public static function getData($event, $event_id) {
        $eventTable = '';
        $eventItemTable = '';
        if (Event::purchase()->value == $event) {
            $eventTable = 'pcs_purchase';
            $eventItemTable = 'pcs_purchase_items';
        } else if (Event::consignment()->value == $event) {
            $eventTable = 'csn_consignment';
            $eventItemTable = 'csn_consignment_items';
        } else if (Event::csn_order()->value == $event) {
            $eventTable = 'csn_orders';
            $eventItemTable = 'csn_order_items';
        }

        $log_purchase = DB::table('pcs_purchase_log as log')
            ->leftJoin($eventTable.' as purchase', function($join) use($event) {
                $join->on('purchase.id', '=', 'log.event_parent_id');
                $join->where('log.event', $event);
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
            ->whereNull('log.product_style_id')
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
                , 'log.product_title as title'
            )
            ->whereNotNull('items.id') //選擇商品狀態時 可能隨時會被使用者刪除 所以要加此防呆
            ->where('log.event_parent_id', '=', $event_id)
            ->where('log.event', '=', $event);

        $logEventFeatureKey_inbound = [];
        foreach (LogEventFeature::asArray() as $key => $value) {
            if (0 === strpos($key, 'inbound')) {
                array_push($logEventFeatureKey_inbound, $key);
            }
        }
        $log_inbound = DB::table('pcs_purchase_log as log')
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
                , 'log.product_title as title'
            )
            ->where('log.event_parent_id', '=', $event_id)
            ->where('log.event', '=', $event);

        $logEventFeatureKey_delivery = [];
        array_push($logEventFeatureKey_delivery, LogEventFeature::delivery()->value);
        array_push($logEventFeatureKey_delivery, LogEventFeature::combo()->value);
        $log_delivery = DB::table('pcs_purchase_log as log')
            ->leftJoin('dlv_receive_depot as rcv_depot', function($join) use($event, $logEventFeatureKey_delivery) {
                $join->on('rcv_depot.id', '=', 'log.event_id');
                $join->where('log.event', $event);
                $join->whereIn('log.feature', $logEventFeatureKey_delivery);
            })
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
                , DB::raw('(case when "ce" = rcv_depot.prd_type then CONCAT(log.product_title, "(組合包內容)")
                    else log.product_title end) as title')
            )
            ->whereNull('rcv_depot.deleted_at')
            ->where('log.event_parent_id', '=', $event_id)
            ->where('log.event', '=', $event)
            ->whereIn('log.feature', $logEventFeatureKey_delivery);

        $logEventFeatureKey_consume = [];
        array_push($logEventFeatureKey_consume, LogEventFeature::consume_delivery()->value);
        $log_consume = DB::table('pcs_purchase_log as log')
            ->select('log.id'
                , 'log.event'
                , 'log.feature'
                , 'log.user_name'
                , 'log.created_at'
                , 'log.qty'
                , 'log.product_title as title'
            )
            ->where('log.event_parent_id', '=', $event_id)
            ->where('log.event', '=', $event)
            ->whereIn('log.feature', $logEventFeatureKey_consume);

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
        if (null != $log_delivery) {
            $log_purchase->union($log_delivery);
        }
        if (null != $log_consume) {
            $log_purchase->union($log_consume);
        }

        if (null != $log_pay_order) {
            $log_purchase->union($log_pay_order);
        }

        $log_purchase->orderByDesc('id');

        return $log_purchase;
    }

    //庫存明細
    public static function getStockData($event, $depot_id, $style_id, $logFeature = null) {
        $logEventFeatureKey_delivery = [];
        array_push($logEventFeatureKey_delivery, LogEventFeature::delivery()->value);
//        array_push($logEventFeatureKey_delivery, LogEventFeature::combo()->value);

        $logPurchase = DB::table('pcs_purchase_log as log')
            ->leftJoin('dlv_receive_depot as rcv_depot', function($join) use($event, $logEventFeatureKey_delivery) {
                $join->on('rcv_depot.id', '=', 'log.event_id');
                $join->where('log.event', $event);
                $join->whereIn('log.feature', $logEventFeatureKey_delivery); //出貨單商品 將其對應回去
            })
            ->leftJoin('pcs_purchase_inbound as inbound', function($join) use($event, $logEventFeatureKey_delivery) {
                $join->on('inbound.id', '=', 'log.inbound_id');
            })
            ->whereNull('rcv_depot.deleted_at')
            ->whereNull('inbound.deleted_at')
            ->whereIn('log.event', $event)
            ->whereNotNull('log.product_style_id')
            ->whereNotNull('log.inbound_id')
            ->select(
                'log.id as id'
                , 'log.event_parent_id as event_parent_id'
                , 'log.product_style_id as product_style_id'
                , DB::raw('(case
                    when "'. Event::purchase()->value. '" = log.event then "'. Event::getDescription(Event::purchase). '"
                    when "'. Event::order()->value. '" = log.event then "'. Event::getDescription(Event::order). '"
                    when "'. Event::ord_pickup()->value. '" = log.event then "'. Event::getDescription(Event::ord_pickup). '"
                    when "'. Event::consignment()->value. '" = log.event then "'. Event::getDescription(Event::consignment). '"
                    when "'. Event::csn_order()->value. '" = log.event then "'. Event::getDescription(Event::csn_order). '"
                    else log.event end) as event')
                , 'log.event_id as event_id'
//                , 'log.feature as feature'
                , DB::raw('(case
                    when "'. LogEventFeature::inbound_add()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::inbound_add). '"
                    when "'. LogEventFeature::inbound_del()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::inbound_del). '"
                    when "'. LogEventFeature::inbound_update()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::inbound_update). '"
                    when "'. LogEventFeature::delivery()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::delivery). '"
                    when "'. LogEventFeature::consume_delivery()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::consume_delivery). '"
                    when "'. LogEventFeature::send_back()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::send_back). '"
                    when "'. LogEventFeature::consume_send_back()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::consume_send_back). '"
                    when "'. LogEventFeature::decompose()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::decompose). '"
                    when "'. LogEventFeature::scrapped()->value. '" = log.feature then "'. LogEventFeature::getDescription(LogEventFeature::scrapped). '"
                    else log.feature end) as feature')
                , 'log.inbound_id as inbound_id'
                , DB::raw('(case when "ce" = rcv_depot.prd_type then CONCAT(log.product_title, "(組合包內容)")
                    else log.product_title end) as title')
                , 'log.prd_type as prd_type'
                , 'log.qty as qty'
                , 'log.user_id as user_id'
                , 'log.user_name as user_name'
                , 'log.note as note'
                , 'log.created_at as created_at'
                , 'inbound.sn as inbound_sn'
                , 'inbound.sku as sku'
                , 'inbound.depot_id as depot_id'
                , 'inbound.depot_name as depot_name'
            );
        if (isset($depot_id)) {
            $logPurchase->where('inbound.depot_id', '=', $depot_id);
        }
        if (isset($style_id)) {
            $logPurchase->where('log.product_style_id', '=', $style_id);
        }
        if (isset($logFeature)) {
            $logPurchase->whereIn('log.feature', $logFeature);
        }

        return $logPurchase;
    }

    //庫存明細
    public static function getStockDataAndEventSn($event_table, $event, $depot_id, $style_id, $logFeature = null) {
        $logPurchase = self::getStockData($event, $depot_id, $style_id, $logFeature)
            ->leftJoin($event_table. ' as event', function ($join) use($event) {
                $join->on('event.id', '=', 'log.event_parent_id')
                    ->whereIn('log.event', $event);
            })
            ->addSelect(['event.sn as event_sn'
            ]);

        return $logPurchase;
    }

    public static function getStockDataForImportInbound($depot_id, $style_id, $logFeature = null) {
        $log_purchase = PurchaseLog::getStockDataAndEventSn(app(Purchase::class)->getTable(), [Event::purchase()->value], $depot_id, $style_id, $logFeature);
        $log_order = PurchaseLog::getStockDataAndEventSn(app(SubOrders::class)->getTable(), [Event::order()->value, Event::ord_pickup()->value], $depot_id, $style_id, $logFeature);
        $log_consignment = PurchaseLog::getStockDataAndEventSn(app(Consignment::class)->getTable(), [Event::consignment()->value], $depot_id, $style_id, $logFeature);
        $log_csn_order = PurchaseLog::getStockDataAndEventSn(app(CsnOrder::class)->getTable(), [Event::csn_order()->value], $depot_id, $style_id, $logFeature);

        $log_purchase->union($log_order);
        $log_purchase->union($log_consignment);
        $log_purchase->union($log_csn_order);

        return $log_purchase;
    }



    //找到LOG最後退貨入庫寫入的資料
    public static function getSendBackData($delivery_id, $event_id) {
        $log = DB::table(app(PurchaseLog::class)->getTable(). ' as pcs_log')
            ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', function ($join) use($delivery_id) {
                $join->on('rcv_depot.delivery_id', '=', DB::raw($delivery_id));
            })
            ->select(
                DB::raw('max(pcs_log.id) as log_id')
            )
            ->groupBy('pcs_log.event_parent_id')
            ->groupBy('pcs_log.product_style_id')
            ->groupBy('pcs_log.event')
            ->groupBy('pcs_log.event_id')
            ->where('pcs_log.event_parent_id', '=', $event_id)
            ->where('pcs_log.feature', '=', DB::raw('"'. LogEventFeature::send_back()->value.'"'))
            ->whereNull('rcv_depot.deleted_at')
            ->get()->toArray();

        $log_ids = [];
        if (isset($log) && 0 < count($log)) {
            foreach ($log as $log_item) {
                $log_ids[] = $log_item->log_id;
            }
        }

        $log_detail = DB::table(app(PurchaseLog::class)->getTable(). ' as pcs_log')
            ->whereIn('pcs_log.id', $log_ids)
            ->get();
        return $log_detail;
    }
}
