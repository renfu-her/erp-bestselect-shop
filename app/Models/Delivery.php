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

    public static function deleteByEventId($event, $event_id)
    {
        return IttmsDBB::transaction(function () use ($event, $event_id
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
        if (isset($param['ship_method']) && 0 < count($param['ship_method'])) {
            $query->whereIn('shi_method.method', $param['ship_method']);
        }
        if (isset($param['logistic_status_code']) && 0 < count($param['logistic_status_code'])) {
            $query->whereIn('delivery.logistic_status_code', $param['logistic_status_code']);
        }
        if (isset($param['ship_category']) && 0 < count($param['ship_category'])) {
            $query->whereIn('query_order.ship_category', $param['ship_category']);
        }
        if (isset($param['order_status']) && 0 < count($param['order_status'])) {
            $query->whereIn('query_order.order_status', $param['order_status']);
        }
        if (isset($param['has_csn']) && "false" == $param['has_csn']) {
            $query->whereNotIn('delivery.event', [Event::consignment()->value]);
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

    public static function back_item($delivery_id = null)
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

        $query = DB::table('dlv_delivery as delivery')
            ->leftJoin(DB::raw('(
                SELECT
                    delivery_id,
                    SUM(dlv_back.price * dlv_back.qty) AS total_price,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "id":"\', dlv_back.id, \'",
                        "event_item_id":"\', COALESCE(dlv_back.event_item_id, ""), \'",
                        "sku":"\', COALESCE(dlv_back.sku, ""), \'",
                        "product_title":"\', dlv_back.product_title, \'",
                        "price":"\', dlv_back.price, \'",
                        "qty":"\', dlv_back.qty, \'",
                        "total_price":"\', dlv_back.price * dlv_back.qty, \'",
                        "grade_id":"\', COALESCE(grade.id, dlv_back.grade_id), \'",
                        "grade_code":"\', COALESCE(grade.code, ""), \'",
                        "grade_name":"\', COALESCE(grade.name, ""), \'",
                        "memo":"\', COALESCE(dlv_back.memo, ""), \'",
                        "note":"\', COALESCE(ord_items.note, ""), \'",
                        "po_note":"\', COALESCE(ord_items.po_note, ""), \'",
                        "taxation":"\', COALESCE(product.has_tax, 1), \'"
                    }\' ORDER BY dlv_back.id), \']\') AS items
                FROM dlv_back
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = dlv_back.grade_id
                LEFT JOIN prd_product_styles ON prd_product_styles.id = dlv_back.product_style_id
                LEFT JOIN ord_items ON ord_items.id = dlv_back.event_item_id
                LEFT JOIN prd_products AS product ON product.id = prd_product_styles.product_id
                WHERE (product.deleted_at IS NULL AND dlv_back.qty > 0 AND dlv_back.show = 1)
                GROUP BY delivery_id
                ) AS delivery_back'), function ($join) {
                $join->on('delivery_back.delivery_id', '=', 'delivery.id');
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
            ->leftJoin('pcs_paying_orders AS po', function ($join) {
                $join->on('po.source_id', '=', 'delivery.id');
                $join->where([
                    'po.source_type' => app(self::class)->getTable(),
                    'po.source_sub_id' => null,
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

                'delivery_back.items AS delivery_back_items',
                'delivery_back.total_price AS delivery_back_total_price',

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
                'po.created_at AS po_created_at'
            )
            ->orderBy('delivery.id', 'desc');

        return $query;
    }
}
