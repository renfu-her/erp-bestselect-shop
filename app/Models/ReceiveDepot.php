<?php

namespace App\Models;

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

    public static function setData($id = null, $delivery_id, $event_item_id = null, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date)
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
            $result = DB::transaction(function () use ($data, $dataGet, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date
            ) {
                $data->update([
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
            return DB::transaction(function () use ($delivery_id, $itemId, $input_arr
            ) {
                if (null != $input_arr['qty'] && 0 < count($input_arr['qty'])) {
                    $addIds = [];
                    foreach($input_arr['qty'] as $key => $val) {
                        $inbound = PurchaseInbound::getSelectInboundList(['inbound_id' => $input_arr['inbound_id'][$key]])->get()->first();
                        if (null != $inbound) {
                            if (0 > $inbound->qty - $val) {
                                return ['success' => 0, 'error_msg' => "庫存數量不足"];
                            }
                            $reSD = ReceiveDepot::setData(
                                null,
                                $delivery_id, //出貨單ID
                                $itemId ?? null, //子訂單商品ID
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
        $result = null;
        if (null != $delivery &&null != $rcvDepotGet && 0 < count($rcvDepotGet)) {
                $result = DB::transaction(function () use ($delivery, $rcvDepot, $rcvDepotGet, $event, $event_id, $delivery_id, $user_id, $user_name
                ) {
                    //扣除入庫單庫存
                    foreach ($rcvDepotGet as $item) {
                        $reShipIb = PurchaseInbound::shippingInbound($event, $event_id, LogEventFeature::order_shipping()->value, $item->inbound_id, $item->qty);
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
            ->whereNotNull('rcv_depot.id');
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
        // 寄倉單的商品列表
        $csn_items = DB::table('csn_consignment_items')
            ->select('id as item_id'
                , 'consignment_id'
                , 'product_style_id'
                , DB::raw('@title:=null as combo_product_title')
                , 'title as product_title'
                , 'sku'
                , 'price'
                , 'num as qty'
                , 'memo'
                , 'created_at'
                , 'updated_at'
                , 'deleted_at'
            )
            ->whereNull('deleted_at')
            ->where('consignment_id', $consignment_id)
            ->get();
        // 對應的出貨資料
        $ord_items_arr = ReceiveDepot::getReceiveDepotParseData($event, $consignment_id, $delivery_id, $product_style_id, $csn_items);
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
