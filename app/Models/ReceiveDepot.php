<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReceiveDepot extends Model
{
    use HasFactory;

    protected $table = 'dlv_receive_depot';
    public $timestamps = false;
    protected $guarded = [];

    public static function setData($id = null, $delivery_id, $event_item_id = null, $combo_id = null, $prd_type = null, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date)
    {
        $data = null;
        $dataGet = null;
        if (null != $id) {
            $data = ReceiveDepot::where('id', $id);
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null == $dataGet) {
            $result = ReceiveDepot::create([
                'delivery_id' => $delivery_id,
                'event_item_id' => $event_item_id,
                //'combo_id' => $combo_id, //剛創建不會有 須等到出貨送出 確認是組合包的元素商品 才會回寫
                'prd_type' => $prd_type,
                'freebies' => $freebies,
                'inbound_id' => $inbound_id,
                'inbound_sn' => $inbound_sn,
                'depot_id' => $depot_id,
                'depot_name' => $depot_name,
                'product_style_id' => $product_style_id,
                'sku' => $sku,
                'product_title' => $product_title,
                'qty' => $qty,
                'expiry_date' => $expiry_date,
            ])->id;
        } else {
            $result = DB::transaction(function () use ($data, $dataGet, $combo_id, $prd_type, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date
            ) {
                $data->update([
                    'freebies' => $freebies,
                    'combo_id' => $combo_id, //剛創建不會有 須等到出貨送出 確認是組合包的元素商品 才會回寫
                    'prd_type' => $prd_type,
                    'inbound_id' => $inbound_id,
                    'inbound_sn' => $inbound_sn,
                    'depot_id' => $depot_id,
                    'depot_name' => $depot_name,
                    'product_style_id' => $product_style_id,
                    'sku' => $sku,
                    'product_title' => $product_title,
                    'qty' => $qty,
                    'expiry_date' => $expiry_date,
                ]);
                return $dataGet->id;
            });
        }
        return ['success' => 1, 'error_msg' => "", 'id' => $result];
    }

    /**
     * 新增對應的入庫商品款式
     * @param $input_arr inbound_id:入庫單ID ; qty:數量
     * @param $delivery_id 出貨單ID
     * @param $itemId 子訂單對應商品 ord_items id
     * @return array|mixed|void
     */
    public static function setDatasWithDeliveryIdWithItemId($input_arr, $delivery_id, $itemId) {
        $delivery = Delivery::where('id', $delivery_id)->get()->first();

            return DB::transaction(function () use ($delivery_id, $delivery, $itemId, $input_arr
            ) {
                if (null != $input_arr['qty'] && 0 < count($input_arr['qty'])) {
                    $addIds = [];
                    foreach($input_arr['qty'] as $key => $val) {
                        //取得delivery event = consignment
                        //	用event_item_id取得csn_consignment_items prd_type
                        //		若c 則寫入element
                        //		若p 則寫入product
                        $rcv_depot_type = '';
                        $item = null;
                        if (Event::order()->value == $delivery->event) {
                            $item = DB::table('ord_items as items')
                                ->leftJoin('prd_product_styles as styles', 'styles.id', '=', 'items.product_style_id')
                                ->select('items.*', 'styles.type as prd_type')
                                ->where('items.id', $itemId)
                                ->get()->first();
                        } else if (Event::consignment()->value == $delivery->event) {
                            $item = ConsignmentItem::where('id', $itemId)->get()->first();
                        }
                        if ('p' == $item->prd_type) {
                            $rcv_depot_type = 'p';
                        } elseif ('c' == $item->prd_type) {
                            $rcv_depot_type = 'ce';
                        }
                        $inbound = PurchaseInbound::getSelectInboundList(['inbound_id' => $input_arr['inbound_id'][$key]])->get()->first();
                        if (null != $inbound) {
                            if (0 > $inbound->qty - $val) {
                                return ['success' => 0, 'error_msg' => "庫存數量不足"];
                            }
                            $reSD = ReceiveDepot::setData(
                                null,
                                $delivery_id, //出貨單ID
                                $itemId ?? null, //子訂單商品ID
                                null, //組合包商品ID
                                $rcv_depot_type ?? null, //商品類型
                                $input_arr['freebies'][$key] ?? 0, //是否為贈品 0:否
                                $inbound->inbound_id,
                                $inbound->inbound_sn,
                                $inbound->depot_id,
                                $inbound->depot_name,
                                $inbound->product_style_id,
                                $inbound->style_sku,
                                $inbound->product_title. '-'. $inbound->style_title,
                                $val, //數量
                                $inbound->expiry_date);
                            if ($reSD['success'] == 0) {
                                DB::rollBack();
                                return $reSD;
                            } else {
                                array_push($addIds, $reSD['id']);
                            }
                        }
                    }
                    return ['success' => 1, 'error_msg' => "", 'id' => $addIds];
                } else {
                    return ['success' => 0, 'error_msg' => "未輸入數量"];
                }
            });
    }

    //將收貨資料變更為成立
    public static function setUpShippingData($event, $event_id, $delivery_id, $user_id, $user_name) {
        $delivery = Delivery::where('id', $delivery_id)->get()->first();
        $rcvDepotGet = null;
        if (null != $delivery_id) {
            $rcvDepot = ReceiveDepot::where('delivery_id', $delivery_id);
            $rcvDepotGet = $rcvDepot->get();
        }
        if (null != $delivery &&null != $rcvDepotGet && 0 < count($rcvDepotGet)) {
                $result = DB::transaction(function () use ($delivery, $rcvDepot, $rcvDepotGet, $event, $event_id, $delivery_id, $user_id, $user_name
                ) {
                    //判斷若為組合包商品 則需新建立一筆資料組合成組合包 並回寫新id
                    $queryComboElement = null;
                    if (Event::order()->value == $delivery->event) {
                        if ('deliver' == $delivery->ship_category) {

                        } else if ('pickup' == $delivery->ship_category) {
                            $event = 'ord_pickup';
                            $queryComboElement = DB::table('dlv_delivery as delivery')
                                ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
                                ->leftJoin('ord_items as items', 'items.id', '=', 'rcv_depot.event_item_id')
                                ->leftJoin('ord_sub_orders as sub_orders', function ($join) {
                                    $join->on('sub_orders.id', '=', 'items.sub_order_id');
                                    $join->on('sub_orders.order_id', 'items.order_id');
                                })
                                ->where('delivery.id', $delivery_id)
                                ->where('delivery.event', Event::order()->value)
                                ->where('rcv_depot.prd_type', 'ce')
                                ->where('sub_orders.ship_category', 'pickup')
                                ->whereNull('delivery.deleted_at')
                                ->whereNull('delivery.deleted_at')
                                ->select(
                                    'rcv_depot.delivery_id'
                                    , 'rcv_depot.event_item_id'
                                    , DB::raw('min(rcv_depot.expiry_date) as expiry_date')
                                    , 'items.product_style_id'
                                    , 'items.product_title as title'
                                    , 'rcv_depot.prd_type as prd_type'
                                    , 'items.sku'
                                    , 'items.qty as num'
                                    , 'rcv_depot.depot_id as depot_id'
                                    , 'rcv_depot.depot_name as depot_name'
                                )
                                ->groupBy('rcv_depot.delivery_id')
                                ->groupBy('rcv_depot.event_item_id')
                                ->groupBy('items.product_style_id')
                                ->groupBy('items.product_title')
                                ->groupBy('rcv_depot.prd_type')
                                ->groupBy('items.sku')
                                ->groupBy('rcv_depot.depot_id')
                                ->groupBy('rcv_depot.depot_name')
                                ->get();
                        }
                    }
                    if (Event::consignment()->value == $delivery->event) {
                        $queryComboElement = DB::table('dlv_delivery as delivery')
                            ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
                            ->leftJoin('csn_consignment_items as items', 'items.id', '=', 'rcv_depot.event_item_id')
                            ->leftJoin('csn_consignment as consignment', 'consignment.id', '=', 'items.consignment_id')
                            ->where('delivery.id', $delivery_id)
                            ->where('delivery.event', Event::consignment()->value)
                            ->where('rcv_depot.prd_type', 'ce')
                            ->whereNull('delivery.deleted_at')
                            ->whereNull('delivery.deleted_at')
                            ->whereNull('items.deleted_at')
                            ->select(
                                'rcv_depot.delivery_id'
                                , 'rcv_depot.event_item_id'
                                , DB::raw('min(rcv_depot.expiry_date) as expiry_date')
                                , 'items.product_style_id'
                                , 'items.title'
                                , 'items.prd_type'
                                , 'items.sku'
                                , 'items.num'
                                , 'consignment.send_depot_id as depot_id'
                                , 'consignment.send_depot_name as depot_name'
                            )
                            ->groupBy('rcv_depot.delivery_id')
                            ->groupBy('rcv_depot.event_item_id')
                            ->groupBy('items.product_style_id')
                            ->groupBy('items.title')
                            ->groupBy('items.prd_type')
                            ->groupBy('items.sku')
                            ->groupBy('consignment.send_depot_id')
                            ->groupBy('consignment.send_depot_name')
                            ->get();
                    }
                    // 寄倉、訂單自取才會有
                    if (null != $queryComboElement && 0 < count($queryComboElement)) {
                        //新增並回寫ID
                        foreach($queryComboElement as $key => $element) {
                            $reSD = ReceiveDepot::setData(
                                null,
                                $delivery_id, //出貨單ID
                                $element->event_item_id, //子訂單商品ID
                                null, //組合包商品ID
                                'c', //商品類型
                                0, //是否為贈品 0:否
                                0,
                                '',
                                $element->depot_id,
                                $element->depot_name,
                                $element->product_style_id,
                                $element->sku,
                                $element->title,
                                $element->num, //數量
                                $element->expiry_date);
                            if ($reSD['success'] == 0) {
                                DB::rollBack();
                                return $reSD;
                            } else {
                                $rcvDepot_elements = ReceiveDepot::where('delivery_id', $delivery_id)
                                    ->where('event_item_id', $element->event_item_id)
                                    ->where('prd_type', 'ce')
                                ;
                                $rcvDepot_elements->update([
                                    'combo_id' => $reSD['id'],
                                ]);
                            }
                        }
                    }

                    //扣除入庫單庫存
                    foreach ($rcvDepotGet as $item) {
                        $reShipIb = PurchaseInbound::shippingInbound($event, $event_id, $item->id, LogEventFeature::delivery()->value, $item->inbound_id, $item->qty);
                        if ($reShipIb['success'] == 0) {
                            DB::rollBack();
                            return $reShipIb;
                        }
                    }

                    $curr_date = date('Y-m-d H:i:s');
                    Delivery::where('id', '=', $delivery_id)->update([
                        'audit_date' => $curr_date,
                        'audit_user_id' => $user_id,
                        'audit_user_name' => $user_name,]);

                    $rcvDepot->update([
                        'audit_date' => $curr_date,
                    ]);

                    return ['success' => 1, 'error_msg' => ""];
                });
        } else {
            return ['success' => 0, 'error_msg' => "無此出貨單"];
        }
        return $result;
    }

    public static function deleteById($id)
    {
        ReceiveDepot::where('id', $id)->delete();
    }

    //更新寄倉到貨數量
    public static function updateCSNArrivedNum($id, $addnum) {
        return DB::transaction(function () use ($id, $addnum
        ) {
            $updateArr = [];
            $updateArr['csn_arrived_qty'] = DB::raw("csn_arrived_qty + $addnum");
            ReceiveDepot::where('id', $id)
                ->update($updateArr);
            return ['success' => 1, 'error_msg' => ""];
        });
    }

    public static function getDataList($param) {
        $query = DB::table('dlv_receive_depot as rcv_depot')
            ->select('rcv_depot.id as id'
                , 'rcv_depot.order_id as order_id'
                , 'rcv_depot.sub_order_id as sub_order_id'
                , 'rcv_depot.inbound_id as inbound_id'
                , 'rcv_depot.depot_id as depot_id'
                , 'rcv_depot.depot_name as depot_name'
                , 'rcv_depot.product_style_id as product_style_id'
                , 'rcv_depot.qty as qty'
                , 'rcv_depot.expiry_date as expiry_date'
            );

        if (isset($param['delivery_id'])) {
            $query->where('rcv_depot.delivery_id', '=', $param['delivery_id']);
        }
        return $query;
    }

    //取得寄倉入庫商品應進數量
    public static function getShouldEnterNumDataList($event, $event_id) {
        $raw = '( COALESCE(rcv_depot.qty, 0) - COALESCE(rcv_depot.csn_arrived_qty, 0) )';
        $result = DB::table('dlv_delivery as delivery')
            ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
            ->select('*'
                , 'rcv_depot.id as rcv_deppot_id'
            )
            ->selectRaw('DATE_FORMAT(expiry_date,"%Y-%m-%d") as expiry_date')
            ->selectRaw($raw.' as should_enter_num')
//            ->where(DB::raw($raw), '>', 0)
            ->where('delivery.event', $event)
            ->where('delivery.event_id', $event_id)
            ->whereNotNull('delivery.audit_date') //判斷有做過出貨審核才給入
            ->whereNotNull('rcv_depot.id');
        //判斷寄倉 則會有組合包 需去除組合元素
        $result->where('rcv_depot.prd_type', '<>', 'ce');
        return $result;
    }

    //取得出貨列表
    public static function getDeliveryWithReceiveDepotList($event = null, $event_id = null, $delivery_id = null, $product_style_id = null)
    {
        $result = DB::table('dlv_delivery as delivery')
            ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
            ->select('delivery.sn as delivery_sn'
                , 'rcv_depot.delivery_id as delivery_id'
                , 'rcv_depot.id as id'
                , 'rcv_depot.event_item_id as event_item_id'
                , 'rcv_depot.freebies as freebies'
                , 'rcv_depot.inbound_id as inbound_id'
                , 'rcv_depot.inbound_sn as inbound_sn'
                , 'rcv_depot.depot_id as depot_id'
                , 'rcv_depot.depot_name as depot_name'
                , 'rcv_depot.product_style_id as product_style_id'
                , 'rcv_depot.sku as sku'
                , 'rcv_depot.product_title as product_title'
                , 'rcv_depot.qty as qty'
                , 'rcv_depot.expiry_date as expiry_date'
                , 'rcv_depot.audit_date as audit_date'
            )
            ->whereNull('rcv_depot.deleted_at');

        if (null != $event) {
            $result->where('delivery.event', $event);
        }
        if (null != $event_id) {
            $result->where('delivery.event_id', $event_id);
        }
        if (null != $delivery_id) {
            $result->where('rcv_depot.delivery_id', $delivery_id);
        }
        if (null != $product_style_id) {
            $result->where('rcv_depot.product_style_id', $product_style_id);
        }

        $result->orderBy('rcv_depot.id');
        return $result;
    }

    //取得子訂單商品列表 與對應的出貨列表
    public static function getOrderShipItemWithDeliveryWithReceiveDepotList($event, $sub_order_id, $delivery_id, $product_style_id = null) {
        // 子訂單的商品列表
        $ord_items = OrderItem::getShipItem($sub_order_id)->get();
        // 對應的出貨資料
        $ord_items_arr = ReceiveDepot::getReceiveDepotParseData($event, $sub_order_id, $delivery_id, $product_style_id, $ord_items);
        return $ord_items_arr;
    }

    public static function getCSNShipItemWithDeliveryWithReceiveDepotList($event, $consignment_id, $delivery_id, $product_style_id = null) {
        $query_combo = DB::table('prd_style_combos')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'prd_style_combos.product_style_child_id')
            ->select('prd_style_combos.product_style_id'
                , 'prd_style_combos.qty'
                , 'prd_product_styles.id'
                , 'prd_product_styles.product_id'
                , 'prd_product_styles.title'
                , 'prd_product_styles.sku'
                , 'prd_product_styles.type'
            );

        //取得子訂單商品內 組合包拆解內容
        $query_ship_combo = DB::table('csn_consignment_items as item')
            ->leftJoin(DB::raw("({$query_combo->toSql()}) as tb_combo"), function ($join) {
                $join->on('tb_combo.product_style_id', '=', 'item.product_style_id');
            })
            ->leftJoin('prd_products', 'prd_products.id', '=', 'tb_combo.product_id')
            ->select('item.id AS item_id'
                , 'item.consignment_id AS consignment_id'
                , 'item.price'
                , 'item.prd_type'
                , 'item.memo'
                , 'item.created_at'
                , 'item.updated_at'
                , 'item.deleted_at'

                , 'item.title AS combo_product_title'
                , 'tb_combo.id AS product_style_id'
                , 'tb_combo.sku'
            )
            ->selectRaw(DB::raw('( item.num * tb_combo.qty ) AS qty'))
            ->selectRaw(DB::raw('Concat(prd_products.title, "-", tb_combo.title) AS product_title'))
            ->whereNotNull('tb_combo.type')
            ->whereNull('item.deleted_at')
            ->where('item.consignment_id', $consignment_id)
            ->mergeBindings($query_combo);

        //取得寄倉單 一般商品
        $csn_items = DB::table('csn_consignment_items as item')
            ->leftJoin('prd_product_styles', 'prd_product_styles.id', '=', 'item.product_style_id')
            ->select('item.id as item_id'
                , 'item.consignment_id'
                , 'item.price'
                , 'item.prd_type'
                , 'item.memo'
                , 'item.created_at'
                , 'item.updated_at'
                , 'item.deleted_at'
                , DB::raw('@item.title:=null as combo_product_title')
                , 'item.product_style_id'
                , 'item.sku'
                , 'item.num as qty'
                , 'item.title as product_title'
            )
            ->whereNull('item.deleted_at')
            ->where('prd_product_styles.type', '=', 'p')
            ->where('item.consignment_id', $consignment_id);
        $query_ship_overview = $csn_items->union($query_ship_combo);

        // 對應的出貨資料
        $ord_items_arr = ReceiveDepot::getReceiveDepotParseData($event, $consignment_id, $delivery_id, $product_style_id, $query_ship_overview->get());
        return $ord_items_arr;
    }

    private static function getReceiveDepotParseData($event, $event_id, $delivery_id, $product_style_id, $obj_items) {
        $obj_items_arr = null;
        if (null != $obj_items && 0 < count($obj_items)) {
            $receiveDepotList = ReceiveDepot::getDeliveryWithReceiveDepotList($event, $event_id, $delivery_id, $product_style_id)->get();
            $obj_items_arr = $obj_items;
            foreach ($obj_items_arr as $ord_key => $ord_item) {
                $obj_items_arr[$ord_key]->receive_depot = [];
            }
            if (0 < count($receiveDepotList)) {
                $receiveDepotList_arr = $receiveDepotList->toArray();
                foreach ($obj_items_arr as $ord_key => $ord_item) {
                    $obj_items_arr[$ord_key]->receive_depot = [];
                    foreach ($receiveDepotList_arr as $revd_key => $revd_item) {
                        if ($obj_items_arr[$ord_key]->item_id == $revd_item->event_item_id
                            && $obj_items_arr[$ord_key]->product_style_id == $revd_item->product_style_id
                        ) {
                            array_push($obj_items_arr[$ord_key]->receive_depot, $receiveDepotList_arr[$revd_key]);
                            unset($receiveDepotList_arr[$revd_key]);
                        }
                    }
                }
            }
        }
        return $obj_items_arr;
    }

}
