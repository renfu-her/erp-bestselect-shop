<?php

namespace App\Models;

use App\Enums\Delivery\BackStatus;
use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dlv_delivery';
    protected $guarded = [];

    public static function getData($event, $event_id)
    {
        $data = null;
        if (null != $event && null != $event_id) {
            $data = Delivery::where('event', $event)->where('event_id', $event_id);
        }
        return $data;
    }

    //新增資料
    //創建時，將上層資料複製進來
    public static function createData($user, $event, $event_id, $event_sn, $temp_id = null, $temp_name = null, $ship_category = null, $ship_category_name = null, $ship_group_id = null, $memo = null)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }

        $result = null;
        if (null == $dataGet) {
            $sn = Sn::createSn('delivery', 'DL', 'ymd', 5);

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
            $reLFCDS = LogisticFlow::createDeliveryStatus($user, $result, [LogisticStatus::A1000()]);
            if ($reLFCDS['success'] == 0) {
                DB::rollBack();
                return $reLFCDS;
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        } else {
            $result = $dataGet->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        }
    }

    //更新宅配、自取資訊
    public static function updateShipCategory($event, $event_id, $temp_id, $temp_name, $ship_category, $ship_category_name)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null != $dataGet) {
            $result = IttmsDBB::transaction(function () use ($data, $dataGet, $temp_id, $temp_name, $ship_category, $ship_category_name
            ) {
                $data->update([
                    'temp_id' => $temp_id,
                    'temp_name' => $temp_name,
                    'ship_category' => $ship_category,
                    'ship_category_name' => $ship_category_name,
                ]);
                return ['success' => 1, 'error_msg' => "", 'id' => $dataGet->id];
            });
        }
        if ($result['success'] == 1) {
            return $result;
        } else {
            return ['success' => 0, 'error_msg' => "更新失敗 無此出貨單"];
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
            $result = IttmsDBB::transaction(function () use ($data, $dataGet, $ship_depot_id, $ship_depot_name
            ) {
                $data->update([
                    'ship_depot_id' => $ship_depot_id,
                    'ship_depot_name' => $ship_depot_name,
                ]);
                return ['success' => 1, 'error_msg' => "", 'id' => $dataGet->id];
            });
        }
        if ($result['success'] == 1) {
            return $result;
        } else {
            return ['success' => 0, 'error_msg' => "更新失敗 無此出貨單"];
        }
    }

    //刪除出貨單和出貨商品
    public static function deleteByEventId($event, $event_id)
    {
        return IttmsDBB::transaction(function () use ($event, $event_id
        ) {
            $delivery = Delivery::where('event', $event)->where('event_id', $event_id)->orderByDesc('id');
            $delivery_get = $delivery->get()->first();
            if (null == $delivery_get) {
                return ['success' => 0, 'error_msg' => "無此出貨單"];
            } else if ($delivery_get->audit_date != null) {
                //若已送出審核 則代表已扣除相應入庫單數量 則不給刪除
                return ['success' => 0, 'error_msg' => "已送出審核，無法刪除"];
            } else {
                if (null == $delivery_get->deleted_at)
                {
                    ReceiveDepot::where('delivery_id', '=', $delivery_get->id)->delete();
                    $delivery->delete();
                }
                return ['success' => 1, 'error_msg' => ""];
            }
        });
    }

    public static function getList($param)
    {
        // 溫層
        $depotTemps = DB::table('depot_temp as dt')
            ->leftJoin('shi_temps as temp', 'dt.temp_id', '=', 'temp.id')
            ->select('dt.depot_id')
            ->selectRaw("GROUP_CONCAT(dt.temp_id) as temp_id")
            ->selectRaw("GROUP_CONCAT(temp.temps) as temp")
            ->groupBy('dt.depot_id');

        //判斷訂單自取溫層
        $query_order_pickup = DB::table('ord_orders as order')
            ->leftJoin('ord_sub_orders', 'ord_sub_orders.order_id', '=', 'order.id')
            ->leftJoinSub($depotTemps, 'temp', 'temp.depot_id', '=', 'ord_sub_orders.ship_event_id')
            ->select('order.id as order_id'
                , 'order.id as id' // 給func orderAddress使用
                , 'order.status as order_status'
                , 'order.created_at as order_created_at'
                , 'ord_sub_orders.id as sub_order_id'
                , 'ord_sub_orders.ship_category as ship_category'
                , 'ord_sub_orders.ship_category_name as ship_category_name'
                , 'ord_sub_orders.total_price'
                , 'ord_sub_orders.ship_event_id as depot_id'
                , 'temp.temp_id as depot_temp_id'
                , 'temp.temp as depot_temp_name'
                , DB::raw('@ship_temp:=null as ship_temp_id')
                , DB::raw('@ship_temp:=null as ship_temp_name')
            )
            ->where('ord_sub_orders.ship_category', '=', 'pickup');

        //判斷訂單溫層 配合訂單自取欄位
        $query_order_delivery = DB::table('ord_orders as order')
            ->leftJoin('ord_sub_orders', 'ord_sub_orders.order_id', '=', 'order.id')
            ->select('order.id as order_id'
                , 'order.id as id' // 給func orderAddress使用
                , 'order.status as order_status'
                , 'order.created_at as order_created_at'
                , 'ord_sub_orders.id as sub_order_id'
                , 'ord_sub_orders.ship_category as ship_category'
                , 'ord_sub_orders.ship_category_name as ship_category_name'
                , 'ord_sub_orders.total_price'
                , DB::raw('@depot_id:=null as depot_id')
                , DB::raw('@temp_id:=null as depot_temp_id')
                , DB::raw('@temp:=null as depot_temp_name')
                , 'ord_sub_orders.ship_temp_id as ship_temp_id'
                , 'ord_sub_orders.ship_temp as ship_temp_name'
            )
            ->where('ord_sub_orders.ship_category', '=', 'deliver');

        $query_order_delivery = $query_order_delivery->union($query_order_pickup);
        $query_order = DB::query()->fromSub($query_order_delivery, 'order')
            ->select('order.*');
        Order::orderAddress($query_order, 'order', 'order_id');

        $query_receive_depot = DB::table('dlv_receive_depot')
            ->select('dlv_receive_depot.delivery_id as dlv_id'
                , 'dlv_receive_depot.depot_id'
                , 'dlv_receive_depot.depot_name'
            )
            ->whereNull('dlv_receive_depot.deleted_at')
            ->groupBy('dlv_receive_depot.delivery_id')
            ->groupBy('dlv_receive_depot.depot_id')
            ->groupBy('dlv_receive_depot.depot_name');

        $query = DB::table('dlv_delivery as delivery')
            ->whereNull('delivery.deleted_at')
            ->leftJoin('shi_group', function ($join) {
                $join->on('shi_group.id', '=', 'delivery.ship_group_id');
                $join->where('delivery.ship_category', '=', 'deliver');
            })
            ->leftJoin('shi_method', function ($join) {
                $join->on('shi_method.id', '=', 'shi_group.method_fk');
                $join->whereNotNull('shi_group.method_fk');
            })
            ->leftJoinSub($query_order, 'query_order', function ($join) {
                $join->on('query_order.sub_order_id', '=', 'delivery.event_id')
                    ->where('delivery.event', '=', 'order');
            })
            ->leftJoin('ord_items', 'query_order.sub_order_id', '=', 'ord_items.sub_order_id')

            ->leftJoin('csn_orders as csnord', function ($join) {
                $join->on('csnord.id', '=', 'delivery.event_id')
                    ->where('delivery.event', '=', Event::csn_order()->value);
            })
            ->whereNull('csnord.deleted_at')

            ->leftJoin('csn_consignment as csn', function ($join) {
                $join->on('csn.id', '=', 'delivery.event_id')
                    ->where('delivery.event', '=', Event::consignment()->value);
            })
            ->whereNull('csn.deleted_at')

            ->leftJoinSub($query_receive_depot, 'query_receive_depot', function ($join) {
                $join->on('query_receive_depot.dlv_id', '=', 'delivery.id');
                $join->where('query_receive_depot.depot_id', '<>', 0);
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
                , 'query_order.order_id'
                , 'query_order.order_status'
                , 'query_order.order_created_at'
                , 'query_order.sub_order_id'
                , 'query_order.ship_category'
                , 'query_order.ship_category_name'
                , 'query_order.depot_id'
                , 'query_order.depot_temp_id as depot_temp_id'
                , 'query_order.depot_temp_name as depot_temp_name'
                , 'query_order.ship_temp_id'
                , 'query_order.ship_temp_name'
                , 'query_order.rec_name'
                , 'query_order.rec_address'
                , 'query_order.rec_phone'
                , 'query_order.rec_zipcode'
                , 'query_order.ord_name'
                , 'query_order.ord_address'
                , 'query_order.ord_phone'
                , 'query_order.ord_zipcode'
                , 'query_order.sed_name'
                , 'query_order.sed_address'
                , 'query_order.sed_phone'
                , 'query_order.sed_zipcode'
                , 'query_order.total_price'
                , 'query_receive_depot.*'
            );
        if (isset($param['delivery_sn'])) {
            $query->where('delivery.sn', 'like', "%" . $param['delivery_sn'] . "%");
        }
        if (isset($param['event_sn'])) {
            $query->where('delivery.event_sn', 'like', "%" . $param['event_sn'] . "%");
        }
        if (isset($param['receive_depot_id']) && 0 < count($param['receive_depot_id'])) {
            $query->whereIn('query_receive_depot.depot_id', $param['receive_depot_id']);
        }
        if (isset($param['logistic_status_code']) && 0 < count($param['logistic_status_code'])) {
            $query->whereIn('delivery.logistic_status_code', $param['logistic_status_code']);
        }
        if (isset($param['ship_category']) && 0 < count($param['ship_category'])) {
            $query->whereIn('query_order.ship_category', $param['ship_category']);
        }
        //判斷若為訂單自取 則不篩選 shi_method
        $ship_method = $param['ship_method'];
        if (false == empty($param['ship_category'])) {
            if (true == in_array('pickup', $param['ship_category'])) {
                $param['ship_method'] = [];
            }
        }
        if (isset($ship_method) && 0 < count($ship_method)) {
            $query->whereIn('shi_method.method', $ship_method);
        }
        $param['ship_method'] = $ship_method;

        if (isset($param['order_status']) && 0 < count($param['order_status'])) {
            $query->whereIn('query_order.order_status', $param['order_status']);
        }
        if (isset($param['has_csn']) && "false" == $param['has_csn']) {
            $query->whereNotIn('delivery.event', [Event::consignment()->value]);
        }
        if (isset($param['has_back_sn']) && 'all' != $param['has_back_sn']) {
            $query->whereNotNull('delivery.back_sn');
        }
        if (isset($param['order_sdate']) && isset($param['order_edate'])) {
            $order_sdate = date('Y-m-d 00:00:00', strtotime($param['order_sdate']));
            $order_edate = date('Y-m-d 23:59:59', strtotime($param['order_edate']));
            $query->whereBetween('query_order.order_created_at', [$order_sdate, $order_edate]);
        }
        if (isset($param['delivery_sdate']) && isset($param['delivery_edate'])) {
            $delivery_sdate = date('Y-m-d 00:00:00', strtotime($param['delivery_sdate']));
            $delivery_edate = date('Y-m-d 23:59:59', strtotime($param['delivery_edate']));
            $query->whereBetween('delivery.audit_date', [$delivery_sdate, $delivery_edate]);
        }

        //篩選宅配溫層
        if (isset($param['ship_temp_id']) && is_array($param['ship_temp_id']) && $param['ship_temp_id']) {
            $query->whereIn('query_order.ship_temp_id', $param['ship_temp_id']);
        }
        //篩選自取倉溫層
        if (isset($param['depot_temp_id']) && is_array($param['depot_temp_id']) && $param['depot_temp_id']) {
            $query->whereRaw('FIND_IN_SET(?, query_order.depot_temp_id)', $param['depot_temp_id']);
        }

        $query->orderByDesc('delivery.id');

        return $query;
    }

    //出貨商品查詢
    public static function getListByProduct($param) {
        $rcv_depot_data = [
            'product_title' => 'rcv_depot.product_title'
            , 'product_style_id' => 'rcv_depot.product_style_id'
            , 'qty' => 'rcv_depot.qty'
            , 'prd_user_name' => 'usr.name'
            , 'ib_source_sn' => 'ib_source.sn'
            , 'rcv_unit_cost' => 'rcv_depot.unit_cost'
        ];

        $re_event_sn_pcs = DB::table(app(Purchase::class)->getTable(). ' as pcs')
            ->select(DB::raw('concat("'. Event::purchase()->value. '") as event'), 'pcs.id', 'pcs.sn');
        $re_event_sn_csn = DB::table(app(Consignment::class)->getTable(). ' as csn')
            ->select(DB::raw('concat("'. Event::consignment()->value. '") as event'), 'csn.id', 'csn.sn');
        $re_event_sn_pcs = $re_event_sn_pcs->union($re_event_sn_csn);

        //出貨單
        $query_delivery = DB::table(app(Delivery::class)->getTable(). ' as delivery')
            ->leftJoin(app(ReceiveDepot::class)->getTable(). ' as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
            ->leftJoin(app(PurchaseInbound::class)->getTable(). ' as inbound', function ($join) {
                $join->on('inbound.id', '=', 'rcv_depot.inbound_id');
            })
            ->leftJoinSub($re_event_sn_pcs, 'ib_source', function($join) {
                $join->on('ib_source.id', '=', 'inbound.event_id')
                    ->on('ib_source.event', '=', 'inbound.event');
            })
            ->leftJoin(app(ProductStyle::class)->getTable(). ' as style', 'style.id', '=', 'rcv_depot.product_style_id')
            ->leftJoin(app(Product::class)->getTable(). ' as prd', 'prd.id', '=', 'style.product_id')
            ->leftJoin(app(User::class)->getTable(). ' as usr', 'usr.id', '=', 'prd.user_id')
            ->select(
                'delivery.id as delivery_id'
                , 'delivery.event'
                , 'delivery.event_sn'
                , 'delivery.event_id'
                , 'delivery.sn'
                , 'delivery.audit_user_name'
                , 'delivery.logistic_status'
                , DB::raw('DATE_FORMAT(delivery.audit_date,"%Y-%m-%d %H:%i:%s") as audit_date')
                , DB::raw('DATE_FORMAT(delivery.created_at,"%Y-%m-%d %H:%i:%s") as created_at')
            )

            ->whereNull('delivery.deleted_at')
            ->whereNull('rcv_depot.deleted_at')
            ->groupBy('delivery.id')
            ->orderByDesc('delivery.id')
            ;

        //商品管理-搜尋廠商條件
        if (!empty($param['search_supplier'])) {
            $query_delivery->leftJoin(app(ProductSupplier::class)->getTable(). ' as prd_prd_supplier', 'prd_prd_supplier.product_id', '=', 'prd.id')
                ->join(app(Supplier::class)->getTable(). ' as supplier', function ($join) use ($param) {
                    $join->on('prd_prd_supplier.supplier_id', '=', 'supplier.id');
                    if (is_array($param['search_supplier'])) {
                        $join->whereIn('supplier.id', $param['search_supplier']);
                    } else if (is_string($param['search_supplier']) || is_numeric($param['search_supplier'])) {
                        $join->where('supplier.id', $param['search_supplier']);
                    }
                });
            $rcv_depot_data['supplier_id'] = 'supplier.id';
            $rcv_depot_data['supplier_name'] = 'supplier.name';
        }
        $query_delivery->addSelect(DB::raw(concatStr($rcv_depot_data). ' as rcv_depot_data'));

        if (isset($param['delivery_sdate']) && isset($param['delivery_edate'])) {
            $delivery_sdate = date('Y-m-d 00:00:00', strtotime($param['delivery_sdate']));
            $delivery_edate = date('Y-m-d 23:59:59', strtotime($param['delivery_edate']));
            $query_delivery->whereBetween('delivery.audit_date', [$delivery_sdate, $delivery_edate]);
        }

        if ($param['keyword']) {
            $query_delivery->where(function ($query) use ($param) {
                $query->where('rcv_depot.product_title', 'like', "%" . $param['keyword'] . "%")
                    ->orWhere('rcv_depot.sku', 'like', "%" . $param['keyword'] . "%");
            });
        }
//        dd($query_delivery->get());

        //訂單
        $ord_item_data = [
            'ord_item_id' => 'ord_item.id'
            , 'ord_title' => DB::raw('ord_item.product_title')
            , 'product_style_id' => 'ord_item.product_style_id'
            , 'ord_price' => DB::raw('ord_item.price')
            , 'ord_qty' => 'ord_item.qty'
            , 'ord_origin_price' => 'ord_item.origin_price'
        ];
        $query_order = DB::table(app(Order::class)->getTable(). ' as ord_ord')
            ->leftJoin(app(SubOrders::class)->getTable(). ' as sub_ord', function ($join) {
                $join->on('sub_ord.order_id', '=', 'ord_ord.id');
            })
            ->leftJoin(app(OrderItem::class)->getTable(). ' as ord_item', function ($join) {
                $join->on('ord_item.order_id', '=', 'sub_ord.order_id')
                    ->on('ord_item.sub_order_id', '=', 'sub_ord.id');
            })
            ->leftJoin(app(Delivery::class)->getTable(). ' as delivery', function ($join) {
                $join->on('delivery.event_id', '=', 'sub_ord.id')
                    ->where('delivery.event', '=', Event::order()->value);
            })
            ->leftJoin(app(Customer::class)->getTable(). ' as customer', function ($join) {
                $join->on('customer.email', '=', 'ord_ord.email')
                    ->whereNotNull('ord_ord.email');
            })
//            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) {
//                $join->on('outs.event_item_id', '=', 'ord_item.id');
//                $join->where('outs.delivery_id', '=', 'dlv.id')
//                    ->where('delivery.event', '=', Event::order()->value)
//                    ->whereNull('delivery.deleted_at');
//            })
            ->select(
                'delivery.id as delivery_id'
                , 'delivery.event'
                , 'delivery.event_id'
                , 'ord_ord.status as ord_status'
                , 'sub_ord.order_id as order_id'
                , 'customer.name as buyer_name'
                , DB::raw('DATE_FORMAT(ord_ord.created_at,"%Y-%m-%d") as ord_created_at')
                , DB::raw(concatStr($ord_item_data). ' as ord_item_data')
            )
            ->groupBy('delivery.id')
        ;
//        dd($query_order->get());

        //寄倉
        $csn_item_data = [
            'ord_item_id' => 'csn_item.id'
            , 'ord_title' => DB::raw('csn_item.title')
            , 'product_style_id' => 'csn_item.product_style_id'
            , 'ord_price' => DB::raw('csn_item.price')
            , 'ord_qty' => 'csn_item.num'
            , 'ord_origin_price' => 'csn_item.price * csn_item.num'
        ];
        $query_csn = DB::table(app(Consignment::class)->getTable(). ' as csn')
            ->leftJoin(app(ConsignmentItem::class)->getTable(). ' as csn_item', function ($join) {
                $join->on('csn_item.consignment_id', '=', 'csn.id');
            })
            ->leftJoin(app(Delivery::class)->getTable(). ' as delivery', function ($join) {
                $join->on('delivery.event_id', '=', 'csn.id')
                    ->where('delivery.event', '=', Event::consignment()->value);
            })
            ->select(
                'delivery.id as delivery_id'
                , 'delivery.event'
                , 'delivery.event_id'
                , DB::raw('@null:=null as ord_status')
                , DB::raw('csn.id as order_id')
                , DB::raw('@null:=null as buyer_name')
                , DB::raw('DATE_FORMAT(csn.created_at,"%Y-%m-%d") as ord_created_at')
                , DB::raw(concatStr($csn_item_data). ' as ord_item_data')
            )
            ->whereNull('csn.deleted_at')
            ->whereNull('csn_item.deleted_at')
            ->groupBy('delivery.id');
//        dd($query_csn->get());

        //寄倉訂購
        $csnord_item_data = [
            'ord_item_id' => 'csnord_item.id'
            , 'ord_title' => DB::raw('csnord_item.title')
            , 'product_style_id' => 'csnord_item.product_style_id'
            , 'ord_price' => DB::raw('csnord_item.price')
            , 'ord_qty' => 'csnord_item.num'
            , 'ord_origin_price' => 'csnord_item.price * csnord_item.num'
        ];
        $query_csnord = DB::table(app(CsnOrder::class)->getTable(). ' as csnord')
            ->leftJoin(app(CsnOrderItem::class)->getTable(). ' as csnord_item', function ($join) {
                $join->on('csnord_item.csnord_id', '=', 'csnord.id');
            })
            ->leftJoin(app(Delivery::class)->getTable(). ' as delivery', function ($join) {
                $join->on('delivery.event_id', '=', 'csnord.id')
                    ->where('delivery.event', '=', Event::csn_order()->value);
            })
            ->select(
                'delivery.id as delivery_id'
                , 'delivery.event'
                , 'delivery.event_id'
                , DB::raw('@null:=null as ord_status')
                , DB::raw('csnord.id as order_id')
                , DB::raw('@null:=null as buyer_name')
                , DB::raw('DATE_FORMAT(csnord.created_at,"%Y-%m-%d") as ord_created_at')
                , DB::raw(concatStr($csnord_item_data). ' as ord_item_data')
            )
            ->whereNull('csnord.deleted_at')
            ->whereNull('csnord_item.deleted_at')
            ->groupBy('delivery.id');

        $query_order = $query_order->union($query_csn);
        $query_order = $query_order->union($query_csnord);

        $re = DB::query()->fromSub($query_delivery, 'dlv')
            ->leftJoinSub($query_order, 'order', function($join) {
                $join->on('order.delivery_id', '=', 'dlv.delivery_id')
                    ->on('order.event', '=', 'dlv.event')
                    ->on('order.event_id', '=', 'dlv.event_id');
            })
            ->select(
                'dlv.delivery_id'
                , 'dlv.event'
                , 'dlv.event_sn'
                , 'dlv.event_id'
                , 'dlv.sn'
                , 'dlv.audit_user_name'
                , 'dlv.audit_date'
                , 'dlv.created_at'
                , 'dlv.rcv_depot_data'
                , 'dlv.logistic_status'

                , 'order.ord_status'
                , 'order.order_id'
                , 'order.buyer_name'
                , 'order.ord_created_at'
                , 'order.ord_item_data'
            )
            ->groupBy('order.delivery_id')
            ->orderByDesc('order.delivery_id')
        ;
//        dd($re->get());

        return $re;
    }

    private static function getSumQtyWithRecDepot()
    {
        $sub_rec_depot = DB::table('dlv_receive_depot')
            ->whereNull('dlv_receive_depot.deleted_at')
            ->select('dlv_receive_depot.delivery_id'
                , 'dlv_receive_depot.event_item_id'
                , 'dlv_receive_depot.prd_type'
                , 'dlv_receive_depot.freebies'
                , 'dlv_receive_depot.product_style_id'
                , 'dlv_receive_depot.sku as rec_sku'
                , 'dlv_receive_depot.product_title as rec_product_title'
            )
            ->selectRaw('sum(dlv_receive_depot.qty) as send_qty')
            ->selectRaw('sum(dlv_receive_depot.back_qty) as back_qty')
            ->groupBy('dlv_receive_depot.delivery_id')
            ->groupBy('dlv_receive_depot.event_item_id')
            ->groupBy('dlv_receive_depot.prd_type')
            ->groupBy('dlv_receive_depot.freebies')
            ->groupBy('dlv_receive_depot.product_style_id')
            ->groupBy('dlv_receive_depot.sku')
            ->groupBy('dlv_receive_depot.product_title');
        return $sub_rec_depot;
    }

    //取得物流頁顯示的 子訂單-出貨商品列表
    public static function getOrderListToLogistic($delivery_id, $order_id = null, $sub_order_id = null)
    {
        $sub_rec_depot = self::getSumQtyWithRecDepot();

        $sub_orders = DB::table('ord_sub_orders')
            ->leftJoin('ord_items as items', function ($join) {
                $join->on('items.sub_order_id', '=', 'ord_sub_orders.id');
            })
            ->select('items.id as item_id'
                , 'items.order_id'
                , 'items.sub_order_id'
                , 'items.product_style_id'
                , 'items.sku'
                , 'items.product_title'
                , 'items.price'
                , 'items.qty'
                , 'items.type'
            );

        $query = DB::table(DB::raw("({$sub_rec_depot->toSql()}) as rec_depot"))
            ->leftJoinSub($sub_orders, 'orders', function ($join) use ($delivery_id) {
                $join->on('orders.item_id', '=', 'rec_depot.event_item_id');
                $join->where('rec_depot.delivery_id', $delivery_id);
            })
            ->whereIn('rec_depot.prd_type', ['p', 'c'])
            ->whereNotNull('rec_depot.delivery_id')
            ->whereNotNull('orders.item_id')
            ->select('*');

        if (isset($order_id)) {
            $query->where('orders.order_id', $order_id);
        }
        if (isset($sub_order_id)) {
            $query->where('orders.sub_order_id', $sub_order_id);
        }

        return $query;
    }

    //取得物流頁顯示的 寄倉商品列表
    public static function getCsnListToLogistic($delivery_id, $consignment_id = null)
    {
        $sub_rec_depot = self::getSumQtyWithRecDepot();

        $sub_orders = DB::table('csn_consignment')
            ->leftJoin('csn_consignment_items as items', function ($join) {
                $join->on('items.consignment_id', '=', 'csn_consignment.id');
            })
            ->select('items.id as item_id'
                , 'items.consignment_id'
                , 'items.product_style_id'
                , 'items.title as product_title'
                , 'items.sku'
                , 'items.price'
                , 'items.num'
                , 'items.memo'
                , 'items.created_at'
            );

        $query = DB::table(DB::raw("({$sub_rec_depot->toSql()}) as rec_depot"))
            ->leftJoinSub($sub_orders, 'csn', function ($join) use ($delivery_id) {
                $join->on('csn.item_id', '=', 'rec_depot.event_item_id');
                $join->where('rec_depot.delivery_id', $delivery_id);
            })
            ->whereIn('rec_depot.prd_type', ['p', 'c'])
            ->whereNotNull('rec_depot.delivery_id')
            ->whereNotNull('csn.item_id')
            ->select('*');

        if (isset($consignment_id)) {
            $query->where('csn.consignment_id', $consignment_id);
        }

        return $query;
    }

    //取得物流頁顯示的 寄倉商品列表
    public static function getCsnOrderListToLogistic($delivery_id, $csn_order_id = null)
    {
        $sub_rec_depot = self::getSumQtyWithRecDepot();

        $sub_orders = DB::table('csn_orders as csnord')
            ->leftJoin('csn_order_items as items', function ($join) {
                $join->on('items.csnord_id', '=', 'csnord.id');
            })
            ->select('items.id as item_id'
                , 'items.csnord_id'
                , 'items.product_style_id'
                , 'items.title as product_title'
                , 'items.sku'
                , 'items.price'
                , 'items.num'
                , 'items.memo'
                , 'items.created_at'
            );

        $query = DB::table(DB::raw("({$sub_rec_depot->toSql()}) as rec_depot"))
            ->leftJoinSub($sub_orders, 'sub_csnord', function ($join) use ($delivery_id) {
                $join->on('sub_csnord.item_id', '=', 'rec_depot.event_item_id');
                $join->where('rec_depot.delivery_id', $delivery_id);
            })
            ->whereNotNull('rec_depot.delivery_id')
            ->whereNotNull('sub_csnord.item_id')
            ->select('rec_depot.delivery_id'
                , 'rec_depot.event_item_id'
                , 'rec_depot.prd_type'
                , 'rec_depot.freebies'
                , 'rec_depot.product_style_id'
                , 'rec_depot.rec_sku'
                , 'rec_depot.rec_product_title'
                , DB::raw('(case when "c" = rec_depot.prd_type then "組合包" else rec_depot.rec_product_title end) as rec_product_title')
                , 'rec_depot.send_qty'
                , 'sub_csnord.item_id'
                , 'sub_csnord.csnord_id'
                , 'sub_csnord.product_title'
                , 'sub_csnord.sku'
                , 'sub_csnord.price'
                , 'sub_csnord.num'
                , 'sub_csnord.memo'
                , 'sub_csnord.created_at'
            );

        if (isset($csn_order_id)) {
            $query->where('sub_csnord.csnord_id', $csn_order_id);
        }

        return $query;
    }

    public static function getDeliveryWithEventWithSn($event, $event_id)
    {
        $query = DB::table('dlv_delivery as delivery');
        if (isset($event)) {
            $query->where('delivery.event', $event);
        }
        if (isset($event_id)) {
            $query->where('delivery.event_id', $event_id);
        }
        return $query;
    }

    public static function changeBackStatus($delivery_id, BackStatus $status)
    {
        if (false == BackStatus::hasKey($status->key)) {
            throw ValidationException::withMessages(['error_msg' => '無此退貨狀態']);
        }

        Delivery::where('id', '=', $delivery_id)->update([
            'back_status' => $status->value
            , 'back_status_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function delivery_item($delivery_id = null, $behavior, $bac_papa_id = null)
    {
        $sq = '
            SELECT
                acc_all_grades.id,
                CASE
                    WHEN acc_first_grade.code IS NOT NULL THEN acc_first_grade.code
                    WHEN acc_second_grade.code IS NOT NULL THEN acc_second_grade.code
                    WHEN acc_third_grade.code IS NOT NULL THEN acc_third_grade.code
                    WHEN acc_fourth_grade.code IS NOT NULL THEN acc_fourth_grade.code
                    ELSE ""
                END AS code,
                CASE
                    WHEN acc_first_grade.name IS NOT NULL THEN acc_first_grade.name
                    WHEN acc_second_grade.name IS NOT NULL THEN acc_second_grade.name
                    WHEN acc_third_grade.name IS NOT NULL THEN acc_third_grade.name
                    WHEN acc_fourth_grade.name IS NOT NULL THEN acc_fourth_grade.name
                    ELSE ""
                END AS name
            FROM acc_all_grades
            LEFT JOIN acc_first_grade ON acc_all_grades.grade_id = acc_first_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FirstGrade"
            LEFT JOIN acc_second_grade ON acc_all_grades.grade_id = acc_second_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\SecondGrade"
            LEFT JOIN acc_third_grade ON acc_all_grades.grade_id = acc_third_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\ThirdGrade"
            LEFT JOIN acc_fourth_grade ON acc_all_grades.grade_id = acc_fourth_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FourthGrade"
            GROUP BY acc_all_grades.id
        ';

        $s_q_table_where = '';
        if($behavior == 'return'){
            $s_q_table = 'dlv_back';
            $s_q_po_type = 9;
            $s_q_table_where = $bac_papa_id ? ' AND dlv_tmp.bac_papa_id = ' . $bac_papa_id : '';

        } else if($behavior == 'out'){
            $s_q_table = 'dlv_out_stock';
            $s_q_po_type = 8;

        } else if($behavior == 'exchange'){
            $s_q_table = 'dlv_back';
            $s_q_po_type = 7;
        }

        $query = DB::table('dlv_delivery as delivery')
            ->leftJoin(DB::raw('(
                SELECT
                    delivery_id,
                    ' . ($bac_papa_id ?? "NULL") . ' AS bac_papa_id,
                    SUM(dlv_tmp.price * dlv_tmp.qty) AS total_price,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "id":"\', dlv_tmp.id, \'",
                        "event_item_id":"\', COALESCE(dlv_tmp.event_item_id, ""), \'",
                        "sku":"\', COALESCE(dlv_tmp.sku, ""), \'",
                        "product_title":"\', dlv_tmp.product_title, \'",
                        "price":"\', dlv_tmp.price, \'",
                        "qty":"\', dlv_tmp.qty, \'",
                        "total_price":"\', dlv_tmp.price * dlv_tmp.qty, \'",
                        "grade_id":"\', COALESCE(grade.id, dlv_tmp.grade_id), \'",
                        "grade_code":"\', COALESCE(grade.code, ""), \'",
                        "grade_name":"\', COALESCE(grade.name, ""), \'",
                        "memo":"\', COALESCE(dlv_tmp.memo, ""), \'",
                        "note":"\', COALESCE(ord_items.note, ""), \'",
                        "po_note":"\', COALESCE(ord_items.po_note, ""), \'",
                        "taxation":"\', COALESCE(product.has_tax, 1), \'"
                    }\' ORDER BY dlv_tmp.id), \']\') AS items
                FROM ' . $s_q_table . ' AS dlv_tmp
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = dlv_tmp.grade_id
                LEFT JOIN prd_product_styles ON prd_product_styles.id = dlv_tmp.product_style_id
                LEFT JOIN ord_items ON ord_items.id = dlv_tmp.event_item_id
                LEFT JOIN prd_products AS product ON product.id = prd_product_styles.product_id
                WHERE (product.deleted_at IS NULL AND dlv_tmp.qty > 0 AND dlv_tmp.show = 1' . $s_q_table_where . ')
                GROUP BY delivery_id
                ) AS delivery_tmp'), function ($join) {
                $join->on('delivery_tmp.delivery_id', '=', 'delivery.id');
            })
            ->leftJoin('ord_sub_orders AS sub_order', function ($join) {
                $join->on('sub_order.id', '=', 'delivery.event_id');
                $join->where([
                    'delivery.event' => 'order',
                ]);
            })
            ->leftJoin('ord_orders as order', 'order.id', '=', 'sub_order.order_id')
            ->leftJoin('usr_customers as buyer', 'buyer.email', '=', 'order.email')
            ->leftJoin('usr_customers_address AS buyer_add', function ($join) {
                $join->on('buyer.id', '=', 'buyer_add.usr_customers_id_fk');
                $join->where([
                    'buyer_add.is_default_addr' => 1,
                ]);
            })
            ->leftJoin('pcs_paying_orders AS po', function ($join) use($s_q_po_type, $bac_papa_id) {
                $join->on('po.source_id', '=', 'delivery.id');
                $join->where([
                    'po.source_type' => app(self::class)->getTable(),
                    'po.source_sub_id' => $bac_papa_id,
                    'po.type' => $s_q_po_type,
                    'po.deleted_at' => null,
                ]);
            })
            ->where(function ($q) use ($delivery_id) {
                if ($delivery_id) {
                    if (gettype($delivery_id) == 'array') {
                        $q->whereIn('delivery.id', $delivery_id);
                    } else {
                        $q->where('delivery.id', $delivery_id);
                    }
                }

                $q->where('delivery.deleted_at', null);
            })

            ->select(
                'delivery.id AS delivery_id',
                'delivery.sn AS delivery_sn',
                'delivery.event AS delivery_event',
                'delivery.event_id AS delivery_event_id',
                'delivery.event_sn AS delivery_event_sn',
                'delivery.memo AS delivery_memo',

                'delivery_tmp.items AS delivery_items',
                'delivery_tmp.total_price AS delivery_total_price',
                'delivery_tmp.bac_papa_id AS delivery_bac_papa_id',

                'order.id AS order_id',
                'order.sn AS order_sn',
                'order.dlv_fee AS order_dlv_fee',
                'order.origin_price AS order_origin_price',
                'order.total_price AS order_total_price',
                'order.discount_value AS order_discount_value',
                'order.dlv_taxation AS order_dlv_taxation',
                'order.note AS order_note',

                'sub_order.id AS sub_order_id',
                'sub_order.sn AS sub_order_sn',
                'sub_order.ship_category AS sub_order_ship_category',
                'sub_order.ship_category_name AS sub_order_ship_category_name',
                'sub_order.ship_event AS sub_order_ship_event',
                'sub_order.dlv_fee AS sub_order_dlv_fee',
                'sub_order.total_price AS sub_order_total_price',
                'sub_order.discount_value AS sub_order_discount_value',

                'buyer.id AS buyer_id',
                'buyer.name AS buyer_name',
                'buyer.phone AS buyer_phone',
                'buyer.email AS buyer_email',
                'buyer_add.address AS buyer_address',

                'po.id AS po_id',
                'po.sn AS po_sn',
                'po.price AS po_price',
                'po.balance_date AS po_balance_date',
                'po.summary AS po_summary',
                'po.memo AS po_memo',
                'po.payee_id AS po_payee_id',
                'po.payee_name AS po_payee_name',
                'po.payee_phone AS po_payee_phone',
                'po.payee_address AS po_payee_address',
                'po.created_at AS po_created_at',
                DB::raw('"' . PayingOrder::paying_order_link('dlv_delivery', $delivery_id, $bac_papa_id, $s_q_po_type) . '" AS po_link'),
                DB::raw('"' . PayingOrder::paying_order_source_link('dlv_delivery', $delivery_id, $bac_papa_id, $s_q_po_type) . '" AS po_source_link')
            )
            ->orderBy('delivery.id', 'desc');

        return $query;
    }
}
