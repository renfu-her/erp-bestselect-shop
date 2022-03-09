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

        $logistic_status = LogisticStatus::where('title', '新增');
        $logistic_status_get = $logistic_status->get()->first();
        if (null == $logistic_status_get) {
            return ['success' => 0, 'error_msg' => '無此物流狀態'];
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
            $reDlvUpd = Delivery::updateLogisticStatus($event, $event_id, $logistic_status_get->id);
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
    public static function updateLogisticStatus($event, $event_id, $logistic_status_id)
    {
        $logistic_status = LogisticStatus::where('id', $logistic_status_id);
        $logistic_status_get = $logistic_status->get()->first();
        if (null == $logistic_status_get) {
            return ['success' => 0, 'error_msg' => '無此物流狀態'];
        }

        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null != $dataGet) {
            return DB::transaction(function () use ($data, $dataGet, $logistic_status_get
            ) {
                $data->update([
                    'logistic_status' => $logistic_status_get->title,
                    'logistic_status_id' => $logistic_status_get->id,
                ]);
                LogisticFlow::changeDeliveryStatus($dataGet->id, $logistic_status_get);
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
        if (null != $event_id) {
            if (Event::order()->value == $event) {
                Delivery::where('event_id', $event_id)->delete();
            }
        }
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
            ->select('dlv_receive_depot.delivery_id'
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
                $join->on('query_receive_depot.delivery_id', '=', 'delivery.id');
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
                , 'delivery.logistic_status_id'
                , 'delivery.logistic_status'
                , 'delivery.memo'
                , 'delivery.close_date'
                , 'delivery.created_at'
                , 'delivery.updated_at'
                , 'delivery.deleted_at'
                , 'shi_method.method'
                , 'query_order.*'
                , 'query_receive_depot.*'
            );
        if (isset($param['event_sn'])) {
            $query->where('delivery.event_sn', '=', $param['event_sn']);
        }
        if (isset($param['delivery_sn'])) {
            $query->where('delivery.sn', '=', $param['delivery_sn']);
        }
        if (isset($param['receive_depot_id']) && 0 < count($param['receive_depot_id'])) {
            $query->whereIn('query_receive_depot.depot_id', $param['receive_depot_id']);
        }
        if (isset($param['ship_method']) && 0 < count($param['ship_method'])) {
            $query->whereIn('shi_method.method', $param['ship_method']);
        }
        if (isset($param['logistic_status_id']) && 0 < count($param['logistic_status_id'])) {
            $query->whereIn('delivery.logistic_status_id', $param['logistic_status_id']);
        }
        if (isset($param['order_sdate']) && isset($param['order_edate'])) {
            $query->whereBetween('query_order.order_created_at', [date((string) $param['order_sdate']), date((string) $param['order_edate'])]);
        }
        if (isset($param['delivery_sdate']) && isset($param['delivery_edate'])) {
            $query->whereBetween('delivery.created_at', [date((string) $param['delivery_sdate']), date((string) $param['delivery_edate'])]);
        }
        $query->orderBy('delivery.created_at');
        return $query;
    }
}
