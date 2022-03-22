<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dlv_delivery';
    protected $guarded = [];

    public static function getData($event, $event_id) {
        $data = null;
        if (null != $event_id) {
            if (Event::order()->value == $event) {
                $data = Delivery::where('event', $event)->where('event_id', $event_id);
            }
        }
        return $data;
    }

    //新增資料
    //創建時，將上層資料複製進來
    public static function createData($event, $event_id, $event_sn, $temp_id, $temp_name, $ship_category, $ship_category_name, $ship_group_id, $memo = null)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }

        $result = null;
        if (null == $dataGet) {

            $sn = date("ymd") . str_pad((Delivery::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $result = Delivery::create([
                'sn' => $sn,
                'event' => $event,
                'event_id' => $event_id,
                'event_sn' => $event_sn,
                'temp_id' => $temp_id,
                'temp_name' => $temp_name,
                'ship_category' => $ship_category,
                'ship_category_name' => $ship_category_name,
                'ship_group_id' => $ship_group_id,
                'memo' => $memo,
            ])->id;
            $reDlvUpd = Delivery::updateLogisticStatus(null, $event, $event_id, \App\Enums\Delivery\LogisticStatus::A1000());
            if ($reDlvUpd['success'] == 0) {
                DB::rollBack();
                return $reDlvUpd;
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        } else {
            $result = $dataGet->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        }
    }

    //更新物流狀態
    public static function updateLogisticStatus($user, $event, $event_id, \App\Enums\Delivery\LogisticStatus $logistic_status)
    {
        if (null == $logistic_status) {
            return ['success' => 0, 'error_msg' => '無此物流狀態'];
        }

        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null != $dataGet) {
            return DB::transaction(function () use ($data, $dataGet, $logistic_status, $user
            ) {
                $reLFCDS = LogisticFlow::createDeliveryStatus($user, $dataGet->id, [$logistic_status]);

                if ($reLFCDS['success'] == 0) {
                    DB::rollBack();
                }
                $data->update([
                    'logistic_status' => $logistic_status->value,
                    'logistic_status_code' => $logistic_status->key,
                ]);

                return ['success' => 1, 'error_msg' => "", 'id' => $dataGet->id];
            });
        } else {
            return ['success' => 0, 'error_msg' => "更新失敗 無此物流單"];
        }
    }

    //更新出貨倉庫
    public static function updateShipDepot($event, $event_id, $ship_depot_id, $ship_depot_name)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null != $dataGet) {
            $result = DB::transaction(function () use ($data, $dataGet, $ship_depot_id, $ship_depot_name
            ) {
                $data->update([
                    'ship_depot_id' => $ship_depot_id,
                    'ship_depot_name' => $ship_depot_name,
                ]);
                return ['success' => 1, 'error_msg' => "", 'id' => $dataGet->id];
            });
        }
        return ['success' => 0, 'error_msg' => "更新失敗 無此物流單"];
    }

    public static function deleteByEventId($event, $event_id)
    {
        return DB::transaction(function () use ($event, $event_id
        ) {
            $delivery = Delivery::where('event', $event)->where('event_id', $event_id)->orderByDesc('id')->withTrashed();
            $delivery_get = $delivery->get()->first();
            if (null == $delivery_get) {
                return ['success' => 0, 'error_msg' => "無此出貨單"];
            } else if ($delivery_get->audit_date != null) {
                //若已送出審核 則代表已扣除相應入庫單數量 則不給刪除
                return ['success' => 0, 'error_msg' => "已送出審核，無法刪除"];
            } else {
                $delivery->delete();
                return ['success' => 1, 'error_msg' => ""];
            }
        });
    }

    public static function getList($param) {
        $query_order = DB::table('ord_orders as order')
            ->leftJoin('ord_sub_orders', 'ord_sub_orders.order_id', '=', 'order.id')
            ->select('order.id as order_id'
                , 'order.created_at as order_created_at'
                , 'ord_sub_orders.id as sub_order_id'
            );
        Order::orderAddress($query_order, 'order', 'order_id');

        $query_receive_depot = DB::table('dlv_receive_depot')
            ->select('dlv_receive_depot.delivery_id as dlv_id'
                , 'dlv_receive_depot.depot_id'
                , 'dlv_receive_depot.depot_name'
            )
            ->groupBy('dlv_receive_depot.delivery_id')
            ->groupBy('dlv_receive_depot.depot_id')
            ->groupBy('dlv_receive_depot.depot_name');

        $query = DB::table('dlv_delivery as delivery')
            ->leftJoin('shi_group', function($join) {
                $join->on('shi_group.id', '=', 'delivery.ship_group_id');
                $join->where('delivery.ship_category', '=', 'deliver');
            })
            ->leftJoin('shi_method', function($join) {
                $join->on('shi_method.id', '=', 'shi_group.method_fk');
                $join->whereNotNull('shi_group.method_fk');
            })
            ->leftJoinSub($query_order, 'query_order', function($join) {
                $join->on('query_order.sub_order_id', '=', 'delivery.event_id')
                    ->where('delivery.event', '=', 'order');
            })
            ->leftJoinSub($query_receive_depot, 'query_receive_depot', function($join) {
                $join->on('query_receive_depot.dlv_id', '=', 'delivery.id');
            })
            ->select('delivery.id as delivery_id'
                , 'delivery.sn as delivery_sn'
                , 'delivery.event'
                , 'delivery.event_id'
                , 'delivery.event_sn'
                , 'delivery.temp_id'
                , 'delivery.temp_name'
                , 'delivery.ship_category'
                , 'delivery.ship_category_name'
                , 'delivery.ship_depot_id'
                , 'delivery.ship_depot_name'
                , 'delivery.ship_group_id'
                , 'delivery.logistic_status_code'
                , 'delivery.logistic_status'
                , 'delivery.memo'
                , 'delivery.audit_date'
                , 'delivery.created_at'
                , 'delivery.updated_at'
                , 'delivery.deleted_at'
                , 'shi_method.method'
                , 'query_order.*'
                , 'query_receive_depot.*'
            );
        if (isset($param['delivery_sn'])) {
            $query->where('delivery.sn', '=', $param['delivery_sn']);
        }
        if (isset($param['event_sn'])) {
            $query->where('delivery.event_sn', '=', $param['event_sn']);
        }
        if (isset($param['receive_depot_id']) && 0 < count($param['receive_depot_id'])) {
            $query->whereIn('query_receive_depot.depot_id', $param['receive_depot_id']);
        }
        if (isset($param['ship_method']) && 0 < count($param['ship_method'])) {
            $query->whereIn('shi_method.method', $param['ship_method']);
        }
        if (isset($param['logistic_status_code']) && 0 < count($param['logistic_status_code'])) {
            $query->whereIn('delivery.logistic_status_code', $param['logistic_status_code']);
        }
        if (isset($param['order_sdate']) && isset($param['order_edate'])) {
            $order_sdate = date('Y-m-d 00:00:00', strtotime($param['order_sdate']));
            $order_edate = date('Y-m-d 23:59:59', strtotime($param['order_edate']));
            $query->whereBetween('query_order.order_created_at', [$order_sdate, $order_edate]);
        }
        if (isset($param['delivery_sdate']) && isset($param['delivery_edate'])) {
            $delivery_sdate = date('Y-m-d 00:00:00', strtotime($param['delivery_sdate']));
            $delivery_edate = date('Y-m-d 23:59:59', strtotime($param['delivery_edate']));
            $query->whereBetween('delivery.created_at', [$delivery_sdate, $delivery_edate]);
        }
        $query->orderBy('delivery.created_at');

        return $query;
    }

    //取得物流頁顯示的 子訂單-出貨商品列表
    public static function getListToLogistic($order_id = null, $sub_order_id = null)
    {
        $sub_rec_depot = DB::table('dlv_receive_depot')
            ->select('dlv_receive_depot.delivery_id'
                , 'dlv_receive_depot.event_item_id'
                , 'dlv_receive_depot.freebies'
                , 'dlv_receive_depot.product_style_id'
                , 'dlv_receive_depot.sku as rec_sku'
                , 'dlv_receive_depot.product_title as rec_product_title'
            )
            ->selectRaw('sum(dlv_receive_depot.qty) as send_qty')
            ->groupBy('dlv_receive_depot.delivery_id')
            ->groupBy('dlv_receive_depot.event_item_id')
            ->groupBy('dlv_receive_depot.freebies')
            ->groupBy('dlv_receive_depot.product_style_id')
            ->groupBy('dlv_receive_depot.sku')
            ->groupBy('dlv_receive_depot.product_title');

        $sub_orders = DB::table('ord_sub_orders')
            ->leftJoin('ord_items', function($join) {
                $join->on('ord_items.sub_order_id', '=', 'ord_sub_orders.id');
            })
            ->select('ord_items.id as item_id'
                , 'ord_items.order_id'
                , 'ord_items.sub_order_id'
                , 'ord_items.product_style_id'
                , 'ord_items.sku'
                , 'ord_items.product_title'
                , 'ord_items.price'
                , 'ord_items.qty'
                , 'ord_items.type'
                , 'ord_items.total_price'
            );

        $query = DB::table(DB::raw("({$sub_rec_depot->toSql()}) as rec_depot"))
            ->leftJoinSub($sub_orders, 'orders', function($join) {
                $join->on('orders.sub_order_id', '=', 'rec_depot.event_item_id');
            })
            ->whereNotNull('rec_depot.delivery_id')
            ->whereNotNull('orders.item_id')
            ->select('*');

        if (isset($order_id)) {
            $query->whereIn('ord_items.order_id', $order_id);
        }
        if (isset($sub_order_id)) {
            $query->whereIn('ord_items.sub_order_id', $sub_order_id);
        }

        return $query;
    }

    //取得出貨單 預設基本設定的物流成本
    public static function getListWithCost($delivery_id = null, $event = null, $event_id = null) {
        $sub_shi = ShipmentGroup::getDataWithCost();
        $query = DB::table('dlv_delivery as delivery')
            ->leftJoinSub($sub_shi, 'shi_tb', function($join) {
                $join->on('shi_tb.group_id_fk', '=', 'delivery.ship_group_id');
                $join->where('delivery.ship_category', '=', 'deliver');
            })
            ->whereNotNull('shi_tb.group_id_fk');;

        if (isset($delivery_id)) {
            $query->where('delivery.id', $delivery_id);
        }
        if (isset($event)) {
            $query->where('delivery.event', $event);
        }
        if (isset($event_id)) {
            $query->where('delivery.event_id', $event_id);
        }
        return $query;
    }

    public static function getDeliveryWithEventWithSn($event, $event_id) {
        $query = DB::table('dlv_delivery as delivery');
        if (isset($event)) {
            $query->where('delivery.event', $event);
        }
        if (isset($event_id)) {
            $query->where('delivery.event_id', $event_id);
        }
        return $query;
    }

}
